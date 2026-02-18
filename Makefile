.PHONY: install start db stop

install:
	composer install
	php bin/console doctrine:database:create --if-not-exists
	php bin/console doctrine:migrations:migrate --no-interaction
	php bin/console doctrine:fixtures:load --no-interaction
	@echo "Project installed! Run 'make start' to launch the server."

start:
	symfony server:start -d

stop:
	symfony server:stop

db:
	php bin/console doctrine:database:drop --force --if-exists
	php bin/console doctrine:database:create
	php bin/console doctrine:migrations:migrate --no-interaction
	php bin/console doctrine:fixtures:load --no-interaction

test:
	php bin/phpunit
