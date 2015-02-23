<?php
namespace Application\Paginator;

use Zend\Paginator\Adapter\AdapterInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\NoResultException;

/**
 * Class Adapter
 * @package Blog\Paginator
 */
class Adapter implements AdapterInterface
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var mixed
     */
    protected $queryBuilder;

    /**
     * @var \Zend\Cache\Storage\StorageInterface
     */
    protected $cache;

    /**
     * @var string
     */
    protected $cacheKey;

    /**
     * @var array
     */
    protected $cacheTags;

    /**
     * @var int
     */
    protected $count;

    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * @param mixed $queryBuilder
     * @param array $options
     */
    public function __construct($queryBuilder, $serviceManager, $options = [])
    {
        $this->queryBuilder   = $queryBuilder;
        $this->serviceManager = $serviceManager;

        if ($options) {
            foreach ($options as $property => $value) {
                if (property_exists(__CLASS__, $property)) {
                    $this->{'set'.ucfirst($property)}($value);
                }
            }
        }
    }

    /**
     * @param \Zend\Cache\Storage\StorageInterface $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $cacheKey
     */
    public function setCacheKey($cacheKey)
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * @param array $cacheTags
     */
    public function setCacheTags($cacheTags)
    {
        $this->cacheTags = $cacheTags;
    }

    /**
     * Returns a collection of items for a page.
     *
     * @param  int $offset           Page offset
     * @param  int $itemCountPerPage Number of items per page
     *
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $cacheKey = null;

        if (null !== $this->cacheKey) {
            $cacheKey = sprintf($this->cacheKey . '_%d_%d', $offset, $itemCountPerPage);
        }

        if ($this->cacheKey && $this->cache) {
            $items = $this->cache->getItem($cacheKey, $success);

            if (false !== $success) {
                $this->items = $items;
                return $this->items;
            }
        }

        $this->items = $this->executeResultQuery($offset, $itemCountPerPage);

        if ($cacheKey && $this->cache) {
            $this->cache->setItem($cacheKey, $this->items);
            if (null !== $this->cacheTags) {
                $this->cache->setTags($cacheKey, $this->cacheTags);
            }
        }

        return $this->items;
    }

    /**
     * @param int $offset
     * @param int $maxResult
     *
     * @return array
     */
    protected function executeResultQuery($offset, $maxResult)
    {
        $this->queryBuilder->setFirstResult($offset);
        $this->queryBuilder->setMaxResults($maxResult);

        return $this->queryBuilder->getQuery()->getResult();
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        if (null === $this->count) {
            // Get from cache
            if ($this->cacheKey && $this->cache) {
                $count = $this->cache->getItem($this->cacheKey, $success);
                if (false !== $success) {
                    $this->count = $count;
                    return $this->count;
                }
            }

            // Get doctrine querybuilder
            $this->queryBuilder = $this->queryBuilder->process($this->serviceManager);

            $countQuery = $this->cloneQuery($this->queryBuilder->getQuery());

            $platform = $countQuery->getEntityManager()->getConnection()->getDatabasePlatform(); // law of demeter win

            $rsm = new ResultSetMapping();
            $rsm->addScalarResult($platform->getSQLResultCasing('dctrn_count'), 'count');

            $countQuery->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, 'Doctrine\ORM\Tools\Pagination\CountOutputWalker');
            $countQuery->setResultSetMapping($rsm);

            $countQuery->setFirstResult(null)->setMaxResults(null);

            try {
                $data =  $countQuery->getScalarResult();
                $data = array_map('current', $data);
                $this->count = array_sum($data);

            } catch (NoResultException $e) {
                $this->count = 0;
            }

            // Save to cache
            if ($this->cacheKey && $this->cache) {
                $this->cache->setItem($this->cacheKey, $this->count);
                if (null !== $this->cacheTags) {
                    $this->cache->setTags($this->cacheKey, $this->cacheTags);
                }
            }
        }

        return $this->count;
    }

    /**
     * Clones a query.
     *
     * @param Query $query The query.
     *
     * @return Query The cloned query.
     */
    private function cloneQuery(Query $query)
    {
        /* @var $cloneQuery Query */
        $cloneQuery = clone $query;

        $cloneQuery->setParameters(clone $query->getParameters());

        foreach ($query->getHints() as $name => $value) {
            $cloneQuery->setHint($name, $value);
        }

        return $cloneQuery;
    }
}
