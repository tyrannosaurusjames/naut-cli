<?php
namespace Guttmann\NautCli\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class SnapshotLogServiceTest extends TestCase
{

    private $testUri = 'http://www.example.com';
    private $testExceptionMessage = 'A test error occurred.';

    public function testRetryUntilFail()
    {
        $this->expectException(RequestException::class);

        $expectedException = $this->setupExpectedException();
        $client = $this->setupFailingMockClient($expectedException);

        $output = new BufferedOutput();
        $logService = new TestSnapshotLogService($client);

        try {
            $logService->streamLog($this->testUri, $output);
        } catch (\Exception $actualException) {
            $this->assertContains($expectedException->getMessage(), $output->fetch());

            throw $actualException;
        }
    }

    private function setupExpectedException()
    {
        return new RequestException(
            $this->testExceptionMessage,
            new Request('GET', $this->testUri)
        );
    }

    private function setupFailingMockClient($exception)
    {
        /** @var Client|Mockery\MockInterface $client */
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('get')->times(3)->andThrow($exception);

        return $client;
    }

    public function testRetryWithRecover()
    {
        $exception = $this->setupExpectedException();
        $client = $this->setupRecoveringMockClient($exception);

        $output = new BufferedOutput();
        $logService = new TestSnapshotLogService($client);

        $result = $logService->streamLog($this->testUri, $output);

        $log = $output->fetch();

        $this->assertTrue($result);
        $this->assertContains($this->testExceptionMessage, $log);
    }

    private function setupRecoveringMockClient($exception)
    {
        /** @var Client|Mockery\MockInterface $client */
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('get')->times(2)->andThrow($exception);
        $client->shouldReceive('get')->once()->andReturn(new Response(200, [], json_encode([
            'content' => 'Test log',
            'status' => 'Complete'
        ])));

        return $client;
    }

}

/**
 * Class TestSnapshotLogService
 * @package Guttmann\NautCli\Service
 *
 * Test class for SnapshotLogService. Only change is the wait method to remove the call to sleep().
 */
class TestSnapshotLogService extends SnapshotLogService
{

    protected function wait($seconds)
    {
        // don't call sleep() during tests
    }

}
