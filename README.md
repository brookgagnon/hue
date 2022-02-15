# Current Specs

Vultr Dedicated Cloud\
120GB SSD\
2 vCPU\
8 GB Memory

# Vultr Configuration

* Disable IPv6
* Enable DDOS Protection
* Add Firewall Group, ensure server connected to this group
  * Accept HTTP (anywhere)
  * Accept HTTPS (anywhere)
  * Accept SSH (custom, IPs as needed)
  * Drop any (all ports) 

# Server Initialization

## Nginx

```
$ add-apt-repository ppa:ondrej/nginx
$ apt update
$ apt upgrade
$ apt install nginx
$ ufw allow http
$ ufw allow https
```

## MariaDB

Install MariaDB and run configuration script. Root password can be stored in password manager (though not needed for access when logged in as root). Since the root user uses "unix_socket" plugin, setting a password may actually be unnecessary.

```
$ apt install mariadb-server
$ mysql_secure_installation

Change the root password? [Y/n] y
Remove anonymous users? [Y/n] y
Disallow root login remotely? [Y/n] y
Remove test database and access to it? [Y/n] y
Reload privilege tables now? [Y/n] y
```

## PHP 8.0

```
$ add-apt-repository ppa:ondrej/php
$ apt update
$ apt upgrade
$ apt install php8.0 php8.0-fpm php8.0-curl php8.0-gd php8.0-mbstring php8.0-mysql php8.0-xml php8.0-imagick php8.0-zip php8.0-bcmath php8.0-intl
```

Following installation, ensure configuration protects again this security issue:
https://www.nginx.com/resources/wiki/start/topics/tutorials/config_pitfalls/#passing-uncontrolled-requests-to-php

This installation provided adequate protection using try_files in /etc/nginx/snippets/fastcgi-php.conf as follows:

```
# Check that the PHP script exists before passing it
try_files $fastcgi_script_name =404;
```

TODO: Test this.

# Render Configuration
