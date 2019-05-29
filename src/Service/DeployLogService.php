<?php
namespace Guttmann\NautCli\Service;

use GuzzleHttp\Client;
use Symfony\Component\Console\Output\OutputInterface;

class DeployLogService
{

    public function streamLog(Client $client, $statusLink, OutputInterface $output)
    {
        $logOutput = '';
        $status = 'Queued';

        $runningStatuses = [
            'New',
            'Approved',
            'Queued',
            'Deploying'
        ];

        while (in_array($status, $runningStatuses)) {
            $lastLogOutput = $logOutput;

            $response = $client->get($statusLink);
            $responseData = json_decode($response->getBody()->getContents(), true);

            $status = $responseData['data']['attributes']['state'];

            $logOutput = implode(PHP_EOL, array_filter($responseData['message'], function ($item) {
                return ($item !== '');
            }));

            $printableOutput = str_replace($lastLogOutput, '', $logOutput);

            $output->write($printableOutput);
            sleep(1);
        }

        $output->writeln('');

        return ($status === 'Completed');
    }

}
