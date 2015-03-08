<?php
namespace Application\Form\Validator;

use Zend\Validator\AbstractValidator;

/**
 * Class Confirm
 * @package Application\Form
 */
class Confirm extends AbstractValidator
{
    /**
     * @const string
     */
    const DIFFERENT_FROM = 'DIFFERENT_FROM';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::DIFFERENT_FROM => 'Value differs from original one',
    );

    /**
     * @var mixed
     */
    private $field;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        if (! isset($options['field'])) {
            throw new \InvalidArgumentException('Field to check missing');
        }

        $this->field = $options['field'];

        parent::__construct($options);
    }

    /**
     * @param mixed $value
     * @param null $context
     *
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        if (! is_array($context) or ! isset($context[$this->field])) {
            throw new \RuntimeException(sprintf('Field "%s" missing in the context', $this->field));
        }

        $this->setValue($value);

        if ($value !== $context[$this->field]) {
            $this->error(self::DIFFERENT_FROM);

            return false;
        }

        return true;
    }
}
