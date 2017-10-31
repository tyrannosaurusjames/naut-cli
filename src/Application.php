<?php
namespace Guttmann\NautCli;

use Pimple\Container;
use Symfony\Component\Console\Application as ConsoleApplication;

class Application extends ConsoleApplication
{

    /** @var Container */
    private $container;

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

}
