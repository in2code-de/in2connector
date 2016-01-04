<?php
namespace In2code\In2connector\Service;

use In2code\In2connector\Domain\Model\Dto\Configuration;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Wraps the TYPO3 registry for convenient usage to retrieve special values fast and easily
 */
class ConfigurationService implements SingletonInterface
{
    const DEFAULT_LOGS_PER_PAGE = 25;
    const DEFAULT_PRODUCTION_CONTEXT = true;
    const LOG_LEVEL = 'log_level';
    const LOGS_PER_PAGE = 'logs_per_page';
    const PRODUCTION_CONTEXT = 'production_context';

    /**
     * @var Registry
     */
    protected $registry = null;

    /**
     * @var int
     */
    protected $logLevel = LogLevel::DEBUG;

    /**
     * @var int
     */
    protected $logsPerPage = self::DEFAULT_LOGS_PER_PAGE;

    /**
     * @var bool
     */
    protected $productionContext = self::DEFAULT_PRODUCTION_CONTEXT;

    /**
     * ConfigurationService constructor.
     */
    public function __construct()
    {
        $this->registry = GeneralUtility::makeInstance(Registry::class);
        $this->logLevel = $this->registry->get(TX_IN2CONNECTOR, self::LOG_LEVEL, $this->logLevel);
        $this->logsPerPage = $this->registry->get(TX_IN2CONNECTOR, self::LOGS_PER_PAGE, $this->logsPerPage);
        $this->productionContext = $this->registry->get(
            TX_IN2CONNECTOR,
            self::PRODUCTION_CONTEXT,
            $this->productionContext
        );
    }

    /**
     * @return Configuration
     */
    public function getConfigurationDto()
    {
        $configuration = new Configuration();
        $configuration->setLogLevel($this->logLevel);
        $configuration->setLogsPerPage($this->logsPerPage);
        $configuration->setProductionContext($this->productionContext);
        return $configuration;
    }

    /**
     * @param Configuration $configuration
     */
    public function updateFromConfigurationDto(Configuration $configuration)
    {
        $this->setLogLevel($configuration->getLogLevel());
        $this->setLogsPerPage($configuration->getLogsPerPage());
        $this->setProductionContext($configuration->isProductionContext());
    }

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
        if (($logLevel >= LogLevel::EMERGENCY) && ($logLevel <= LogLevel::DEBUG)) {
            $this->registry->set(TX_IN2CONNECTOR, self::LOG_LEVEL, $logLevel);
            $this->logLevel = $logLevel;
        }
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
        $this->registry->set(TX_IN2CONNECTOR, self::LOGS_PER_PAGE, $logsPerPage);
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
        $this->registry->set(TX_IN2CONNECTOR, self::PRODUCTION_CONTEXT, $productionContext);
        $this->productionContext = $productionContext;
    }
}
