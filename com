./vendor/bin/phpcs src
./vendor/bin/phpcbf src

vendor/bin/phpstan analyse src

./vendor/bin/psalm --no-cache