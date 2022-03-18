<?php

namespace hue\commands;

function siteadd()
{

  // ask for username and validate
  $username = readline('Username: ');

  // see if user exists
  if(!\hue\user_get($username))
  {
    echo 'User doesn\'t seem to exist.'.PHP_EOL;
    return false;
  }

  // get site name
  do
  {
    $sitename = readline('Site name: ');
    if(preg_match('/[^a-z0-9-]/',$sitename) || substr($sitename, 0, 1)=='-' || substr($sitename,-1)=='-')
    {
      echo 'Invalid site name.'.PHP_EOL;
      $sitename = null;
    }
  } while(!$sitename);

  // see if site exists
  $info = \hue\user_get($username);
  if(isset($info['sites'][$sitename]))
  {
    echo 'Site already exists.'.PHP_EOL;
    return false;
  }

  // get fqdns and validate
  do
  {
    $fqdns = readline('FQDNs (space separated): ');
    $fqdns = array_unique(explode(' ', $fqdns));
    foreach($fqdns as $fqdn)
    {
      if(!filter_var($fqdn, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME))
      {
        $fqdns = null;
        echo 'One or more invalid FQDNs.'.PHP_EOL;
      }
    }
  } while(!$fqdns);

  // output IP addresses
  foreach($fqdns as $fqdn)
  {
    echo $fqdn.': '.gethostbyname($fqdn).PHP_EOL;
  }

  // site configuration
  $config_options = ['static', 'wordpress'];
  do
  {
    $config = trim(readline('Site Configuration (0: Default/Static or 1: WordPress): '));
    if(!isset($config_options[$config]))
    {
      echo 'Enter 0 or 1.'.PHP_EOL;
      $config = null;
    }
  } while(!$config);
  $config = $config_options[$config];

  // http auth
  do
  {
    $authuser = trim(readline('HTTP Authentication Username (optional): '));
    if(!$authuser) break;
    elseif(preg_match('/[^a-z0-9]/',$authuser) || strlen($authuser)>24)
    {
      echo 'Invalid username.'.PHP_EOL;
      $authuser = null;
    }
  } while(!$authuser);

  try
  {
    $info = \hue\user_get($username);

    // add site to hue user file
    $info['sites'][$sitename] = [
      'fqdns' => $fqdns,
      'config' => $config
    ];
    \hue\user_save($username, $info);

    // create site root directory
    if(!file_exists("/home/$username/www"))
    {
      mkdir("/home/$username/www", 0750);
      chown("/home/$username/www", $username);
      chgrp("/home/$username/www", 'www-data');
    }
    if(!file_exists("/home/$username/www/$sitename"))
    {
      mkdir("/home/$username/www/$sitename", 0750);
      chown("/home/$username/www/$sitename", $username);
      chgrp("/home/$username/www/$sitename", 'www-data');
    }

    // handle http auth
    if($authuser)
    {
      $authpass = \hue\random_password(16);
      echo "HTTP Authentication Password: $authpass".PHP_EOL;
      passthru("htpasswd -bc /etc/hue/$username/$sitename.htpasswd $authuser $authpass");
      chgrp("/etc/hue/$username/$sitename.htpasswd", 'www-data');
      chmod("/etc/hue/$username/$sitename.htpasswd", 0440);
    }
  }
  catch (\Exception | \mysqli_sql_exception $e)
  {
    echo $e.PHP_EOL;
    return false;
  }

  // regenerate nginx config
  \hue\commands\sitegen();

  // letsencrypt
  // passthru('certbot certonly --nginx -d '.implode(',',$fqdn_array).' --agree-tos --no-eff-email -m brook@pikalabs.com');

  echo PHP_EOL.'Site added.'.PHP_EOL;
  return true;
}
