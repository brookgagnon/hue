# Current Specs

Vultr Dedicated Cloud\
120GB SSD\
2 vCPU\
8 GB Memory

# Vultr Configuration

* Disable IPv6
* Add Firewall Group, ensure server connected to this group
  * Accept HTTP (anywhere)
  * Accept HTTPS (anywhere)
  * Accept SSH (custom, IPs as needed)
  * Drop any (all ports) 

# Server Initialization

Based on: https://gist.github.com/nd3w/8017f2e0b8afb44188e733d2ec487deb

## Nginx

```
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

# Render Configuration
