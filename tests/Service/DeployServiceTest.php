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

        $firstResponseBody = '<html><body><select name="Branch" class="dropdown nolabel" id="DeployForm_DeployForm_Branch"><option value="6f4658662396e5414c7019c394d8c9340ac438ac-test-branch-one">test-branch-one (6f465866, 55 days old)</option><option value="79bebc86c13ddff38148612a84b911d23c6b80b7-test-branch-two">test-branch-two (79bebc86, 3 months old)</option></select></body</html>';
        $secondResponseBody = json_encode([
            'message' => 'Deploy queued as job 68a09c440579d60415ac40f917a1074b',
            'href' => getenv('NAUT_URL') . '/naut/api/' . $instance . '/' . $environment . '/deploy/123'
        ]);

        $mockClient = \Mockery::mock(Client::class);
        $mockClient
            ->shouldReceive('get')
            ->andReturn(new Response(
            200,
            [],
            $firstResponseBody
        ));

        $mockClient
            ->shouldReceive('request')
            ->andReturn(new Response(
                200,
                [],
                $secondResponseBody
            ));

        $service = new DeployService();
        $logLink = $service->deploy($mockClient, $instance, $environment, $branch);

        $this->assertEquals(
            getenv('NAUT_URL') . '/naut/api/' . $instance . '/' . $environment . '/deploy/123',
            $logLink
        );
    }

}
