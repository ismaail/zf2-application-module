<?php
namespace Application\Cache\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Cache as Cache;

class CacheFileSystemFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $sm
     *
     * @return \Zend\Cache\Storage\Adapter\Filesystem
     */
    public function createService(ServiceLocatorInterface $sm)
    {
        $config = $sm->get('config');

        $cache = new Cache\Storage\Adapter\Filesystem(array(
            'cache_dir' => $config['cache']['filesystem']['dir'],
            'ttl'       => $config['cache']['filesystem']['ttl'],
            'dir_level' => 0,
            'file_permission' => 0644,
        ));

        $plugin = new Cache\Storage\Plugin\ExceptionHandler(array(
            'throw_exceptions' => false,
        ));

        $cache->addPlugin($plugin);
        $cache->addPlugin(new Cache\Storage\Plugin\Serializer());

        return $cache;
    }
}
