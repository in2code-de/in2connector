<?php
namespace In2code\In2connector\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class LogEntryRepository
 */
class LogRepository extends Repository
{
    /**
     * @var \In2code\In2connector\Service\ConfigurationService
     * @inject
     */
    protected $configurationService = null;

    /**
     * Newest log entries first!
     *
     * @var array
     */
    protected $defaultOrderings = [
        'uid' => QueryInterface::ORDER_DESCENDING,
    ];

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findWithLimit()
    {
        return $this->createQuery()->setLimit($this->configurationService->getLogsPerPage())->execute();
    }
}
