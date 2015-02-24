<?php
namespace Application\Model;

use Zend\ServiceManager\ServiceManager;

/**
 * Class QueryBuilder
 * @package Application\Model
 */
class QueryBuilder
{
    private $data = [
        'leftJoin'  => [],
        'orderBy'   => [],
        'where'     => [],
        'andWhere'  => [],
        'parameter' => [],
        'groupBy'   => [],
    ];

    /**
     * @param  ServiceManager $serviceManager
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function process(ServiceManager $serviceManager)
    {
        $qb = $serviceManager->get('doctrine.entitymanager.orm_default')->createQueryBuilder();

        $qb->select($this->data['select'])
           ->from($this->data['from'][0], $this->data['from'][1]);

        if (! empty($this->data['leftJoin'])) {
            foreach ($this->data['leftJoin'] as $join) {
                $qb->leftJoin($join[0], $join[1]);
            }
        }

        if (! empty($this->data['orderBy'])) {
            foreach ($this->data['orderBy'] as $order) {
                $qb->orderBy($order[0], $order[1]);
            }
        }

        if (! empty($this->data['where'])) {
            foreach ($this->data['where'] as $condition) {
                $qb->where($condition);
            }
        }

        if (! empty($this->data['andWhere'])) {
            foreach ($this->data['andWhere'] as $condition) {
                $qb->andWhere($condition);
            }
        }

        if (! empty($this->data['parameter'])) {
            foreach ($this->data['parameter'] as $parameter) {
                $qb->setParameter($parameter[0], $parameter[1]);
            }
        }

        if (! empty($this->data['groupBy'])) {
            foreach ($this->data['groupBy'] as $value) {
                $qb->groupBy($value);
            }
        }

        return $qb;
    }

    /**
     * @param  string|array $query
     *
     * @return QueryBuilder
     */
    public function select($query)
    {
        $this->data['select'] = $query;

        return $this;
    }

    /**
     * @param  string $entityName
     * @param  string $alias
     *
     * @return QueryBuilder
     */
    public function from($entityName, $alias)
    {
        $this->data['from'] = [$entityName, $alias];

        return $this;
    }

    /**
     * @param  string $name
     * @param  string $alias
     *
     * @return QueryBuilder
     */
    public function leftJoin($name, $alias)
    {
        array_push($this->data['leftJoin'], [$name, $alias]);

        return $this;
    }

    /**
     * @param  string $name
     * @param  string $direction
     *
     * @return QueryBuilder
     */
    public function orderBy($name, $direction = 'asc')
    {
        array_push($this->data['orderBy'], [$name, $direction]);

        return $this;
    }

    /**
     * @param  string $condition
     *
     * @return QueryBuilder
     */
    public function where($condition)
    {
        array_push($this->data['where'], $condition);

        return $this;
    }

    /**
     * @param  string $condition
     *
     * @return QueryBuilder
     */
    public function andWhere($condition)
    {
        array_push($this->data['andWhere'], $condition);

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return QueryBuilder
     */
    public function setParameter($name, $value)
    {
        array_push($this->data['parameter'], [$name, $value]);

        return $this;
    }

    /**
     * @param  string $value
     *
     * @return QueryBuilder
     */
    public function groupBy($value)
    {
        array_push($this->data['groupBy'], $value);

        return $this;
    }
}
