<?php
namespace In2code\In2connector\Registry;

use In2code\In2connector\Domain\Factory\RepositoryFactory;
use In2code\In2connector\Domain\Model\AbstractConnection;
use In2code\In2connector\Domain\Repository\AbstractConnectionRepository;
use In2code\In2connector\Exceptions\ConnectionTypeNotSupportedException;
use In2code\In2connector\Logging\LoggerTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class ConnectionRegistry
 */
class ConnectionRegistry implements SingletonInterface
{
    use LoggerTrait;

    /**
     * @var AbstractConnection[][]
     */
    protected $requiredConnections = [];

    /**
     * @param string $package Must be the extension key of the requiring extension.
     * @param string $identityKey Can be anything but must be unique within the requiring extension.
     * @param string $type The FQCN of the required Connection type. Available Connections are LDAP and SOAP.
     * @throws ConnectionTypeNotSupportedException If the required connection is not supported
     */
    public function requireConnection($package, $identityKey, $type)
    {
        $this->getLogger()->debug(
            'Requiring connection',
            [
                'package' => $package,
                'identityKey' => $identityKey,
                'type' => $type,
            ]
        );
        if (is_subclass_of(
            $type,
            AbstractConnection::class
        )) {
            $this->requiredConnections[$package][$identityKey] = $type;

            $this->getLogger()->debug(
                'Requiring connection successful',
                [
                    'package' => $package,
                    'identityKey' => $identityKey,
                    'type' => $type,
                ]
            );
        } else {
            $this->getLogger()->critical(
                'Requiring connection failed: "The connection class is not supported"',
                [
                    'package' => $package,
                    'identityKey' => $identityKey,
                    'type' => $type,
                ]
            );

            throw new ConnectionTypeNotSupportedException(
                'The connection class ' . $type . ' is not supported',
                1450371768
            );
        }
    }

    /**
     * @return AbstractConnection[]
     */
    public function getRequiredConnections()
    {
        return $this->requiredConnections;
    }

    /**
     * @param string $package
     * @param string $identityKey
     * @return AbstractConnection|null
     */
    public function getRequiredConnection($package, $identityKey)
    {
        $this->getLogger()->debug(
            'Finding requested connection',
            [
                'package' => $package,
                'identityKey' => $identityKey,
            ]
        );
        if (!empty($this->requiredConnections[$package][$identityKey])) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

            $repositoryFactory = $objectManager->get(RepositoryFactory::class);

            $repository = $repositoryFactory->getRepositoryForClassName(
                $this->requiredConnections[$package][$identityKey]
            );

            if ($repository instanceof AbstractConnectionRepository) {
                $connection = $repository->findOneByPackageAndIdentityKey(
                    $package,
                    $identityKey
                );

                if ($connection instanceof AbstractConnection) {
                    $this->getLogger()->info(
                        'Found requested connection',
                        [
                            'package' => $package,
                            'identityKey' => $identityKey,
                            'type' => $this->requiredConnections[$package][$identityKey],
                            'uid' => $connection->getUid(),
                        ]
                    );
                    return $connection;
                } else {
                    $this->getLogger()->error(
                        'The required connection does not exist',
                        [
                            'package' => $package,
                            'identityKey' => $identityKey,
                            'type' => $this->requiredConnections[$package][$identityKey],
                        ]
                    );
                }
            } else {
                $this->getLogger()->error(
                    'The repository for the required connection could not be found',
                    [
                        'package' => $package,
                        'identityKey' => $identityKey,
                        'type' => $this->requiredConnections[$package][$identityKey],
                    ]
                );
            }
        } else {
            $this->getLogger()->error(
                'The searched connection was not required, thus there is no type',
                [
                    'package' => $package,
                    'identityKey' => $identityKey,
                ]
            );
        }
        return null;
    }
}
