# Introduction

Hue is a minimal set of scripts for managing a [LEMP](https://lemp.io/) server on Ubuntu Server 20.04 LTS. It is designed for web developers to provide an inexpective, high performance server for clients (one or more clients per VPS).

## Current Functionality

* Guide for server set up (below)
* Add and remove users
* Add and remove sites (including and Let's Encrypt / HTTPS and optional HTTP authentication)
* Add and remove databases

## Possible Upcoming Functionality

* Edit users, sites, and databases
* Script to initialize server from fresh Ubuntu Server 20.04 install
* Server configuration options such as [Postmark](https://postmarkapp.com/) via sSMTP and multiple PHP versions

# Server Initializtion and Hue Installation

## Suggested Vultr Configuration

* Optimized Cloud
* Disable IPv6
* Enable DDOS Protection
* Add Firewall Group, ensure server connected to this group
  * Accept HTTP (anywhere)
  * Accept HTTPS (anywhere)
  * Accept SSH (custom, IPs as needed)
  * Drop any (all ports) 
* Enable backups

## Server Initialization

### Initial Configuration

Remove the default non-root user:

```
# userdel -r ubuntu
```

Enable reboot after automatic updates, if necessary, by editing /etc/apt/apt.conf.d/50unattended-upgrades:

```
Unattended-Upgrade::Automatic-Reboot "true";
Unattended-Upgrade::Automatic-Reboot-Time "12:00";
```

Adjust the reboot time as needed. The server will likely be in UTC by default.

Disable password authentication for SSH by editing /etc/ssh/sshd_config:

```
PasswordAuthentication no
```

### Nginx

```
# add-apt-repository ppa:ondrej/nginx
# apt update
# apt upgrade
# apt install nginx apache2-utils
# ufw allow http
# ufw allow https
# snap install --classic certbot
# systemctl enable nginx
```

Remove the default site:

```
# rm /etc/nginx/sites-enabled/default
```

### MariaDB

Install MariaDB and run configuration script. Root password can be stored in password manager (though not needed for access when logged in as root). Since the root user uses "unix_socket" plugin, setting a password may actually be unnecessary.

```
# apt install mariadb-server
# mysql_secure_installation

Change the root password? [Y/n] y
Remove anonymous users? [Y/n] y
Disallow root login remotely? [Y/n] y
Remove test database and access to it? [Y/n] y
Reload privilege tables now? [Y/n] y
```

### PHP 8.0

```
# add-apt-repository ppa:ondrej/php
# apt update
# apt upgrade
# apt install php8.0 php8.0-fpm php8.0-curl php8.0-gd php8.0-mbstring php8.0-mysql php8.0-xml php8.0-imagick php8.0-zip php8.0-bcmath php8.0-intl
```

Following installation, ensure configuration protects again this security issue:
https://www.nginx.com/resources/wiki/start/topics/tutorials/config_pitfalls/#passing-uncontrolled-requests-to-php

This installation provided adequate protection using try_files in /etc/nginx/snippets/fastcgi-php.conf as follows:

```
# Check that the PHP script exists before passing it
try_files $fastcgi_script_name =404;
```

Furthermore, security.limit_extensions is on by default:  
https://www.php.net/manual/en/install.fpm.configuration.php#security-limit-extensions

Finally, you may wish to remove the default php-fpm pool if not needed. Hue will handle creating pools for each user.

```
# rm /etc/php/8.0/fpm/pool.d/www.conf
```

## Hue Installation

### Introduction

Hue is a helper tool for user management based on this server configuration.

### Installation

1. Add /root/bin to path by appending the following to /root/.profile:

```
PATH="$HOME/bin:$PATH"
```

2. Clone hue repository and create symlinks to hue bin scripts.

```
# git clone https://github.com/brookgagnon/hue.git /root/hue
# mkdir /root/bin
# ln -s /root/hue/bin/* /root/bin
```

## Optional

* Install dart-sass to /usr/local/bin/sass
* Install wp-cli to /usr/local/bin/wp
* Install composer to /usr/local/bin/composer

### Mail

For outgoing emails (transactional, reports, etc), consider using ssmtp with a service like [Postmark](https://postmarkapp.com/) for ease of use and improved deliverability.
