<?php

namespace Guttmann\NautCli\Command;

use Guttmann\NautCli\Service\SnapshotService;
use PHPUnit\Framework\TestCase;
use Guttmann\NautCli\Application;
use PHPUnit\Framework\Attributes\CoversClass;
use Pimple\Container;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(SnapshotListCommand::class)]
class SnapshotListCommandTest extends TestCase
{

    /** @var Application */
    private $application;

    public function setUp(): void
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
        $this->application->addCommand(new SnapshotListCommand());

        $command = $this->application->find('snapshot:list');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            'stack' => 'test'
        ));

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Snapshots for stack: test', $output);
        $this->assertStringContainsString('Production', $output);
        $this->assertStringContainsString('2014-07-02 00:00:00', $output);
        $this->assertStringContainsString('db', $output);
    }
}
