<?php
namespace Application\Exception;

/**
 * Class Parser
 * @package Application\Exception
 */
class Parser
{
    /**
     * @var array
     */
    protected $exceptions = array();

    /**
     * @param \Exception $exception
     *
     * @return array
     */
    public function parse($exception)
    {
        $this->appendExecption($exception);
        $this->getNestedPreviousExceptions($exception);

        return $this->exceptions;
    }

    protected function getNestedPreviousExceptions($exception)
    {
        $previous = $exception->getPrevious();

        // exit loop if previous exception equals this class
        if (null === $previous) {
            return;
        }

        $this->appendExecption($previous);
        $this->getNestedPreviousExceptions($previous);
    }

    protected function appendExecption($exception)
    {
        $this->exceptions[] = array(
            'class'   => get_class($exception),
            'code'    => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'trace'   => $exception->getTraceAsString(),
        );
    }
}
