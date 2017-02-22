<?php
namespace Guttmann\NautCli\Service;

use GuzzleHttp\Client;

class FetchLatestService
{

    public function fetch(Client $client, $instance)
    {
        $response = $client->post('/naut/api/' . $instance . '/fetch');

        $jsonResponse = json_decode($response->getBody()->getContents(), true);

        $statusHref = $jsonResponse['href'];

        echo 'Queued';
        do {
            $response = $client->get($statusHref);
            $jsonResponse = json_decode($response->getBody()->getContents(), true);

            $status = $jsonResponse['status'];
            echo '.';
            sleep(1);
        } while ($status == 'Queued');

        echo PHP_EOL . 'Running';
        do {
            $response = $client->get($statusHref);
            $jsonResponse = json_decode($response->getBody()->getContents(), true);

            $status = $jsonResponse['status'];
            echo '.';
            sleep(1);
        } while ($status == 'Running');

        echo PHP_EOL . 'Fetch complete.' . PHP_EOL;
    }

}