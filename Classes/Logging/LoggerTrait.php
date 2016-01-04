<?php
namespace In2code\In2connector\Logging;

use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class LoggerTrait
 */
trait LoggerTrait
{
    /**
     * You should always use $this->getLogger() instead of $this->logger, because it might not be secured, that the
     * logger was initialized already
     *
     * @var Logger
     */
    protected $logger = null;

    /**
     * @return Logger
     * @api
     */
    protected function getLogger()
    {
        if (null === $this->logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(get_class($this));
        }
        return $this->logger;
    }
}
