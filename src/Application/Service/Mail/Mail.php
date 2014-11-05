<?php
namespace Application\Service\Mail;

use Zend\Validator;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;
use Zend\View\Model\ViewModel;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Message as MimeMessage;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail;

/**
 * Class Mail
 * @package Application\Service\Mail
 */
class Mail
{
    /**
     * @var string
     */
    protected $to;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var array
     */
    protected $templates;

    /**
     * @var Validator\EmailAddress
     */
    protected $emailValidator;

    /**
     * @var bool
     */
    protected $sendEnabled = true;

    /**
     * Set email sender
     *
     * @param $from
     *
     * @throws \InvalidArgumentException
     */
    public function setFrom($from)
    {
        if (! $this->isValidEmail($from)) {
            throw new \InvalidArgumentException(sprintf("Invalid email 'From': %s", $from));
        }

        $this->from = $from;
    }

    /**
     * Set email recipient
     * @param $to
     *
     * @throws \InvalidArgumentException
     */
    public function setTo($to)
    {
        if (! $this->isValidEmail($to)) {
            throw new \InvalidArgumentException(sprintf("Invalid email 'To': %s", $to));
        }

        $this->to = $to;
    }

    /**
     * Set subject
     *
     * @param $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Set templates
     *
     * @param array $templates
     */
    public function setTemplates($templates)
    {
        $this->templates = $templates;
    }

    /**
     * Disable sending the email
     */
    public function disableSend()
    {
        $this->sendEnabled = false;
    }

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        if (! $options) {
            return;
        }

        foreach ($options as $property => $value) {
            if (property_exists(__CLASS__, $property)) {
                $this->{'set'.ucfirst($property)}($value);
            }
        }
    }

    /**
     * Check if email value is valid
     *
     * @param string $email
     *
     * @return bool
     */
    protected function isValidEmail($email)
    {
        if (! $this->emailValidator) {
            $validator = new Validator\EmailAddress();
            $this->emailValidator = $validator;
        }

        return $this->emailValidator->isValid($email);
    }

    /**
     * Prepare email body part using templates
     *
     * @param $data
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    protected function prepareBodyParts($data)
    {
        if (! $this->templates || ! is_array($this->templates)) {
            throw new \InvalidArgumentException("Invalid templates list to process");
        }

        foreach ($this->templates as $template) {
            if (false === realpath($template)) {
                throw new \Exception(sprintf("Template not found at path: \n%s", $template));
            }
        }

        $parts = array();

        $resolver = new TemplateMapResolver();
        $resolver->setMap($this->templates);

        // create email templates
        $view = new PhpRenderer();
        $view->setResolver($resolver);

        // Text
        if (array_key_exists('text', $this->templates)) {
            $textView = new ViewModel();
            $textView->setTemplate('text')
                     ->setVariables($data);
            $textContent = $view->render($textView);
            $textBody = new MimePart($textContent);
            $textBody->type = 'text/plain';

            $parts[] = $textBody;
        }

        // HTML
        if (array_key_exists('html', $this->templates)) {
            $htmlView = new ViewModel();
            $htmlView->setTemplate('html')
                     ->setVariables($data);
            $htmlContent = $view->render($htmlView);
            $htmlBody = new MimePart($htmlContent);
            $htmlBody->type = 'text/html';

            $parts[] = $htmlBody;
        }

        if (empty($parts)) {
            throw new \Exception("Not email part available");
        }

        return $parts;
    }

    /**
     * Send the email
     *
     * @param array $data
     *
     * @return string       Message body
     */
    public function send(array $data)
    {
        $body = new MimeMessage();
        $body->setParts($this->prepareBodyParts($data));

        $message = new Message();
        $message->setEncoding('UTF-8')
                ->addFrom($this->from)
                ->addTo($this->to)
                ->setSubject($this->subject)
                ->setBody($body)
                ;

        // If there is more than one template, mark email as multipart
        if (count($this->templates) > 1) {
            $message->getHeaders()->get('content-type')->setType('multipart/alternative');
        }

        $transport = new Sendmail();

        if ($this->sendEnabled) {
            $transport->send($message);
        }

        return $message->getBodyText();
    }
}
