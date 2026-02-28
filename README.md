# Symfony Template

Template Symfony 7.4 avec Docker, PHP 8.4 et outils de qualité.

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

## (re)start the app


## Commandes utiles

```bash
make up              # (re) start the application
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


# Troubleshooting

## Port already in use

Either you change the entry used port or you shut down the service using it. 

Sample:

```bash
Error response from daemon: failed to set up container networking: driver failed programming external connectivity on endpoint symfony_nginx (c33a9daa141044650d533742375ac9bef5a7263f77e3073c1ab95bad8b125d78): failed to bind host port 0.0.0.0:80/tcp: address already in use
```

What is using it ?
```bash
sudo lsof -i :80
COMMAND  PID     USER   FD   TYPE DEVICE SIZE/OFF NODE NAME
apache2 1882     root    4u  IPv6  17998      0t0  TCP *:http (LISTEN)
apache2 1918 www-data    4u  IPv6  17998      0t0  TCP *:http (LISTEN)
apache2 1919 www-data    4u  IPv6  17998      0t0  TCP *:http (LISTEN)
apache2 1920 www-data    4u  IPv6  17998      0t0  TCP *:http (LISTEN)
apache2 1921 www-data    4u  IPv6  17998      0t0  TCP *:http (LISTEN)
apache2 1922 www-data    4u  IPv6  17998      0t0  TCP *:http (LISTEN)
```

Shutdown Apache and remove auto start
```bash
sudo systemctl stop apache2.service
sudo systemctl disable apache2.service 
```

Other way, change the entry port for nginx in the docker-compose.yml file
```yaml
  nginx:
    image: nginx:alpine
    container_name: symfony_nginx
    ports:
      - "443:443" # probably not necessary in local but you can also update this one
      - "80:80" -> "8080:80"
```

# Keep fork update like
```bash

make fetch-template-update # add remote branch reference
make merge-template-update # merge updates
```