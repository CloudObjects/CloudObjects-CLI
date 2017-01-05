.PHONY: all

all: CloudObjects-CLI.phar

composer.lock: composer.json
	# Updating Dependencies with Composer
	composer update -o

vendor: composer.lock
	# Installing Dependencies with Composer
	composer install -o

phar-composer.phar:
	# Get a copy of phar-composer
	wget https://github.com/clue/phar-composer/releases/download/v1.0.0/phar-composer.phar

CloudObjects-CLI.phar: cloudobjects.php vendor phar-composer.phar
	# Building archive with phar-composer.phar
	php phar-composer.phar build . CloudObjects-CLI.phar
