<?php
namespace Guttmann\NautCli\Service;

use GuzzleHttp\Client;

class SnapshotService
{

    /** @var Client */
    private $client;

    public function __construct(ClientConfigurator $clientConfigurator)
    {
        $this->client = $clientConfigurator->getClient();
    }

    /**
     * @param string $stack The id of the stack
     * @return array
     */
    public function listSnapshots($stack)
    {
        $response = $this->client->get('/naut/project/' . $stack . '/snapshots');

        $responseJson = json_decode($response->getBody()->getContents(), true);

        return $responseJson['data'];
    }

    /**
     * @param string $stack The id of the stack
     * @param int $snapshotId The id of the snapshot to delete
     *
     * @return bool
     */
    public function deleteSnapshot($stack, $snapshotId)
    {
        $response = $this->client->delete('/naut/project/' . $stack . '/snapshots/' . $snapshotId);

        return ($response->getStatusCode() === 204);
    }

}
