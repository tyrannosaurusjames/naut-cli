<?php
namespace Guttmann\NautCli\Service;

use GuzzleHttp\Client;

class DeployLogService
{

    public function streamLog(Client $client, $statusLink)
    {
        $output = '';
        $status = 'Queued';

        while ($status === 'Queued' || $status === 'Running') {
            $lastOutput = $output;

            $response = $client->get($statusLink);
            $responseData = json_decode($response->getBody()->getContents(), true);

            $status = $responseData['status'];
            $output = $responseData['message'];
            $printableOutput = str_replace($lastOutput, '', $output);

            echo $printableOutput;
            sleep(1);
        }

        return ($status === 'Complete');
    }

}
