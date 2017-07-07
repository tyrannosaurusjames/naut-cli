<?php

$autoloadOptions = [
    __DIR__ . '/../vendor/autoload.php', // dev
    __DIR__ . '/../../../autoload.php' // global install
];

foreach ($autoloadOptions as $autoloadOption) {
    if (file_exists($autoloadOption)) {
        require $autoloadOption;
        break;
    }
}

use Symfony\Component\Console\Application;

$application = new Application();

$application->setName(
<<<TXT
\e[31m
 ▐ ▄  ▄▄▄· ▄• ▄▌▄▄▄▄▄     ▄▄· ▄▄▌  ▪  
•█▌▐█▐█ ▀█ █▪██▌•██      ▐█ ▌▪██•  ██ 
▐█▐▐▌▄█▀▀█ █▌▐█▌ ▐█.▪    ██ ▄▄██▪  ▐█·
██▐█▌▐█ ▪▐▌▐█▄█▌ ▐█▌·    ▐███▌▐█▌▐▌▐█▌
▀▀ █▪ ▀  ▀  ▀▀▀  ▀▀▀     ·▀▀▀ .▀▀▀ ▀▀▀\e[39m
TXT
);

$application->addCommands([
    (new Guttmann\NautCli\Command\DeployCommand()),
    (new Guttmann\NautCli\Command\ConfigureCommand())
]);

define('ENV_FILE', '.naut.env');

$application->run();
