<?php

namespace hue\commands;

function useradd()
  {

  $driver = new \mysqli_driver();
  $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

  $db = new \mysqli('localhost','root');

  // ask for username and validate
  do
  {
    $username = readline('Username: ');
    if(preg_match('/[^a-z0-9]/',$username) || strlen($username)>24)
    {
      echo 'Invalid username.'.PHP_EOL;
      $username = null;
    }
  } while(!$username);

  // see if user exists
  if(\hue\system_user_exists($username))
  {
    echo 'User already exists.'.PHP_EOL;
    return false;
  }

  try
  {
    // create random sql password
    $sqlpass = \hue\random_password(16);

    // add sql user and permissions
    $db->query("CREATE USER `$username`@localhost IDENTIFIED BY '$sqlpass'");
    $db->query("GRANT ALL PRIVILEGES ON `$username\_%`.* TO `$username`@localhost");

    // add system user
    $result_code = null;
    passthru("useradd -m $username", $result_code);
    if($result_code!==0) throw new Exception('Error encountered creating account with useradd command.');

    // add user record
    if(!file_exists("/etc/hue"))
    {
      mkdir('/etc/hue', 0700);
    }
    file_put_contents("/etc/hue/$username", json_encode(['sites'=>[], 'databases'=>[]]));
    chmod("/etc/hue/$username", 0600);
  }
  catch (Exception | mysqli_sql_exception $e)
  {
    echo $e.PHP_EOL;
    return false;
  }

  echo 'User "'.$username.'" added.'.PHP_EOL;
  echo 'SQL Username: '.$username.PHP_EOL;
  echo 'SQL Password: '.$sqlpass.PHP_EOL;

  return true;
}