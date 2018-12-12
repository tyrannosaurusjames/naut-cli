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
            $previousException = null;

            try {
                $dotenv = new Dotenv(getenv('HOME'), ENV_FILE);
                $dotenv->load();
            } catch (InvalidPathException $e) {
                $previousException = $e;
            }

            $nautUrl = getenv('NAUT_URL');
            $nautUsername = getenv('NAUT_USERNAME');
            $nautToken = getenv('NAUT_TOKEN');

            if ($nautUrl === false || $nautUsername === false || $nautToken === false) {
                $errorMessage = <<<TEXT
Missing configuration
Environment variables NAUT_URL, NAUT_USERNAME, and NAUT_TOKEN are required.
Use the 'configure' command to create a .naut.env file or configure the
variables in your environment before running naut-cli.
TEXT;

                throw new \Exception(
                    $errorMessage,
                    0,
                    $previousException
                );
            }

            return new Client([
                'base_uri' => $nautUrl,
                'cookies' => true,
                'auth' => [
                    $nautUsername,
                    $nautToken
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
