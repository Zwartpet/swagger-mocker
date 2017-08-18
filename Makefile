COMPOSER_OPTS=--no-interaction --no-ansi

default: install

install: clean composer

composer:
	  /usr/local/bin/composer install $(COMPOSER_OPTS)

clean:
	  rm -rf app/cache/*