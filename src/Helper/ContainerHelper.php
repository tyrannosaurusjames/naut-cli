<?php
namespace Guttmann\NautCli\Helper;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Guttmann\NautCli\Service\DeployLogService;
use Guttmann\NautCli\Service\DeployService;
use Guttmann\NautCli\Service\FetchLatestService;
use Guttmann\NautCli\Service\SnapshotDownloadService;
use Guttmann\NautCli\Service\SnapshotLogService;
use Guttmann\NautCli\Service\SnapshotService;
use GuzzleHttp\Client;
use Pimple\Container;

class ContainerHelper
{

    public static function buildContainer()
    {
        $container = new Container();

        static::setupDependencies($container);

        return $container;
    }

    private static function setupDependencies(Container $container)
    {
        $container['naut.client'] = function () {
            try {
                $dotenv = new Dotenv(getenv('HOME'), ENV_FILE);
                $dotenv->load();
            } catch (InvalidPathException $e) {
                throw new \Exception('The .naut.env file was not found, have you run the \'configure\' command?', 0, $e);
            }

            return new Client([
                'base_uri' => getenv('NAUT_URL'),
                'cookies' => true,
                'auth' => [
                    getenv('NAUT_USERNAME'),
                    base64_decode(getenv('NAUT_TOKEN_B64'))
                ],
                'headers' => [
                    'Content-Type' => 'application/vnd.api+json',
                    'Accept' => 'application/vnd.api+json'
                ]
            ]);
        };

        $container['naut.fetch'] = function () {
            return new FetchLatestService();
        };

        $container['naut.deploy'] = function () {
            return new DeployService();
        };

        $container['naut.deploy_log'] = function () {
            return new DeployLogService();
        };

        $container['naut.snapshot'] = function ($c) {
            return new SnapshotService($c['naut.client']);
        };

        $container['naut.snapshot_log'] = function ($c) {
            return new SnapshotLogService($c['naut.client']);
        };

        $container['naut.download'] = function ($c) {
            return new SnapshotDownloadService($c['naut.client']);
        };
    }

}
