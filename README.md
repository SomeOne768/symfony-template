# Symfony Template

Template Symfony 7.4 avec Docker, PHP 8.3 et outils de qualité.

## Structure

- `docker/` : conteneurs PHP, Nginx, MySQL, Node
- `service-app/` : application Symfony

## Installation

```bash
cp service-app/.env.dist service-app/.env
make init
make vendor
```


## Commandes utiles

```bash
make php-cs-fixer    # PHP-CS-Fixer
make phpstan         # PHPStan
make rector          # Rector
make twigcs          # TwigCS
make arkitect        # PHPArkitect
make qa-core         # Full QA pipeline
```

# TODO

- add renovate
- clean makefile
- clean .env and dist files