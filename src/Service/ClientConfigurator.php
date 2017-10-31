<?php
namespace Guttmann\NautCli\Service;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use GuzzleHttp\Client;

class ClientConfigurator
{

    private static $client;

    public function getClient()
    {
        $this->loadEnvironmentConfig();

        if (!static::$client instanceof Client) {
            static::$client = new Client([
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
        }

        return static::$client;
    }

    private function loadEnvironmentConfig()
    {
        try {
            $dotenv = new Dotenv(getenv('HOME'), ENV_FILE);
            $dotenv->load();
        } catch (InvalidPathException $e) {
            throw new \Exception('The .naut.env file was not found, have you run the \'configure\' command?', 0, $e);
        }
    }

}
