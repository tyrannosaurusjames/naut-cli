<?php
namespace Guttmann\NautCli\Service;

use GuzzleHttp\Client;

class DeployLogService
{

    public function streamLog(Client $client, $streamLink)
    {
        /** @var \GuzzleHttp\Cookie\CookieJar $cookieJar */
        $cookieJar = $client->getConfig('cookies');

        $cookieStrings = [];

        foreach ($cookieJar->toArray() as $cookie) {
            $cookieStrings[] = $cookie['Name'] . '=' . $cookie['Value'] . ';';
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'Cookie: ' . implode(' ', $cookieStrings) . "\r\n"
            ]
        ]);

        $timeout = 30;

        $output = '';

        while ($timeout > 0) {
            $lastOutput = $output;
            $output = file_get_contents($streamLink, 'r', $context);

            $printableOutput = str_replace($lastOutput, '', $output);

            if ($printableOutput === '') {
                $timeout -= 1;
            } else {
                $timeout = 30;
            }

            echo $printableOutput;

            if (preg_match('/deploy of ".*" to ".*" finished/i', $printableOutput) === 1) {
                return;
            } else {
                sleep(1);
            }
        }

        echo PHP_EOL . 'Streaming deploy log timed out. Maybe the deployment failed?' . PHP_EOL;
        exit(1);
    }

}
