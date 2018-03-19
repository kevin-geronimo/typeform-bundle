.SILENT:
.PHONY: build

# Alias
COMPOSER=php composer.phar

## Colors
COLOR_RESET   = \033[0m
COLOR_INFO    = \033[32m
COLOR_COMMENT = \033[33m

## Help
help:
	printf "${COLOR_COMMENT}Usage:${COLOR_RESET}\n"
	printf " make [target]\n\n"
	printf "${COLOR_COMMENT}Available targets:${COLOR_RESET}\n"
	awk '/^[a-zA-Z\-\_0-9\.@]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf " ${COLOR_INFO}%-16s${COLOR_RESET} %s\n", helpCommand, helpMessage; \
		} \
	} \
	{ lastLine = $$0 }' $(MAKEFILE_LIST)

###############
# Environment #
###############

###########
# Install #
###########
install:
	$(COMPOSER) install --verbose --optimize-autoloader --no-interaction --prefer-dist --no-suggest

#########
# Build #
#########

#####################
# Lint / Code rules #
#####################
grum:
	php vendor/phpro/grumphp/bin/grumphp run

fix:
	php vendor/bin/php-cs-fixer --allow-risky=no --using-cache=yes --path-mode=intersection --verbose fix src/
