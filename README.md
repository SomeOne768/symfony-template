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

### Add hostname and HTTPS

If you want to add a hostname and allow https (certified by yourself):

#### install a certificate authority
```bash
sudo apt install mkcert
sudo apt install libnss3-tools
mkcert -install
```

#### Generate your certificate

```bash
mkcert service-app.local
```

#### Add hostname in /etc/hosts

```bash
sudo nano /etc/hosts 
And add in the file:  127.0.0.1 service-app.local
```

You must then restart your browser


## Commandes utiles

```bash
make php-cs-fixer    # PHP-CS-Fixer
make phpstan         # PHPStan
make rector          # Rector
make twigcs          # TwigCS
make arkitect        # PHPArkitect
make qa-core         # Full QA pipeline
make vendor          # install/rebuild the app
make yarn-watch      # detect changes on css/js files
```


# TODO

- add renovate
- clean makefile
- clean .env and dist files
```

