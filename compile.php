<?php

// compile command: php --define phar.readonly=0 compile.php
// based on https://blog.programster.org/creating-phar-files
try
{
    $pharFile = __DIR__.'/app.phar';

    // create phar
    $phar = new Phar($pharFile);

    // start buffering. Mandatory to modify stub to add shebang
    $phar->startBuffering();

    // Create the default stub from main.php entrypoint
    $defaultStub = $phar->createDefaultStub('hue.php');

    // Add the rest of the apps files
    $phar->buildFromDirectory(__DIR__ . '/app');

    // Customize the stub to add the shebang
    $stub = "#!/usr/bin/env php \n" . $defaultStub;

    // Add the stub
    $phar->setStub($stub);

    $phar->stopBuffering();

    # Make the file executable
    chmod(__DIR__ . '/app.phar', 0700);
    rename(__DIR__ . '/app.phar', __DIR__ . '/bin/hue');

    echo "Success." . PHP_EOL;
}
catch (Exception $e)
{
    echo $e->getMessage();
}
