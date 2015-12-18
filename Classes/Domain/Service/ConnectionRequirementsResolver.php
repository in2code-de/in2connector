<?php
namespace In2code\In2connector\Domain\Service;

use In2code\In2connector\Domain\Model\AbstractConnection;

/**
 * Class ConnectionRequirementsResolver
 */
class ConnectionRequirementsResolver
{
    /**
     * @var \In2code\In2connector\Registry\ConnectionRegistry
     * @inject
     */
    protected $connectionRegistry = null;

    /**
     * @var \In2code\In2connector\Domain\Factory\RepositoryFactory
     * @inject
     */
    protected $repositoryFactory = null;

    /**
     * @return \In2code\In2connector\Domain\Model\AbstractConnection[]
     */
    public function getConnectionsForRequirements()
    {
        /** @var AbstractConnection[] $connections */
        $connections = [];
        foreach ($this->connectionRegistry->getRequiredConnections() as $package => $requiredConnections) {
            foreach ($requiredConnections as $identityKey => $requiredConnection) {
                $repository = $this->repositoryFactory->getRepositoryForClassName($requiredConnection);
                $combinedIdentity = AbstractConnection::combineIdentity($package, $identityKey);
                $connections[$combinedIdentity] = $repository->findOneByPackageAndIdentityKey($package, $identityKey);
            }
        }
        return $connections;
    }
}
