<?php
namespace Guttmann\NautCli\Command;

use Guttmann\NautCli\Kernel;
use Guttmann\NautCli\Service\SnapshotService;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SnapshotListCommandTest extends KernelTestCase
{

    /** @var Application */
    private $application;

    public function setUp()
    {
        $kernel = self::bootKernel();
        $this->application = new Application($kernel);

        $this->mockSnapshotService();
    }

    private function mockSnapshotService()
    {
        $snapshotService = \Mockery::mock(SnapshotService::class);

        $snapshotService
            ->shouldReceive('listSnapshots')
            ->andReturn(json_decode(
                file_get_contents(dirname(__DIR__) . '/data/SnapshotService.listSnapshots.json'),
                true
            ));

        $container = $this->application->getKernel()->getContainer();
        $container->set('naut.snapshot', $snapshotService);
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
    }

    protected static function createKernel(array $options = array())
    {
        return new Kernel('test', false);
    }

}
