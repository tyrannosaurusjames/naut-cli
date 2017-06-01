<?php
namespace Guttmann\NautCli\Service;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class DeployService
{

    public function deploy(Client $client, $instance, $environment, $branch)
    {
        $response = $client->get('/naut/project/' . $instance . '/environment/' . $environment);

        $crawler = new Crawler($response->getBody()->getContents());

        $branchIdentifier = $crawler->filter('option[value$="' . $branch . '"]')->first()->attr('value');
        $branchIdentifierParts = explode('-', $branchIdentifier);
        $commitHash = array_shift($branchIdentifierParts);

        $payload = json_encode([
            'release' => $commitHash
        ]);

        $response = $client->request(
            'POST',
            '/naut/api/' . $instance . '/' . $environment . '/deploy',
            [
                'body' => $payload
            ]
        );

        $responseData = json_decode($response->getBody()->getContents(), true);

        return $responseData['href'];
    }

}
