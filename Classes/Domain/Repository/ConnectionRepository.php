<?php
namespace In2code\In2connector\Domain\Repository;

/*
 * Copyright notice
 *
 * (c) 2015 Oliver Eglseder <oliver.eglseder@in2code.de>, in2code GmbH
 *
 * All rights reserved
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

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

    /**
     * @param Connection $connection
     */
    public function removeAndPersist(Connection $connection)
    {
        parent::remove($connection);
        $this->persistenceManager->persistAll();
    }
}
