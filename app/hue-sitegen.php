<?php

namespace hue\commands;

function sitegen()
  {

  $nginxconf = '';
  $fpmconf = '';

  $users = \hue\user_list();

  foreach($users as $username)
  {
    $info = \hue\user_get($username);
    if(empty($info['sites'])) continue;    

    foreach($info['sites'] as $sitename=>$site)
    {
      $server_name = implode(' ', $site['fqdns']);

      $nginxconf .= "server {
    listen 80;
    listen [::]:80;

    server_name $server_name;

    root /home/$username/www/$sitename;
    index index.html index.htm index.php;

    location ~ /\.(?!well-known).* {
      deny all;
      return 404;
    }

    location / {
      try_files \$uri \$uri/ =404;
    }

    location ~ \.php$ {
      include snippets/fastcgi-php.conf;
      fastcgi_pass unix:/var/run/php/php8.0-$username-fpm.sock;
    }
  }

  ";
    }

    $fpmconf .= "[$username]
user = pikalabs
group = pikalabs
listen = /run/php/php8.0-$username-fpm.sock
listen.owner = www-data
listen.group = www-data
pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3

";

  }

  file_put_contents('/etc/php/8.0/fpm/pool.d/hue.conf', $fpmconf);
  file_put_contents('/etc/nginx/conf.d/hue.conf', $nginxconf);
  passthru('systemctl restart php8.0-fpm');
  passthru('systemctl restart nginx');

  echo 'PHP-FPM pools and Nginx configuration updated.'.PHP_EOL;

  return true;
}
