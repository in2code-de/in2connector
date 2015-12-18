<?php
namespace In2code\In2connector\Domain\Repository;

use In2code\In2connector\Domain\Factory\ConnectionFactory;
use In2code\In2connector\Domain\Model\AbstractConnection;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class AbstractConnectionRepository
 */
abstract class AbstractConnectionRepository extends Repository
{
    /**
     * @param string $package
     * @param string $identityKey
     * @return AbstractConnection
     */
    public function findOneByPackageAndIdentityKey($package, $identityKey)
    {
        $query = $this->createQuery();

        /** @var AbstractConnection $connection */
        $connection = $query->matching(
            $query->logicalAnd([$query->equals('package', $package), $query->equals('identityKey', $identityKey)])
        )->setLimit(1)->execute()->getFirst();

        if ($connection === null) {
            $connection = $this->getConnectionFactory()->createConnectionByRepositoryClassName(
                get_class($this),
                $package,
                $identityKey
            );
        } else {
            $connection->setStatus(AbstractConnection::STATUS_REQUIREMENT_MATCH_EXISTING);
        }
        return $connection;
    }

    /**
     * @return ConnectionFactory
     */
    protected function getConnectionFactory()
    {
        return $this->objectManager->get(ConnectionFactory::class);
    }
}
