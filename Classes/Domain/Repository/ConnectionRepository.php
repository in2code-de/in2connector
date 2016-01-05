<?php
namespace In2code\In2connector\Domain\Repository;

use In2code\In2connector\Domain\Model\Connection;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class ConnectionRepository
 */
class ConnectionRepository extends Repository
{
    /**
     * @param Connection $connection
     */
    public function addAndPersist(Connection $connection)
    {
        parent::add($connection);
        $this->persistenceManager->persistAll();
    }

    /**
     * @param Connection $connection
     */
    public function updateAndPersist(Connection $connection)
    {
        parent::update($connection);
        $this->persistenceManager->persistAll();
    }
}
