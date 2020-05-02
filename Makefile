bash: ## Launch bash in docker container with PHP
	docker run \
		--name=security_bundle_console \
		--volume=$(shell pwd):/srv \
		--env USERNAME=$(shell whoami) \
		--env UNIX_UID=$(shell id -u) \
		--env=CONTAINER_SHELL=/bin/bash \
		--workdir=/srv \
		--interactive \
		--tty \
		--rm \
		meuhmeuhconcept/php:2.3.2 \
		/bin/login -p -f $(shell whoami)

console: ## Launch zsh in docker container with PHP
	docker run \
		--name=security_bundle_console \
		--volume=$(shell pwd):/srv \
		--volume=$$HOME/.home-developer:/home/developer \
		--env USERNAME=$(shell whoami) \
		--env UNIX_UID=$(shell id -u) \
		--env=CONTAINER_SHELL=/bin/zsh \
		--workdir=/srv \
		--interactive \
		--tty \
		--rm \
		meuhmeuhconcept/php:2.3.2 \
		/bin/login -p -f $(shell whoami)

help:
    @grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

.DEFAULT_GOAL := help