<?php
namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;
use Zend\Mail\Message;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Model\ViewModel;
use Zend\Mail\Transport\Sendmail;

class ErrorHandler extends AbstractPlugin
{
    protected $exceptions = array();

    protected $mailTransport;

    protected $mailConfig;

    protected $request;

    /**
     * setMailTransport
     */
    public function setMailTransport($mailTransport)
    {
        $this->mailTransport = $mailTransport;
    }

    /**
     * setMailConfig
     */
    public function setMailConfig($mailConfig)
    {
        $this->mailConfig = $mailConfig;
    }

    public function run($exception, $request)
    {
        $this->request = $request;

        $this->appendExecption($exception);
        $this->getNestedPreviousExceptions($exception);

        if ('production' === APPLICATION_ENV) {
            $this->sendMail();
        }
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

    /**
     * Send E-mail message
     *
     * @todo Add method to set template file
     * @todo Create plain text template file
     */
    protected function sendMail()
    {
        // create email templates
        $view     = new PhpRenderer();
        $resolver = new TemplateMapResolver();
        $resolver->setMap(array(
            //'mailLayout' => __DIR__ . '/../../Application/view/layout/layout-mail.phtml',
            'mailTemplate' => __DIR__ . '/../../view/error/emailTemplates/htmlError.phtml',
        ));

        $view->setResolver($resolver);

        $viewModel = new ViewModel();
        $viewModel->setTemplate('mailTemplate')
                  ->setVariables(array(
                      // Server
                      'userAgent'   => $this->request->getHeaders()->get('User-Agent')->getFieldValue(),
                      'remoteIP'    => $this->request->getServer()->get('SERVER_ADDR'),
                      'referer'     => (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : null,
                      // Request
                      'requestHost' => $this->request->getUri()->getHost(),
                      'requestUri'  => $this->request->getRequestUri(),
                      'requestType' => (isset($_SERVER['HTTP_X_REQUESTED_WITH']))
                                       ? $_SERVER['HTTP_X_REQUESTED_WITH']
                                       : null,
                      'params'      => array(
                          'query' => $this->request->getQuery()->toArray(),
                          'post'  => $this->request->getPost()->toArray(),
                      ),
                      // exception
                      'exceptions'  => $this->exceptions,
                  ));

        $htmlContent = $view->render($viewModel);

        $htmlBody = new MimePart($htmlContent);
        $htmlBody->type = 'text/html';

        $textBody = new MimePart('An error occurred');
        $textBody->type = 'text/plain';

        $body     = new MimeMessage();
        $body->setParts(array($textBody, $htmlBody));

        $message  = new Message();
        $message->setEncoding('UTF-8')
                ->addFrom($this->mailConfig['from'])
                ->addTo($this->mailConfig['to'])
                ->setSubject($this->mailConfig['subject'])
                ->setBody($body)
                ->getHeaders()->get('content-type')->setType('multipart/alternative')
                ;

        $transport = new Sendmail();

        if ('production' !== APPLICATION_ENV) {
            $transport = $this->mailTransport;
        }

        $transport->send($message);
    }
}
