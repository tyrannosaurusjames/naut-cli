<?php
namespace Guttmann\NautCli\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Output\OutputInterface;

class SnapshotLogService
{

    /**
     * Time to wait between requests to retrieve logs (in seconds)
     */
    const STREAM_DELAY = 1;

    /**
     * How many times to retry retrieving logs before
     * throwing exception.
     */
    const MAX_RETRIES = 3;

    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function streamLog($logLink, OutputInterface $output)
    {
        $logContent = '';
        $status = 'Queued';
        $retries = 0;

        while (in_array($status, ['Queued', 'Running'])) {
            $lastOutput = $logContent;

            try {
                $logData = $this->getLog($logLink);

                $status = $logData['status'];
                $logContent = $logData['content'];

                $printableOutput = str_replace($lastOutput, '', $logContent);

                $output->write($printableOutput);
                $retries = 0;

                $this->wait(self::STREAM_DELAY);
            } catch (RequestException $e) {
                if ($retries >= self::MAX_RETRIES) {
                    throw $e;
                }

                $output->writeln('Failed to get log: ' . $e->getMessage());

                $retries += 1;
                $this->wait(self::STREAM_DELAY + $retries); // back off retry time between failures
            }
        }

        $output->writeln('');

        return ($status === 'Complete');
    }

    private function getLog($logLink)
    {
        $logResponse = $this->client->get($logLink);
        $responseBody = $logResponse->getBody()->getContents();
        return json_decode($responseBody, true);
    }

    protected function wait($seconds)
    {
        sleep($seconds);
    }

}
