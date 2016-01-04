<?php
namespace In2code\In2connector\Domain\Repository;

use In2code\In2connector\Domain\Model\Configuration;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class LogEntryRepository
 */
class LogEntryRepository extends Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'uid' => QueryInterface::ORDER_DESCENDING,
    ];

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findLatest()
    {
        $configuration = new Configuration();
        $query = $this->createQuery();
        return $query->setLimit($configuration->getLogEntriesPerPage())->execute();
    }
}
