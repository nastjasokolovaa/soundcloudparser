.PHONY: init
init:
	php bin/console doctrine:database:create
