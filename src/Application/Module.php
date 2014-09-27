<?php
namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Di\Di;
use Zend\Mail\Transport;
use Zend\Cache;
use Application\Service;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        //$e->getApplication()->getServiceManager()->get('translator');
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $sm = $e->getApplication()->getServiceManager();

        /*
         Errors handling
         */
        if ('production' === APPLICATION_ENV) {
            $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function ($e) use ($sm) {

                $exception = $e->getParam('exception');

                // disable layout
                $result = $e->getResult();
                $result->setTerminal(true);

                $errorHandler = $sm->get('ErrorHandler');

                if ($exception) {
                    // Execution error
                    $errorHandler->run($exception, $e->getRequest());

                } else {
                    // 404 error
                    //TODO: send tempalte 404.phtml by email

                    $result->setTemplate('error/production_404.phtml');
                }
            });
        }

        /*
         FirehpProfiler - Doctrine2 queries
         */
        if ('production' !== APPLICATION_ENV) {
            $serviceManager = $e->getApplication()->getServiceManager();

            // set firephp logger to entitymanager
            $serviceManager->get('doctrine.entitymanager.orm_default')
                ->getConfiguration()->setSQLLogger($serviceManager->get('FirePhpLogger'));

            // fire logger at finish event
            $eventManager->attach(
                MvcEvent::EVENT_FINISH,
                function ($e) use ($serviceManager) {
                    $profiler = $serviceManager->get('FirePhpLogger');
                    $profiler->showTable();
                },
                100
            );
        }
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'mail_transport' => function ($sm) {
                    $config = $sm->get('Config');
                    $transport = new Transport\Smtp();
                    $transport->setOptions(new Transport\SmtpOptions($config['mail']['transport']['options']));

                    return $transport;
                },
                'Zend\Authentication\AuthenticationService' => function ($sm) {
                    return $sm->get('doctrine.authenticationservice.orm_default');
                },
                'ErrorHandler' => function ($sm) {
                    $config = $sm->get('config');
                    $di     = new Di();
                    $di->instanceManager()->setParameters('Application\Controller\Plugin\ErrorHandler', array(
                        'mailTransport' => $sm->get('mail_transport'),
                        'mailConfig'    => $config['mail']['debug'],
                    ));

                    return $twitter = $di->get('Application\Controller\Plugin\ErrorHandler');
                },
            ),
            'invokables' => array(
                'FirePhpLogger' => 'Application\Logger\Doctrine\FirePhp',
            ),
        );
    }
}
