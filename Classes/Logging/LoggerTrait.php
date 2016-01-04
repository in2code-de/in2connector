<?php
namespace In2code\In2connector\Logging;

use TYPO3\CMS\Core\Log\Logger;

/**
 * Class LoggerTrait
 */
trait LoggerTrait
{
    /**
     * @var null|Logger
     */
    protected $logger = null;

    /**
     * @return Logger
     */
    public function getLogger()
    {
        if (null === $this->logger) {
            $this->logger = LoggerFactory::getLogger(get_class($this));
        }
        return $this->logger;
    }
}
