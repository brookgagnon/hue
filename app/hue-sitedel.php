<?php

namespace hue\commands\site;

function del()
{
  // get username
  $username = readline('Username: ');

  // get user
  if(!$info = \hue\user_get($username))
  {
    echo 'User doesn\'t seem to exist.'.PHP_EOL;
    return false;
  }

  // get site
  $sitename = readline('Site name: ');
  if(!$site = $info['sites'][$sitename] ?? null)
  {
    echo 'Site doesn\'t seem to exist.'.PHP_EOL;
    return false;
  }

  try
  {
    // delete certificate (if it exists)
    $cert = $site['fqdns'][0];
    if(file_exists("/etc/letsencrypt/live/$cert"))
    {
      $result_code = null;
      passthru("certbot delete -n --cert-name $cert", $result_code);
      if($result_code!==0) throw new \Exception('Error encountered removing certificates.');
    }

    // delete htpasswd if applicable
    passthru("rm -f /etc/hue/$username/$sitename.htpasswd");

    // delete site from user record
    unset($info['sites'][$sitename]);
    \hue\user_save($username, $info);
  }
  catch(\Exception | \mysqli_sql_exception $e)
  {
    echo $e.PHP_EOL;
    return false;
  }

  // regenerate nginx config
  \hue\commands\site\gen();

  echo PHP_EOL.'Site deleted. Note that site root directory must be deleted manually if desired.'.PHP_EOL;
  return;
}