<?php
namespace Guttmann\NautCli\Service;

use GuzzleHttp\Client;

class DeployLogService
{

    public function streamLog(Client $client, $statusLink)
    {
        $output = '';
        $status = 'Queued';

        $runningStatuses = [
            'New',
            'Approved',
            'Queued',
            'Deploying'
        ];

        while (in_array($status, $runningStatuses)) {
            $lastOutput = $output;

            $response = $client->get($statusLink);
            $responseData = json_decode($response->getBody()->getContents(), true);

            $status = $responseData['data']['attributes']['state'];

            $output = implode(PHP_EOL, array_filter($responseData['message'], function ($item) {
                return ($item !== '');
            }));

            $printableOutput = str_replace($lastOutput, '', $output);

            echo $printableOutput;
            sleep(1);
        }

        echo PHP_EOL;

        return ($status === 'Completed');
    }

}
