<?php

if(exec('whoami')!=='root')
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

> db add
> db del
> site add
> site del
> site gen
> user add
> user del

';

// write history on shutdown
function hue_write_history()
{
  readline_write_history('/root/.hue_history');
}
register_shutdown_function('hue_write_history');
pcntl_async_signals(true);
pcntl_signal(SIGINT,function($signal) { hue_write_history(); exit(0); }); 

// main
readline_read_history('/root/.hue_history');
while(true)
{
  do
  {
    $command = trim(readline('hue> '));
  } while($command=='');
  readline_add_history($command);

  if($command=='exit') exit(0);

  $command_parts = explode(' ',$command);
  $command_namespace = $command_parts[0] ?? null;
  $command_function = $command_parts[1] ?? null;

  if($command_namespace && $command_function && function_exists("\hue\commands\\$command_namespace\\$command_function"))
  {
    echo PHP_EOL;
    call_user_func("\hue\commands\\$command_namespace\\$command_function");
    echo PHP_EOL;
  }
  else echo PHP_EOL.'Command not found.'.PHP_EOL.PHP_EOL;
}
