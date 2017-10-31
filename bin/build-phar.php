<?php

define('BASE_PATH', realpath(dirname(__FILE__) . '/../'));
echo 'Base path: ' . BASE_PATH . PHP_EOL;

define('DIST_ROOT', BASE_PATH . DIRECTORY_SEPARATOR . 'dist');
echo 'Dist root: ' . DIST_ROOT . PHP_EOL;

define('BUILD_ROOT', DIST_ROOT . DIRECTORY_SEPARATOR . 'build');
echo 'Build root: ' . BUILD_ROOT . PHP_EOL;

define('PHAR_FILE', 'naut-cli.phar');
define('PATH_TO_PHAR', DIST_ROOT . DIRECTORY_SEPARATOR . PHAR_FILE);
echo 'Path to PHAR: ' . PATH_TO_PHAR . PHP_EOL;

$loadDirectories = [
    BUILD_ROOT . DIRECTORY_SEPARATOR . 'config',
    BUILD_ROOT . DIRECTORY_SEPARATOR . 'src',
    BUILD_ROOT . DIRECTORY_SEPARATOR . 'vendor'
];

$phar = new Phar(
    DIST_ROOT . "/naut-cli.phar",
    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
    "naut-cli.phar"
);

foreach ($loadDirectories as $loadDirectory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($loadDirectory));

    foreach ($iterator as $file) {
        if ($file->isDir()) {
            continue;
        }

        $pathName = $file->getPathname();

        $phar[str_replace(BUILD_ROOT . '/', '', $pathName)] = file_get_contents($pathName);
        echo '.';
    }
}

$phar['index.php'] = file_get_contents(BUILD_ROOT . DIRECTORY_SEPARATOR . 'naut-cli.php');

echo PHP_EOL;

$defaultStub = $phar->createDefaultStub("index.php");

$phar->setStub('#!/usr/bin/env php' . PHP_EOL . $defaultStub);
$phar->stopBuffering();

chmod(PATH_TO_PHAR, 0755);

echo 'Phar created at ' . PATH_TO_PHAR . PHP_EOL;