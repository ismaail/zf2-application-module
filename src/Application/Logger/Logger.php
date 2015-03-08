<?php
namespace Application\Logger;

use Zend\Log;
use Zend\Log\Writer;
use FirePHP;

/**
 * Class Logger
 * @package Application\Logger
 */
class Logger
{
    /**
     * @var Log\Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $exceptions = array();

    /**
     * @var array
     */
    protected $priorityList = array(
        0 => 'emerg',  // Emergency: system is unusable
        1 => 'alert',  // Alert: action must be taken immediately
        2 => 'crit',   // Critical: critical conditions
        3 => 'err',    // Error: error conditions
        4 => 'warn',   // Warning: warning conditions
        5 => 'notice', // Notice: normal but significant condition
        6 => 'info',   // Informational: informational messages
        7 => 'debug',  // Debug: debug messages
    );

    /**
     * Constructor
     *
     * @todo Get path to save log file from application config
     */
    public function __construct()
    {
        if ('development' === APPLICATION_ENV) {
            if (! class_exists('FirePHP', true)) {
                throw new \Exception("Class FirePHP not defined");
            }

            $firephp = FirePHP::getInstance(true);
            $this->logger = $firephp;

        } else {
            $logger = new Log\Logger();
            $writer = new Writer\Stream('data/log/application.log');
            $logger->addWriter($writer);

            $this->logger = $logger;
        }
    }

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
        if (headers_sent()) {
            return;
        }

        if ($args[0] instanceof \Exception
            && 'development' === APPLICATION_ENV
        ) {
            $this->logExceptions($args[0]);
            return;
        }

        // Fix "err" vs "error" for different logger type
        if ('development' === APPLICATION_ENV && 'err' === $name) {
            $name = 'error';
        } elseif ('development' !== APPLICATION_ENV && 'error' === $name) {
            $name = 'err';
        }

        $this->logger->{$name}($args[0]);
    }

    /**
     * @return Log\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Log exceptions as groups
     *
     * @param $exceptions
     */
    protected function logExceptions($exceptions)
    {
        $this->exceptions = array();

        $this->appendExecption($exceptions);
        $this->getNestedPreviousExceptions($exceptions);

        foreach ($this->exceptions as $e) {
            $this->logger->group($e['class']);
            $this->logger->error($e['message']);
            $this->logger->error($e['file'] . ', line:' . $e['line']);
            // $this->logger->error($e['trace']);
            $this->logger->groupEnd();
        }
    }

    /**
     * Get previous exception
     *
     * @param $exception
     */
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

    /**
     * Extract exception info to an array and add it exceptions collections
     *
     * @param $exception
     */
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
