<?php

namespace hue\commands\db;

function dbadd()
{

  $driver = new \mysqli_driver();
  $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

  $db = new \mysqli('localhost','root');

  // ask for username and validate
  $username = readline('Username: ');

  // see if user exists
  if(!$info = \hue\user_get($username))
  {
    echo 'User doesn\'t seem to exist.'.PHP_EOL;
    return false;
  }

  // get db name
  do
  {
    $dbname = readline("Database name: {$username}_");
    if(preg_match('/[^a-z0-9_]/',$dbname) || substr($dbname, 0, 1)=='_' || substr($dbname,-1)=='_' || strlen($dbname)>39)
    {
      echo 'Invalid database name.'.PHP_EOL;
      $dbname = null;
    }
  } while(!$dbname);

  try
  {
    // add to user record
    $info['databases'][$dbname] = [];
    \hue\user_save($username, $info);

    // create database
    $db->query("CREATE DATABASE {$username}_{$dbname}");
  }
  catch(\Exception | \mysqli_sql_exception $e)
  {
    echo $e.PHP_EOL;
    return false;
  }

  echo PHP_EOL.'Database created.'.PHP_EOL;
  return true;

}