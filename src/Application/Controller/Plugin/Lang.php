<?php
namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Class Lang
 * @package Application\Controller\Plugin
 *
 * Get locale language
 */
class Lang extends AbstractPlugin
{
    /**
     * @var string $language
     */
    protected $language;

    /**
     * @return string
     */
    public function __invoke()
    {
        if (! $this->language) {
            /** @var \Application\Translation\Translation $translation */
            $translation = $this->getController()->getServiceLocator()->get('translation/translation');
            $this->language = $translation->getLanguage();
        }

        return $this->language;
    }
}
