.PHONY: all clean

all: CloudObjects-CLI.phar

clean:
	rm *.phar

composer.lock: composer.json
	# Updating Dependencies with Composer
	composer update -o

vendor: composer.lock
	# Installing Dependencies with Composer
	composer install -o

robo.phar:
	# Get a copy of robo
	wget http://robo.li/robo.phar

CloudObjects-CLI.phar: cloudobjects.php vendor RoboFile.php robo.phar
	# Building archive with robo
	php robo.phar phar
