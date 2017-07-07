<?php
namespace Guttmann\NautCli\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class DeployServiceTest extends TestCase
{

    public function setUp()
    {
        putenv('NAUT_URL=https://naut.test.com');
        putenv('NAUT_USERNAME=test@test.com');
        putenv('NAUT_PASSWORD_B64=testpassword');
    }

    public function testDeploy()
    {
        $instance = 'test-project';
        $environment = 'test';
        $branch = 'test-branch-two';

        $mockResponseBody = json_encode([
            'data' => [
                'links' => [
                    'self' => getenv('NAUT_URL') . '/naut/project/' . $instance . '/environment/' . $environment . '/deploys/123'
                ]
            ]
        ]);

        $mockClient = \Mockery::mock(Client::class);

        $mockClient
            ->shouldReceive('request')
            ->andReturn(new Response(
                200,
                [],
                $mockResponseBody
            ));

        $service = new DeployService();
        $logLink = $service->deploy($mockClient, $instance, $environment, $branch);

        $this->assertEquals(
            getenv('NAUT_URL') . '/naut/project/' . $instance . '/environment/' . $environment . '/deploys/log/123',
            $logLink
        );
    }

}
