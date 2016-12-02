.PHONY: all

all: CloudObjects-CLI.phar

composer.lock: composer.json
	# Updating Dependencies with Composer
	composer update -o

vendor: composer.lock
	# Installing Dependencies with Composer
	composer install -o

CloudObjects-CLI.phar: cloudobjects.php vendor
	# Building archive with phar-composer.phar
	# Note: phar-composer.phar must be added manually, it is not present in the repository
	php phar-composer.phar build .
