#################################
# Git
#################################

fetch-template-update:
	@if ! git remote | grep -q "^template$$"; then \
		git remote add template https://github.com/SomeOne768/symfony-template; \
	fi
	git fetch template


merge-template-update:
	git merge template/main
