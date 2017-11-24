<?php
namespace Guttmann\NautCli\Service;

use GuzzleHttp\Client;
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

    public function download($downloadLink, OutputInterface $output)
    {
        $headResponse = $this->client->head($downloadLink);

        $linkPathInfo = pathinfo($downloadLink);
        $filename = $linkPathInfo['basename'];

        $output->writeln('Downloading snapshot: ' . $filename);

        $totalSize = $headResponse->getHeader('Content-Length')[0];

        $progressBar = new ProgressBar($output, 100);
        $progressBar->setFormat('[%bar%] %percent%%');

        $this->client->get($downloadLink, [
            'sink' => $filename,
            'progress' => function ($dl_total_size, $dl_size_so_far) use ($progressBar, $totalSize) {
                $progress = round(($dl_size_so_far / $totalSize) * 100);
                $progressBar->setProgress($progress);
            }
        ]);

        $progressBar->finish();
        $output->writeln('');
    }

}
