<?php

namespace hue\commands\user;

function del()
{
  $driver = new \mysqli_driver();
  $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

  $db = new \mysqli('localhost','root');

  // ask for username and validate
  $username = readline('Username: ');

  // see if user exists
  if(!\hue\user_get($username))
  {
    echo 'User doesn\'t seem to exist.'.PHP_EOL;
    return false;
  }

  echo PHP_EOL.'This will delete the system user, database user, home directory, and databases (TODO).'.PHP_EOL.'Are you sure you want to do this?'.PHP_EOL.PHP_EOL;
  $confirm = readline('Type the username again to confirm: ');
  if($confirm!=$username)
  {
    echo 'Username does not match. Please try again.'.PHP_EOL;
    return false;
  }

  try
  {
    // remove sql user
    $db->query("DROP USER `$username`@localhost");

    // remove user record
    passthru("rm -rf /etc/hue/$username");

    // run sitegen to update remove user php-fpm pool
    \hue\commands\site\gen();

    // remove system user (must be done after sitegen to remove user php-fpm pool first)
    $result_code = null;
    passthru("userdel -r $username", $result_code);
    if($result_code!==0) throw new \Exception('Error removing account with userdel command.');

  }
  catch (\Exception | \mysqli_sql_exception $e)
  {
    echo $e.PHP_EOL;
    return false;
  }

  echo PHP_EOL.'User "'.$username.'" deleted.'.PHP_EOL;
  return true;
}
