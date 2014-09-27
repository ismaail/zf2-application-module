<?php
namespace Application\ViewHelper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ErrorMessage extends AbstractHelper implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var \Zend\I18n\Translator\Translator
     */
    protected $translator;

    /**
     * Set the service locator.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CustomHelper
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }
    /**
     * Get the service locator.
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Translate
     *
     * @param string $word
     *
     * @return string
     */
    protected function translate($word)
    {
        if (! $this->translator) {
            $this->translator = $this->getServiceLocator()->getServiceLocator()->get('translator');
        }

        return $this->translator->translate($word);
    }

    /**
     * @param array $list
     *
     * @return string
     */
    public function __invoke($list)
    {
        $this->getServiceLocator();
        if (empty($list)) {
            return '';
        }

        if (! is_array($list)) {
            $list = (array)$list;
        }

        // translate array list
        $list = array_map(array($this, 'translate'), $list);

        $list = join('</li><li>', $list);

        $html = <<<HTML
<div class="alert col-md-8 alert-error">
    <ul class="list-unstyled">
        <li><strong>{$this->translate('Error')}</strong></li>
    </ul>
    <ul>
        <li>%s</li>
    </ul>
</div>
HTML;

        return sprintf($html, $list);
    }
}
