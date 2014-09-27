<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Captcha;
use Zend\View\Model\JsonModel;

/**
 * Class CaptchaController
 * @package User\Controller
 */
class CaptchaController extends AbstractActionController
{
    /**
     * Regenerate captcha
     *
     * @return JsonModel
     */
    public function regenerateAction()
    {
        $this->checkRequest();

        $captcha = new Captcha\Image(array(
            'wordLen'        => 5,
            'font'           => './data/fonts/arial_bold.ttf',
            'fontSize'       => 16,
            'width'          => 130,
            'height'         => 45,
            'dotNoiseLevel'  => 6,
            'lineNoiseLevel' => 0,
            'imageDir'       => './public/images/captcha/',
            'imgAlt'         => 'captcha',
        ));

        $captcha->generate();

        return new JsonModel(array(
            'id'  => $captcha->getId(),
            'url' => $captcha->getImgUrl() . $captcha->getId() . $captcha->getSuffix(),
        ));
    }

    /**
     * Check if the request is not cross-origin
     *
     * @throws \Exception
     */
    protected function checkRequest()
    {
        if (! isset($_SERVER['HTTP_REFERER'])) {
            $this->getResponse()->setStatusCode(403);
            throw new \Exception("Request not allowed");
        }

        $params = parse_url($_SERVER['HTTP_REFERER']);

        if ($params['host'] !== $_SERVER['HTTP_HOST']) {
            $this->getResponse()->setStatusCode(428);
            throw new \Exception("Request not authorized");
        }
    }
}
