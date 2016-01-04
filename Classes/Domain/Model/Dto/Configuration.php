<?php
namespace In2code\In2connector\Domain\Model\Dto;

use In2code\In2connector\Service\ConfigurationService;
use TYPO3\CMS\Core\Log\LogLevel;

/**
 * Class Configuration
 */
class Configuration
{
    /**
     * @var int
     */
    protected $logLevel = LogLevel::DEBUG;

    /**
     * @var int
     */
    protected $logsPerPage = ConfigurationService::DEFAULT_LOGS_PER_PAGE;

    /**
     * @var bool
     */
    protected $productionContext = ConfigurationService::DEFAULT_PRODUCTION_CONTEXT;

    /**
     * @return int
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * @param int $logLevel
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
    }

    /**
     * @return int
     */
    public function getLogsPerPage()
    {
        return $this->logsPerPage;
    }

    /**
     * @param int $logsPerPage
     */
    public function setLogsPerPage($logsPerPage)
    {
        $this->logsPerPage = $logsPerPage;
    }

    /**
     * @return boolean
     */
    public function isProductionContext()
    {
        return $this->productionContext;
    }

    /**
     * @param boolean $productionContext
     */
    public function setProductionContext($productionContext)
    {
        $this->productionContext = $productionContext;
    }
}
