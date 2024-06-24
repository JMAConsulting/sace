<?php

set_time_limit(0);

$repo_root = __DIR__ . '/..';

$possible_autoloader_locations = [
    $repo_root . '/../../autoload.php',
    $repo_root . '/vendor/autoload.php',

];

foreach ($possible_autoloader_locations as $location) {
    if (file_exists($location)) {
        $autoloader = require_once $location;
        break;
    }
}

if (empty($autoloader)) {
    echo 'Unable to autoload classes for yml-cli.' . PHP_EOL;
    exit(1);
}

<<<<<<< HEAD
=======
use Grasmash\YamlCli\Command\GetTypeCommand;
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
use Grasmash\YamlCli\Command\GetValueCommand;
use Grasmash\YamlCli\Command\LintCommand;
use Grasmash\YamlCli\Command\UnsetKeyCommand;
use Grasmash\YamlCli\Command\UpdateKeyCommand;
use Grasmash\YamlCli\Command\UpdateValueCommand;
use Symfony\Component\Console\Application;

$application = new Application('yaml-cli', '@package_version@');
$application->add(new GetValueCommand());
<<<<<<< HEAD
=======
$application->add(new GetTypeCommand());
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
$application->add(new LintCommand());
$application->add(new UnsetKeyCommand());
$application->add(new UpdateKeyCommand());
$application->add(new UpdateValueCommand());
$application->run();
