<?php
/**
 * @author  ismaail <contact@ismaail.com>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
namespace Application\ViewHelper;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Class Lang *
 * @package Application\ViewHelper
 *
 * Get locale language
 */
class Lang extends AbstractHelper implements ServiceLocatorAwareInterface
{
    /**
     * @var \Zend\View\HelperPluginManager
     */
    protected $serviceLocator;

    /**
     * @var string $language
     */
    protected $language;

    public function __invoke()
    {
        if (! $this->language) {
            $translation    = $this->serviceLocator->getServiceLocator()->get('translation/translation');
            $this->language = $translation->getLanguage();
        }

        return $this->language;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
