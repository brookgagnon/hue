<?php

namespace hue;

function system_user_exists($username)
{
  $result_code = null;
  $output = null;
  exec('id '.escapeshellarg($username).' 2>/dev/null', $output, $result_code);
  return $result_code==0;
}

function user_list()
{
  // find user files
  $files = scandir('/etc/hue');
  $files = array_filter($files, function($file)
  {
    return $file[0]!='.';
  });

  $users = [];

  foreach($files as $file)
  {
    $users[] = $file;
  }
  
  return $users;
}

function user_get($username)
{
  if(!file_exists("/etc/hue/$username/hue.json")) return false;
  return json_decode(file_get_contents("/etc/hue/$username/hue.json"), true);
}

function user_save($username, $info)
{
  file_put_contents("/etc/hue/$username/hue.json", json_encode($info));
}

function random_password($length)
{
  $keyspace = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890';
  $password = '';
  while(strlen($password)<$length) $password = $password.$keyspace[random_int(0, strlen($keyspace)-1)];
  return $password;
}