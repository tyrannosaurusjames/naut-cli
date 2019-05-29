<?php
namespace Guttmann\NautCli\Service;

use GuzzleHttp\Client;

class DeployService
{

    public function deployBranch(Client $client, $instance, $environment, $branch)
    {
        $payload = json_encode([
            'ref_type' => 'branch',
            'ref' => $branch,
            'bypass_and_start' => true
        ]);

        $response = $client->request(
            'POST',
            '/naut/project/' . $instance . '/environment/' . $environment . '/deploys',
            [
                'body' => $payload
            ]
        );

        $responseData = json_decode($response->getBody()->getContents(), true);

        return str_replace('deploys/', 'deploys/log/', $responseData['data']['links']['self']);
    }

    public function promoteFromUat(Client $client, $stackId)
    {
        $payload = json_encode([
            'ref_type' => 'promote_from_uat',
            'bypass_and_start' => true
        ]);

        $response = $client->request(
            'POST',
            '/naut/project/' . $stackId . '/environment/prod/deploys',
            [
                'body' => $payload
            ]
        );

        $responseData = json_decode($response->getBody()->getContents(), true);

        return str_replace('deploys/', 'deploys/log/', $responseData['data']['links']['self']);
    }

}
