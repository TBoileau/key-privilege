ifneq (,$(findstring feature-,$(BRANCH)))
	TEMP_NAME=$(subst $(findstring feature-,$(BRANCH)),feature/,$(BRANCH))
else
	TEMP_NAME=$(BRANCH)
endif
ifneq (,$(findstring release-,$(TEMP_NAME)))
	BRANCH_NAME=$(subst $(findstring release-,$(TEMP_NAME)),release/,$(TEMP_NAME))
else
	BRANCH_NAME=$(TEMP_NAME)
endif

unit-tests:
	php bin/phpunit --testsuite unit

acceptance-tests:
	vendor/bin/behat

functional-tests:
	php bin/phpunit --testsuite functional

.PHONY: fix
fix:
	npx eslint assets/ --fix
	npx stylelint "assets/styles/**/*.scss" --fix
	php bin/phpcbf

.PHONY: vendor
analyze:
	npm audit --production
	npx eslint assets/
	npx stylelint "assets/styles/**/*.scss"
	composer valid
	composer unused --excludePackage=beberlei/doctrineextensions
	php bin/console doctrine:schema:valid --skip-sync
	php bin/phpcs
	php bin/console lint:twig templates/
	vendor/bin/twigcs templates/
	vendor/bin/yaml-lint config/
	php bin/console lint:xliff translations/
	vendor/bin/phpcpd --exclude src/Controller/Admin/ --exclude src/Entity --exclude src/Repository src/
	vendor/bin/phpmd src/ text .phpmd.xml
	php vendor/bin/phpstan analyse -c phpstan.neon src --level 7 --no-progress

.PHONY: tests
tests:
	vendor/bin/behat
	php bin/phpunit --testdox

fixtures-test:
	php bin/console doctrine:fixtures:load -n --env=test

fixtures-dev:
	php bin/console doctrine:fixtures:load -n --env=dev

database-test:
	php bin/console doctrine:database:drop --if-exists --force --env=test
	php bin/console doctrine:database:create --env=test
	php bin/console doctrine:schema:update --force --env=test

database-dev:
	php bin/console doctrine:database:drop --if-exists --force --env=dev
	php bin/console doctrine:database:create --env=dev
	php bin/console doctrine:schema:update --force --env=dev

prepare-test:
	make database-test
	make fixtures-test

prepare-dev:
	make database-dev
	make fixtures-dev

prepare-build:
	make database-test
	make fixtures-test
	npm run dev

install:
	cp .env.dist .env.local
	sed -i -e 's/BRANCH/$(BRANCH)/' .env.local
	sed -i -e 's/USER/$(DATABASE_USER)/' .env.local
	sed -i -e 's/PASSWORD/$(DATABASE_PASSWORD)/' .env.local
	composer install
	npm install
.PHONY: install

deploy:
	composer install
	npm install
	make database-dev
	make fixtures-dev
	npm run build
