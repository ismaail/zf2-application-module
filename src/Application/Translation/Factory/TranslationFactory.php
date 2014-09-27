<?php
/**
 * @author  ismaail <contact@ismaail.com>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
namespace Application\Translation\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Di\Di;

/**
 * Class TranslationFactory
 * @package Application\Translation\Factory
 */
class TranslationFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return \Application\Translation\Translation
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $di = new Di();

        return $di->get('Application\Translation\Translation', array(
            'translator' => $serviceLocator->get('translator'),
            'config'     => $serviceLocator->get('config')['locale'],
        ));
    }
}
