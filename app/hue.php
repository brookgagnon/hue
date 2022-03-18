<?php

if(get_current_user()!=='root')
{
  echo 'Hue must be run as root.'.PHP_EOL;
  exit(1);
}

if(php_sapi_name()!=='cli')
{
  echo 'Hue must be run using PHP CLI.'.PHP_EOL;
  exit(1);
}

require(__DIR__.'/hue-common.php');
require(__DIR__.'/hue-dbadd.php');
require(__DIR__.'/hue-dbdel.php');
require(__DIR__.'/hue-siteadd.php');
require(__DIR__.'/hue-sitedel.php');
require(__DIR__.'/hue-sitegen.php');
require(__DIR__.'/hue-useradd.php');
require(__DIR__.'/hue-userdel.php');

// init
if(!file_exists("/etc/hue"))
{
  mkdir('/etc/hue', 0755);
}

// help
echo '
Commands:

> dbadd
> dbdel
> siteadd
> sitedel
> sitegen
> useradd
> userdel

';

// main
while(true)
{
  $command = trim(readline('hue> '));

  if($command=='exit') exit(0);

  if(function_exists("\hue\commands\\$command"))
  {
    echo PHP_EOL;
    call_user_func("\hue\commands\\$command");
    echo PHP_EOL;
  }
  else echo PHP_EOL.'Command not found.'.PHP_EOL.PHP_EOL;
}