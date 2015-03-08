<?php
namespace Application\Logger\Doctrine;

use Doctrine\DBAL\Logging\SQLLogger;

/**
 * Class FirePhp
 * @package Application\Logger\Doctrine
 */
class FirePhp implements SQLLogger
{
    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * @var int
     */
    private $start = 0;

    /**
     * @var int
     */
    private $end = 0;

    /**
     * @var array
     */
    private $queries = array();

    /**
     * @var null
     */
    private $currentQuery = null;

    /**
     * $logger FirePHP
     */
    private $logger;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        if (! class_exists('FirePHP', true)) {
            throw new \Exception("Class FirePHP not defined");
        }

        $this->logger   = \FirePHP::getInstance(true);
        $this->queries[]  = array('Time', 'Query','Parameters');
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        if (! $this->start) {
            $this->start = \microtime(true);
        }

        $this->currentQuery            = new \stdClass();
        $this->currentQuery->sql       = $sql;
        $this->currentQuery->params    = $params;
        $this->currentQuery->types     = $types;
        $this->currentQuery->startTime = \microtime(true);

        if ($params) {
        }

        if ($types) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        $executionMS = \microtime(true) - $this->currentQuery->startTime;

        $this->queries[] = array(
            number_format($executionMS, 4),
            $this->currentQuery->sql,
            $this->currentQuery->params,
        );

        $this->end = \microtime(true);
    }

    /**
     * showTable dispaly FirePHP table
     */
    public function showTable()
    {
        if (headers_sent()) {
            return;
        }

        if (! empty($this->queries) && count($this->queries) > 1) {
            $this->logger->table(
                sprintf(
                    'Doctrine Query Logs (%d @ %s sec)',
                    count($this->queries) - 1,
                    number_format($this->end - $this->start, 4)
                ),
                $this->queries
            );
        }
    }
}
