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
            $output = implode(PHP_EOL, $responseData['message']);
            $printableOutput = str_replace($lastOutput, '', $output);

            echo $printableOutput;
            sleep(1);
        }

        return ($status === 'Completed');
    }

}
