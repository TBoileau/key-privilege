unit-tests:
	php bin/phpunit --testsuite unit

functional-tests:
	php bin/phpunit --testsuite functional

.PHONE: tests
tests:
	php bin/phpunit

fixtures-test:
	make database-test
	php bin/console doctrine:fixtures:load -n --env=test

fixtures-dev:
	make database-dev
	php bin/console doctrine:fixtures:load -n --env=test

database-test:
	php bin/console doctrine:database:drop --if-exists --force --env=test
	php bin/console doctrine:database:create --env=test
	php bin/console doctrine:schema:update --force --env=test

database-dev:
	php bin/console doctrine:database:drop --if-exists --force --env=dev
	php bin/console doctrine:database:create --env=dev
	php bin/console doctrine:schema:update --force --env=dev

install:
	composer install
	npm install
	cp .env.dist .env.dev.local
	cp .env.dist .env.test.local
.PHONE: install