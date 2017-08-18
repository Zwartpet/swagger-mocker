COMPOSER_OPTS=--no-interaction --no-ansi

default: install

install: clean composer

composer:
	  composer install $(COMPOSER_OPTS)

clean:
	  rm -rf app/cache/*