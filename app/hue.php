<?php

require(__DIR__.'/hue-common.php');

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

while(true)
{
  $command = trim(readline('hue> '));

  if($command=='exit') exit(0);

  $file = __DIR__."/hue-$command.php";
  if(file_exists($file))
  {
    require_once($file);
    echo PHP_EOL;
    call_user_func("\hue\commands\\$command");
    echo PHP_EOL;
  }
  else echo PHP_EOL.'Command not found.'.PHP_EOL.PHP_EOL;
}