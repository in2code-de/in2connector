<?php
namespace In2code\In2connector\Registry;

use In2code\In2connector\Domain\Model\AbstractConnection;
use In2code\In2connector\Registry\Exceptions\ConnectionTypeNotSupportedException;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class ConnectionRegistry
 */
class ConnectionRegistry implements SingletonInterface
{
    /**
     * @var AbstractConnection[]
     */
    protected $requiredConnections = [];

    /**
     * @param string $package Must be the extension key of the requiring extension.
     * @param string $identifier Can be anything but must be unique within the requiring extension.
     * @param string $type The FQCN of the required Connection type. Available Connections are LDAP and SOAP.
     * @throws ConnectionTypeNotSupportedException If the required connection is not supported
     */
    public function requireConnection($package, $identifier, $type)
    {
        if (is_subclass_of($type, AbstractConnection::class)) {
            $this->requiredConnections[$package][$identifier] = $type;
        } else {
            throw new ConnectionTypeNotSupportedException(
                'The connection class ' . htmlspecialchars($type) . ' is not supported',
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
}
