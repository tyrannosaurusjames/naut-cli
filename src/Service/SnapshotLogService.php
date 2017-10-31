<?php
namespace Guttmann\NautCli\Service;

use GuzzleHttp\Client;

class SnapshotLogService
{

    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function streamLog($logLink, $output)
    {
        $responseOutput = '';
        $status = 'Queued';

        while (in_array($status, ['Queued', 'Running'])) {
            $lastOutput = $responseOutput;
            $logResponse = $this->client->get($logLink);
            $responseBody = $logResponse->getBody()->getContents();
            $jsonResponseBody = json_decode($responseBody, true);
            $status = $jsonResponseBody['status'];
            $responseOutput = $jsonResponseBody['content'];

            $printableOutput = str_replace($lastOutput, '', $responseOutput);

            $output->write($printableOutput);
            sleep(1);
        }

        $output->writeln('');

        return ($status === 'Complete');
    }

}
