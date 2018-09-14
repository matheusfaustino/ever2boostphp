box:
ifeq (,$(wildcard ./box.phar))
		curl -LSs https://box-project.github.io/box2/installer.php | php
endif

build: box
	composer install --no-ansi --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader
	chmod +x box.phar
	./box.phar build
	composer install
