<?php
namespace Application\Translation;

use Locale;
use Exception;
use Zend\Mvc\I18n\Translator;
use Zend\Validator\AbstractValidator;

/**
 * Class Translation
 * @package Application\Translation
 */
class Translation
{
    /**
     * @var \Zend\I18n\Translator\Translator $translator
     */
    protected $translator;

    /**
     * @var array $config
     */
    protected $config;

    /**
     * @var string $locale
     */
    protected $locale;

    /**
     * @var string $language
     */
    protected $language;

    /**
     * @param $translator
     * @param array $config
     */
    public function __construct($translator, array $config)
    {
        $this->translator = $translator;
        $this->config     = $config;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function setLocale()
    {
        if (! $this->locale) {
            $this->translator->setLocale(
                Locale::acceptFromHttp(
                    isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : 'en-US,en;q=0.5'
                )
            )->setFallbackLocale('en_US');

            if (! isset($this->config['languages'])) {
                throw new Exception("locale configuration not found");
            }

            $this->prepareLanguage();

            $this->setFormTranslation();

            /** IMPORTANT: Locale default is used to refere to .po language file */
            Locale::setDefault($this->locale);
        }

        return $this->locale;
    }

    /**
     * Extract language from locale, ex: 'en_US' to 'en'
     */
    protected function prepareLanguage()
    {
        $this->locale = $this->config['default'];

        if (array_key_exists($this->translator->getLocale(), $this->config['languages'])) {
            $this->locale = $this->translator->getLocale();
        }

        $this->translator->setLocale($this->locale);

        $language = explode('_', $this->locale)[0];
        $this->language = $language;
    }

    /**
     * Load form error messages translation
     */
    protected function setFormTranslation()
    {
        $formTranslator =  new Translator($this->translator);
        $formTranslator->addTranslationFile(
            'phpArray',
            './vendor/zendframework/zendframework/resources/languages/'. $this->getLanguage() .'/Zend_Validate.php'
        );
        $formTranslator->addTranslationFile(
            'phpArray',
            './module/SCMS/language/'. $this->getLanguage() .'/Zend_Validate.php'
        );

        AbstractValidator::setDefaultTranslator($formTranslator);
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }
}
