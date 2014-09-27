<?php
namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Class Translate
 * @package Application\Controller\Plugin
 */
class Translate extends AbstractPlugin
{
    /**
     * @var \Zend\I18n\Translator\Translator
     */
    protected $translator;

    /**
     * @param string $word
     *
     * @return string
     */
    public function __invoke($word)
    {
        if (! $this->translator) {
            $this->translator = $this->getController()->getServiceLocator()->get('translator');
        }

        return $this->translator->translate($word);
    }
}
