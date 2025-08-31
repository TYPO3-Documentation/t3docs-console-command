.PHONY: help
help: ## Displays this list of targets with descriptions
    @echo "The following commands are available:\n"
    @grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: rector
rector: ## Run rector
	Build/Scripts/runTests.sh -s rector

.PHONY: fix-cs
fix-cs: ## Fix PHP coding styles
	Build/Scripts/runTests.sh -s cgl

.PHONY: composer-normalize
composer-normalize: ## Normalize composer.json
	Build/Scripts/runTests.sh -s composerNormalize

.PHONY: fix ## Fix PHP: rector and coding styles
fix: rector fix-cs composer-normalize

.PHONY: phpstan
phpstan: ## Run phpstan tests
	Build/Scripts/runTests.sh -s phpstan

.PHONY: phpstan-baseline
phpstan-baseline: ## Update the phpstan baseline
	Build/Scripts/runTests.sh -s phpstanBaseline

.PHONY: install
install: ## Update the phpstan baseline
	Build/Scripts/runTests.sh -s composerUpdate

.PHONY: test-lint
test-lint: ## Regenerate code snippets
	Build/Scripts/runTests.sh -s lint -p 8.1

.PHONY: test-cgl
test-cgl: ## Regenerate code snippets
	Build/Scripts/runTests.sh -s cgl -p 8.1

.PHONY: test
test: test-lint test-cgl phpstan

.PHONY: docs
docs: ## Generate projects docs (from "Documentation" directory)
	mkdir -p Documentation-GENERATED-temp
	docker run --user $(shell id -u):$(shell id -g) --rm --pull always -v "$(shell pwd)":/project -t ghcr.io/typo3-documentation/render-guides:latest --config=Documentation

.PHONY: test-docs
test-docs: ## Test the documentation rendering
	mkdir -p Documentation-GENERATED-temp
	docker run --user $(shell id -u):$(shell id -g) --rm --pull always -v "$(shell pwd)":/project -t ghcr.io/typo3-documentation/render-guides:latest --config=Documentation --no-progress --fail-on-log
