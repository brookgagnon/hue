<?php

namespace hue\commands;

function dbdel()
{

  $driver = new \mysqli_driver();
  $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

  $db = new \mysqli('localhost','root');

  // ask for username and validate
  $username = readline('Username: ');

  // get user record
  if(!$info = \hue\user_get($username))
  {
    echo 'User doesn\'t seem to exist.'.PHP_EOL;
    return false;
  }

  // get database
  $dbname = readline("Database name: {$username}_");
  if(($database = $info['databases'][$dbname] ?? null) === null)
  {
    echo 'Database doesn\'t seem to exist.'.PHP_EOL;
    return false;
  }

  try
  {
    // update user record
    unset($info['databases'][$dbname]);
    \hue\user_save($username, $info);

    // drop database
    $db->query("DROP DATABASE {$username}_{$dbname}");
  }
  catch(\Exception | \mysqli_sql_exception $e)
  {
    echo $e.PHP_EOL;
    return false;
  }

  echo PHP_EOL.'Database deleted.'.PHP_EOL;
  return true;
}