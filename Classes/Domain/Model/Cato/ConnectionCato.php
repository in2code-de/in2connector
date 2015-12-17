<?php
namespace In2code\In2connector\Domain\Model\Cato;

use In2code\In2connector\Domain\Model\AbstractConnection;

/**
 * Class ConnectionCato
 */
class ConnectionCato
{
    const MATCHING = 'matching';
    const ORPHANED_EXISTING = 'orphanedExisting';
    const ORPHANED_REQUIRED = 'orphanedRequired';

    /**
     * @dco\storage
     * @var AbstractConnection[string][]
     */
    protected $requiredConnections = [];

    /**
     * @dco\storage
     * @var AbstractConnection[string][]
     */
    protected $existingConnections = [];

    /**
     * @dco\buffer
     * @var AbstractConnection[][]
     */
    protected $sortedConnections = [];

    /**
     * @dco\buffer
     * @var AbstractConnection[]
     */
    protected $existingIdentifiers = [];

    /**
     * @dco\buffer
     * @var string[]
     */
    protected $requiredIdentifiers = [];

    /**
     * ConnectionDco constructor.
     *
     * @param AbstractConnection[] $requiredConnections
     * @param AbstractConnection[][] $existingConnections
     */
    public function __construct(array $requiredConnections, array $existingConnections)
    {
        $this->requiredConnections = $requiredConnections;
        $this->existingConnections = $existingConnections;
    }

    /**
     *
     */
    protected function compute()
    {
        if ([] === $this->sortedConnections) {
            foreach ($this->existingConnections as $connections) {
                foreach ($connections as $connection) {
                    $this->existingIdentifiers[$connection->getCombinedIdentity()] = $connection;
                }
            }
            foreach ($this->requiredConnections as $package => $identityKeys) {
                foreach (array_keys($identityKeys) as $identityKey) {
                    $this->requiredIdentifiers[] = AbstractConnection::combineIdentity($package, $identityKey);
                }
            }
            $existingIdentifiers = array_keys($this->existingIdentifiers);
            $matchingIdentifiers = array_intersect(
                $this->requiredIdentifiers,
                $existingIdentifiers
            );
            foreach ($matchingIdentifiers as $matchingIdentifier) {
                $this->sortedConnections[self::MATCHING][$matchingIdentifier] = $this->existingIdentifiers[$matchingIdentifier];
            }
            foreach (array_diff($existingIdentifiers, $matchingIdentifiers) as $existingOrphanedIdentifier) {
                $this->sortedConnections[self::ORPHANED_EXISTING][$existingOrphanedIdentifier] = $this->existingConnections[$existingOrphanedIdentifier];
            }
            foreach (array_diff($this->requiredIdentifiers, $matchingIdentifiers) as $requiredOrphanedIdentifier) {
                $this->sortedConnections[self::ORPHANED_REQUIRED][$requiredOrphanedIdentifier] = $this->existingConnections[$requiredOrphanedIdentifier];
            }
        }
    }

    /**
     * @return AbstractConnection[]
     */
    public function getOrphanedExistingConnections()
    {
        $this->compute();
        return $this->sortedConnections[self::ORPHANED_EXISTING];
    }

    /**
     * @return AbstractConnection[]
     */
    public function getOrphanedRequiredConnections()
    {
        $this->compute();
        return $this->sortedConnections[self::ORPHANED_REQUIRED];
    }

    /**
     * @return AbstractConnection[]
     */
    public function getMatchingConnections()
    {
        $this->compute();
        return $this->sortedConnections[self::MATCHING];
    }
}
