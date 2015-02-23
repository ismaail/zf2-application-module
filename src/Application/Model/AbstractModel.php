<?php
/**
 * @author  ismaail <contact@ismaail.com>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */
namespace Application\Model;

use Zend\Cache\Storage as CacheStorage;
use Doctrine\ORM\EntityManager;
use RuntimeException;

/**
 * Class AbstractModel
 * @package Application
 */
abstract class AbstractModel
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;
    /**
     * @var string
     */
    protected $entityName;

    /**
     * Holds the cache
     *
     * @var CacheStorage\StorageInterface
     */
    protected $cache;

    /**
     * @var bool
     */
    protected $cacheEnabled = true;

    /**
     * @param \Zend\ServiceManager\ServiceManager $serviceManager
     *
     * @throws \Exception     If entity name not defined
     */
    public function __construct($serviceManager)
    {
        if (! $this->entityName) {
            throw new \Exception(sprintf('entity name no defined for class %s', get_called_class()), 1);
        }

        $this->serviceManager = $serviceManager;
    }

    /**
     * Set the cache
     *
     * @param CacheStorage\StorageInterface $cache
     */
    public function setCache(CacheStorage\StorageInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get the entity's name
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @return \Zend\ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->entityManager = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        }

        return $this->entityManager;
    }

    /**
     * findAll Find all record
     *
     * @return object|null
     */
    public function findAll()
    {
        return $this->em->getRepository($this->entityName)->findAll();
    }

    /**
     * Find single record by param id
     *
     * @param integer $id       Record id
     * @param bool $stripped    Get only id field and ommit other field
     *
     * @return mixed
     */
    public function findOneById($id, $stripped = false)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('e')
           ->from($this->getEntityName(), 'e')
           ->where('e.id = :id')
           ->setParameter('id', $id)
           ;

        if ($stripped) {
            $qb->select('partial e.{id}');
        }

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Rollback entity manager transaction
     */
    public function rollbackTransaction()
    {
        if (null !== $this->em
            && $this->em->getConnection()->isTransactionActive()
        ) {
            $this->em->rollback();
        }
    }

    /**
     * Set query hint to reduce the number of queries using Gedmo translation
     *
     * @param \Doctrine\ORM\Query $query
     */
    public function setQueryHintTranslationWalker(&$query)
    {
        $query->setHint(
            \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );
    }

    /**
     * Set item to cache
     *
     * @param string $key   Key of cache item
     * @param mixed  $item  Item to cache
     * @param array  $tags  List of tags
     *
     * @throws RuntimeException    If no cache system is available
     */
    public function setCacheItem($key, $item, array $tags = null)
    {
        if (! $this->cache) {
            throw new RuntimeException("Cache system not available.");
        }

        if (! $this->isCacheEnabled()) {
            return;
        }

        $this->cache->setItem($key, $item);

        if (null !== $tags) {
            $this->cache->setTags($key, $tags);
        }
    }

    /**
     * Get item from cache
     *
     * @param  string $key Key of cache item
     *
     * @return mixed
     *
     * @throws RuntimeException    If no cache system is available
     */
    public function getCacheItem($key)
    {
        if (! $this->cache) {
            throw new RuntimeException("Cache system not available.");
        }

        if (! $this->isCacheEnabled()) {
            return false;
        }

        $item = $this->cache->getItem($key, $success);

        if ($success) {
            return $item;
        }

        return false;
    }

    /**
     * Clear cache item by key
     *
     * @param string $key
     *
     * @throws RuntimeException    If no cache system is available
     */
    public function clearCacheItem($key)
    {
        if (! $this->cache) {
            throw new RuntimeException("Cache system not available.");
        }

        $this->cache->removeItem($key);
    }

    /**
     * Clear cache item(s) by tags
     *
     * @param array $tags           Tags list
     * @param boolean $disjunction  If true only one of the given tags must match
     *
     * @throws RuntimeException    If no cache system is available
     */
    public function clearCacheByTags(array $tags, $disjunction = false)
    {
        if (! $this->cache) {
            throw new RuntimeException("Cache system not available.");
        }

        $this->cache->clearByTags($tags, $disjunction);
    }

    /**
     * Enable the cache
     */
    public function enableCache()
    {
        $this->cacheEnabled = true;
    }

    /**
     * Disable the cache
     */
    public function disableCache()
    {
        $this->cacheEnabled = false;
    }

    /**
     * Cheack if cache is enabled
     * @return bool
     */
    public function isCacheEnabled()
    {
        return $this->cacheEnabled;
    }
}
