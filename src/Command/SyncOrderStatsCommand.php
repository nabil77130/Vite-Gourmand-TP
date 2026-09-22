<?php

namespace App\Command;

use App\Repository\OrderRepository;
use App\Service\OrderStatsRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Recopie toutes les commandes de la base relationnelle vers MongoDB.
 *
 * Utile dans deux cas : au premier branchement du NoSQL, pour ne pas partir
 * d'un tableau de bord vide, et apres un rechargement des fixtures, pour
 * remettre les deux bases d'accord.
 *
 * L'ecriture etant idempotente (upsert sur l'identifiant de commande),
 * relancer la commande ne cree jamais de doublon.
 */
#[AsCommand(
    name: 'app:sync-order-stats',
    description: 'Synchronise les commandes vers la base NoSQL utilisée par le tableau de bord',
)]
class SyncOrderStatsCommand extends Command
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly OrderStatsRepository $orderStats,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'purge',
            null,
            InputOption::VALUE_NONE,
            'Vide la collection avant de resynchroniser (à utiliser après un rechargement des fixtures)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->orderStats->isReachable()) {
            $io->error([
                'Impossible de joindre la base MongoDB.',
                'Vérifiez MONGODB_URI dans le fichier .env, et que votre adresse IP est autorisée dans Atlas.',
            ]);

            return Command::FAILURE;
        }

        if ($input->getOption('purge')) {
            $deleted = $this->orderStats->purgeAll();
            $io->note(sprintf('%d document(s) supprimé(s) de la collection.', $deleted));
        }

        $orders = $this->orderRepository->findAll();

        if ($orders === []) {
            $io->warning('Aucune commande à synchroniser.');

            return Command::SUCCESS;
        }

        $io->progressStart(count($orders));
        $synced = 0;

        foreach ($orders as $order) {
            try {
                $this->orderStats->saveOrder($order);
                ++$synced;
            } catch (\Throwable $e) {
                $io->warning(sprintf('Commande #%d ignorée : %s', $order->getId(), $e->getMessage()));
            }
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success(sprintf('%d commande(s) synchronisée(s) vers MongoDB.', $synced));

        return Command::SUCCESS;
    }
}
