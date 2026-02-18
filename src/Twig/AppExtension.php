<?php

namespace App\Twig;

use App\Repository\HoraireRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private HoraireRepository $horaireRepository)
    {
    }

    public function getGlobals(): array
    {
        return [
            'horaires' => $this->horaireRepository->findAll(),
        ];
    }
}
