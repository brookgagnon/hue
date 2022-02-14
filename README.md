# Current Specs

Vultr Dedicated Cloud\
120GB SSD\
2 vCPU\
8 GB Memory

# Render Configuration

# Server Initialization

Based on: https://gist.github.com/nd3w/8017f2e0b8afb44188e733d2ec487deb

## Nginx

```
$ apt install nginx
$ ufw allow https
```

## MariaDB

```
$ apt install mariadb-server
$ mysql_secure_installation
```
