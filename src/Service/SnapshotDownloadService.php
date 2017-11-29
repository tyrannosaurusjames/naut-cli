<?php
namespace Guttmann\NautCli\Service;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class SnapshotDownloadService
{

    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function downloadToStdOut($downloadLink, OutputInterface $output)
    {
        $response = $this->client->get($downloadLink, [
            RequestOptions::STREAM => true,
            RequestOptions::SINK => STDOUT
        ]);

        $body = $response->getBody();

        while (!$body->eof()) {
            $chunk = $body->read(8192);
            $output->write($chunk, false, OutputInterface::OUTPUT_RAW);
        }
    }

    public function downloadWithProgressBar($downloadLink, OutputInterface $output)
    {
        $headResponse = $this->client->head($downloadLink);

        $linkPathInfo = pathinfo($downloadLink);
        $filename = $linkPathInfo['basename'];

        $output->writeln('Downloading snapshot: ' . $filename);

        $totalSize = $headResponse->getHeader('Content-Length')[0];

        $progressBar = new ProgressBar($output, 100);
        $progressBar->setFormat('[%bar%] %percent%%');

        $this->client->get($downloadLink, [
            RequestOptions::SINK => getcwd() . DIRECTORY_SEPARATOR . $filename,
            RequestOptions::PROGRESS => function ($dl_total_size, $dl_size_so_far) use ($progressBar, $totalSize) {
                $progress = round(($dl_size_so_far / $totalSize) * 100);
                $progressBar->setProgress($progress);
            }
        ]);

        $progressBar->finish();
        $output->writeln('');
    }

}
