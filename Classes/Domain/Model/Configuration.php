<?php
namespace In2code\In2connector\Domain\Model;

use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 */
class Configuration
{
    const REGISTRY_NAMESPACE = 'tx_in2connector';
    const LOG_LEVEL = 'logLevel';
    const LOG_ENTRIES_PER_PAGE = 'logEntriesPerPage';

    /**
     * @var int
     */
    protected $logLevel = LogLevel::DEBUG;

    /**
     * @var int
     */
    protected $logEntriesPerPage = 20;

    /**
     * Configuration constructor.
     */
    public function __construct()
    {
        $registry = GeneralUtility::makeInstance(Registry::class);
        $this->logLevel = $registry->get(
            self::REGISTRY_NAMESPACE,
            self::LOG_LEVEL,
            $this->logLevel
        );
        $this->logEntriesPerPage = $registry->get(
            self::REGISTRY_NAMESPACE,
            self::LOG_ENTRIES_PER_PAGE,
            $this->logEntriesPerPage
        );
    }

    /**
     *
     */
    public function persist()
    {
        $registry = GeneralUtility::makeInstance(Registry::class);
        $registry->set(
            self::REGISTRY_NAMESPACE,
            self::LOG_LEVEL,
            $this->logLevel
        );
        $registry->set(
            self::REGISTRY_NAMESPACE,
            self::LOG_ENTRIES_PER_PAGE,
            $this->logEntriesPerPage
        );
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
        $this->logLevel = $logLevel;
    }

    /**
     * @return int
     */
    public function getLogEntriesPerPage()
    {
        return $this->logEntriesPerPage;
    }

    /**
     * @param int $logEntriesPerPage
     */
    public function setLogEntriesPerPage($logEntriesPerPage)
    {
        $this->logEntriesPerPage = $logEntriesPerPage;
    }
}
