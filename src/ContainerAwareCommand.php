<?php
namespace Guttmann\NautCli;

use Pimple\Container;
use Symfony\Component\Console\Command\Command;

class ContainerAwareCommand extends Command
{

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->getApplication()->getContainer();
    }

}
