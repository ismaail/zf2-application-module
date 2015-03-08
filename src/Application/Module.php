<?php
namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Mail\Transport;
use Zend\Cache;
use Application\Service;
use Application\Error\Reporter as ErrorReporter;

/**
 * Class Module
 * @package Application
 */
class Module
{
    /**
     * @param MvcEvent $event
     */
    public function onBootstrap(MvcEvent $event)
    {
        $eventManager        = $event->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        // Change layout for Error
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'changeErrorLayout']);

        // Error reporing
        if ('production' === APPLICATION_ENV) {
            $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'sendErrorReport']);
        }

        // FirehpProfiler - Doctrine2 queries
        if ('development' === APPLICATION_ENV) {
            $this->enableFirePhpProfiler($event);
        }
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * @return array
     */
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
     * Send email error report
     *
     * @param MvcEvent $event
     */
    public function sendErrorReport(MvcEvent $event)
    {
        // Cancel if no exception found
        $exception = $event->getParam('exception');
        if (null === $exception) {
            return;
        }

        $config = $event->getApplication()->getServiceManager()->get('config');

        // Cancel if send options is not true
        if (! $config['error_handler']['send_report']) {
            return;
        }

        $reporter = new ErrorReporter($exception, $event->getRequest());
        $reporter->sendReport($config);
    }

    /**
     * @param MvcEvent $event
     */
    public function enableFirePhpProfiler(MvcEvent $event)
    {
        $serviceManager = $event->getApplication()->getServiceManager();
        $eventManager   = $event->getApplication()->getEventManager();

        try {
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
        } catch (\Exception $e) {
            $logger = $serviceManager->get('logger');
            $logger->error('Error enabling FirePHP profiler');
            $logger->error($e);
        }
    }
}
