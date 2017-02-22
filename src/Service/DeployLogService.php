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

        $timeout = 10;

        $output = '';

        while ($timeout > 0) {
            $lastOutput = $output;
            $output = file_get_contents($streamLink, 'r', $context);

            $printableOutput = str_replace($lastOutput, '', $output);

            if ($printableOutput === '') {
                $timeout -= 1;
            } else {
                $timeout = 10;
            }

            echo $printableOutput;
            sleep(1);
        }

        echo PHP_EOL . 'No contact with stream for 10 seconds, assumed finished.' . PHP_EOL;
    }

}
