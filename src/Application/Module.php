<?php
namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Di\Di;
use Zend\Mail\Transport;
use Zend\Cache;
use Application\Service;

/**
 * Class Module
 * @package Application
 */
class Module
{
    public function onBootstrap(MvcEvent $event)
    {
        //$e->getApplication()->getServiceManager()->get('translator');
        $eventManager        = $event->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        /*
         Change layout for Error
         */
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'changeErrorLayout']);

        /*
         FirehpProfiler - Doctrine2 queries
         */
        if ('production' !== APPLICATION_ENV) {
            $this->enableFirePhpProfiler($event);
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

    /**
     * Change layout for errors
     *
     * @param MvcEvent $event
     */
    public function changeErrorLayout(MvcEvent $event)
    {
        $config = $event->getApplication()->getServiceManager()->get('config');
        $event->getViewModel()->setTemplate($config['error_handler']['error_layout']);
    }

    /**
     * @param MvcEvent $event
     */
    public function enableFirePhpProfiler(MvcEvent $event)
    {
        $serviceManager = $event->getApplication()->getServiceManager();
        $eventManager   = $event->getApplication()->getEventManager();

        // Attach FirePhp logger to the EntityManager
        $serviceManager->get('doctrine.entitymanager.orm_default')
            ->getConfiguration()->setSQLLogger($serviceManager->get('FirePhpLogger'));

        // Show FirePhp table logger at finish event
        $eventManager->attach(
            MvcEvent::EVENT_FINISH,
            function () use ($serviceManager) {
                $profiler = $serviceManager->get('FirePhpLogger');
                $profiler->showTable();
            },
            100
        );
    }
}
