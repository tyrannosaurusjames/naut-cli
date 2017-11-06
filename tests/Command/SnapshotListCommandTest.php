<?php
namespace Guttmann\NautCli\Command;

use Guttmann\NautCli\Service\SnapshotService;
use PHPUnit\Framework\TestCase;
use Guttmann\NautCli\Application;
use Pimple\Container;
use Symfony\Component\Console\Tester\CommandTester;

class SnapshotListCommandTest extends TestCase
{

    /** @var Application */
    private $application;

    public function setUp()
    {
        $this->application = new Application();
        $this->mockSnapshotService();
    }

    private function mockSnapshotService()
    {
        $container = new Container();

        $container['naut.snapshot'] = function ($c) {
            $snapshotService = \Mockery::mock(SnapshotService::class);

            $snapshotService
                ->shouldReceive('listSnapshots')
                ->andReturn(json_decode(
                    file_get_contents(dirname(__DIR__) . '/data/SnapshotService.listSnapshots.json'),
                    true
                ));

            return $snapshotService;
        };

        $this->application->setContainer($container);
    }

    public function testExecute()
    {
        $this->application->add(new SnapshotListCommand());

        $command = $this->application->find('snapshot:list');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            'stack' => 'test'
        ));

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertContains('Snapshots for stack: test', $output);
        $this->assertContains('Production', $output);
        $this->assertContains('2014-07-02 00:00:00', $output);
        $this->assertContains('db', $output);
    }

}
