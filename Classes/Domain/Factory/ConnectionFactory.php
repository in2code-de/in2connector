<?php
namespace In2code\In2connector\Domain\Factory;

use In2code\In2connector\Domain\Model\AbstractConnection;
use In2code\In2connector\Exceptions\ConnectionTypeNotSupportedException;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;

/**
 * Class RepositoryFactory
 */
class ConnectionFactory extends AbstractFactory
{
    /**
     * Creates a new connection and sets the state to orphaned requirement
     *
     * @param string $className
     * @param string $package
     * @param string $identityKey
     * @return AbstractConnection
     * @throws ConnectionTypeNotSupportedException
     */
    public function createConnectionByRepositoryClassName($className, $package, $identityKey)
    {
        return $this->createConnectionByClassName(
            ClassNamingUtility::translateRepositoryNameToModelName($className),
            $package,
            $identityKey
        );
    }

    /**
     * @param string $className
     * @param string $package
     * @param string $identityKey
     * @return AbstractConnection
     * @throws ConnectionTypeNotSupportedException
     */
    public function createConnectionByClassName($className, $package, $identityKey)
    {
        if (!is_subclass_of($className, AbstractConnection::class)) {
            throw new ConnectionTypeNotSupportedException(
                'The connection class ' . htmlspecialchars($className) . ' is not supported',
                1450452219
            );
        }
        /** @var AbstractConnection $connection */
        $connection = $this->objectManager->get($className);
        $connection->setRequiredStatus(AbstractConnection::REQUIRED_STATUS_REQUIRED);
        $connection->setPackage($package);
        $connection->setIdentityKey($identityKey);
        return $connection;
    }
}
