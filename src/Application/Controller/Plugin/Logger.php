<?php
namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Logger extends AbstractPlugin
{
    protected $logger = null;

    /**
     * __call magic function
     *
     * @param  string $name         Function name to execute
     * @param  array|string $args   Arguments
     *
     * @return void
     */
    public function __call($name, $args)
    {
        if (! $this->logger) {
            $this->logger = $this->getController()->getServiceLocator()->get('logger');
        }

        $this->logger->{$name}($args[0]);
    }
}
