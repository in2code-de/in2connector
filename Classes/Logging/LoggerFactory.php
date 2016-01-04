<?php
namespace In2code\In2connector\Logging;

use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class LoggerFactory
 */
class LoggerFactory
{
    /**
     * @param $class
     */
    public static function getLogger($class)
    {
        return GeneralUtility::makeInstance(LogManager::class)->getLogger($class);
    }
}
