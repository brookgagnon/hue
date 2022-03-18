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
      if($site['config']=='wordpress')
      {
        $index = 'index.php';
        $try_files = '$uri $uri/ /index.php?$args';
      }
      else {
        $index = 'index.html index.htm index.php';
        $try_files = '$uri $uri/ =404';
      }

      $server_name = implode(' ', $site['fqdns']);
      $cert_dir = $site['fqdns'][0];
      $https = file_exists("/etc/letsencrypt/live/$cert_dir");
      
      if($https)
      {

        $nginx_listen = "
  listen 443 ssl;
  ssl_certificate /etc/letsencrypt/live/$cert_dir/fullchain.pem;
  ssl_certificate_key /etc/letsencrypt/live/$cert_dir/privkey.pem;
  include /etc/letsencrypt/options-ssl-nginx.conf;
  ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
        ";

        $nginx_after = "
server {
  server_name $server_name;
  listen 80;
  return 301 https://\$host\$request_uri;
}
        ";

      }

      else
      {

        $nginx_listen = "
  listen 80;
        ";

        $nginx_after = '';

      }

      if(file_exists("/etc/hue/$username/$sitename.htpasswd"))
      {
        $nginx_auth = "
  auth_basic \"Authentication Required\";
  auth_basic_user_file /etc/hue/$username/$sitename.htpasswd;
        ";
      }
      else $nginx_auth = "";

      $nginxconf .= "
server {
  server_name $server_name;
  $nginx_listen
  $nginx_auth

  root /home/$username/www/$sitename;
  index $index;

  location ~ /\.(?!well-known).* {
    deny all;
    return 404;
  }

  location / {
    try_files $try_files;
  }

  location ~ \.php$ {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/var/run/php/php8.0-$username-fpm.sock;
  }
}

$nginx_after
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
