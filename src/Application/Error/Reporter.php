<?php
namespace Application\Error;

use Application\Exception\Exception;
use Application\Exception\Parser as ExceptionParser;
use Application\Service\Mail\Mail;

/**
 * Class Reporter
 * @package Application\Error
 */
class Reporter
{
    /**
     * @var \Exception
     */
    protected $exception;

    /**
     * @var \Zend\Http\PhpEnvironment\Request
     */
    protected $request;

    /**
     * @param \Exception $exception
     * @param \Zend\Http\PhpEnvironment\Request $request
     */
    public function __construct($exception, $request)
    {
        $this->exception = $exception;
        $this->request    = $request;
    }

    /**
     * Send the report
     *
     * @param array $config
     *
     * @throws Exception    If mail configuration not found
     */
    public function sendReport($config)
    {
        if (! isset($config['mail']) || ! $config['mail']['debug']) {
            throw new Exception("Mail configuration not found");
        }

        $mailer   = new Mail($config['mail']['debug']);
        $template = $config['view_manager']['template_map']['template/email/error/html'];
        $mailer->setTemplates(array('html' => $template));

        $mailer->send([
            'exceptions' => (new ExceptionParser())->parse($this->exception),
            'request'    => $this->prepareRequestParams($this->request),
        ]);
    }

    /**
     * @param \Zend\Http\PhpEnvironment\Request $request
     *
     * @return array
     */
    protected function prepareRequestParams($request)
    {
        $response = array(
            // Server
            'userAgent' => $request->getHeaders()->get('User-Agent')->getFieldValue(),
            'remoteIP'  => $request->getServer()->get('SERVER_ADDR'),
            'referer'   => (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : null,

            // Request
            'host' => $request->getUri()->getHost(),
            'uri'  => $request->getRequestUri(),
            'type' => (isset($_SERVER['HTTP_X_REQUESTED_WITH']))
                ? $_SERVER['HTTP_X_REQUESTED_WITH']
                : null,
            'params' => array(
                'query' => $request->getQuery()->toArray(),
                'post'  => $request->getPost()->toArray(),
            ),
        );

        return $response;
    }
}
