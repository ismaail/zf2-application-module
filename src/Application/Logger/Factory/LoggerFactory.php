<?php
namespace Application\Logger\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Logger\Logger;

/**
 * Class LoggerFactory
 * @package Application\Logger\Factory
 */
class LoggerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Logger();
    }
}
