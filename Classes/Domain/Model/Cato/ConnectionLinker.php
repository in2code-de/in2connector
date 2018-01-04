<?php
namespace In2code\In2connector\Domain\Model\Cato;

/***************************************************************
 * Copyright notice
 *
 * (c) 2015 in2code.de and the following authors:
 * Oliver Eglseder <oliver.eglseder@in2code.de>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use In2code\In2connector\Domain\Model\Connection;
use In2code\In2connector\Domain\Model\Dto\ConnectionDemand;

/**
 * Class ConnectionLinker
 */
class ConnectionLinker
{
    /**
     * @var bool
     */
    protected $computed = false;

    /**
     * @cato\storage
     * @var Connection[]
     */
    protected $configuredConnections = [];

    /**
     * @cato\storage
     * @var ConnectionDemand[]
     */
    protected $demandedConnections = [];

    /**
     * @cato\buffer
     * @var Connection[]
     */
    protected $orphanedConnections = [];

    /**
     * @cato\buffer
     * @var Connection[]
     */
    protected $matchingConnections = [];

    /**
     * @cato\buffer
     * @var ConnectionDemand[]
     */
    protected $unconfiguredConnections = [];

    /**
     * ConnectionLinker constructor.
     *
     * @param \In2code\In2connector\Domain\Model\Connection[] $configuredConnections
     * @param \In2code\In2connector\Domain\Model\Dto\ConnectionDemand[] $demandedConnections
     */
    public function __construct(array $configuredConnections, array $demandedConnections)
    {
        $this->configuredConnections = $configuredConnections;
        $this->demandedConnections = $demandedConnections;
    }

    /**
     *
     */
    protected function compute()
    {
        if (false === $this->computed) {
            $configured = $this->configuredConnections;
            $demanded = $this->demandedConnections;
            foreach ($configured as $configuredKey => $configuredConnection) {
                foreach ($this->demandedConnections as $demandedKey => $connectionDemand) {
                    if ($configuredConnection->getIdentityKey() === $connectionDemand->getIdentityKey()) {
                        $this->matchingConnections[] = $configuredConnection;
                        unset($demanded[$demandedKey]);
                        unset($configured[$configuredKey]);
                        break;
                    }
                }
            }
            $this->orphanedConnections = $configured;
            $this->unconfiguredConnections = $demanded;
            $this->computed = true;
        }
    }

    /**
     * @return \In2code\In2connector\Domain\Model\Connection[]
     */
    public function getOrphanedConnections()
    {
        $this->compute();
        return $this->orphanedConnections;
    }

    /**
     * @return \In2code\In2connector\Domain\Model\Connection[]
     */
    public function getMatchingConnections()
    {
        $this->compute();
        return $this->matchingConnections;
    }

    /**
     * @return \In2code\In2connector\Domain\Model\Dto\ConnectionDemand[]
     */
    public function getUnconfiguredConnections()
    {
        $this->compute();
        return $this->unconfiguredConnections;
    }
}
