<?php
namespace In2code\In2connector\Domain\Repository;

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
use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * Class ConnectionRepository
 */
class ConnectionRepository
{
    const TABLE = 'tx_in2connector_domain_model_connection';

    /**
     * @return Connection[]
     */
    public function findAll()
    {
        $connections = array();
        $properties = (array)$this->getDatabase()->exec_SELECTgetRows(
            'uid,identity_key,driver,settings',
            static::TABLE,
            '1=1'
        );
        foreach ($properties as $propertyArray) {
            $connections[] = Connection::fromArray($propertyArray);
        }
        return $connections;
    }

    /**
     * @param Connection $connection
     */
    public function addAndPersist(Connection $connection)
    {
        $properties = $connection->toArray();
        $this->getDatabase()->exec_INSERTquery(static::TABLE, $properties);
        $connection->setUid($this->getDatabase()->sql_insert_id());
    }

    /**
     * @param Connection $connection
     */
    public function updateAndPersist(Connection $connection)
    {
        $uid = $this->checkUid($connection);
        $this->getDatabase()->exec_UPDATEquery(static::TABLE, 'uid=' . $uid, $connection->toArray());
    }

    /**
     * @param Connection $connection
     * @return bool
     */
    public function removeAndPersist(Connection $connection)
    {
        $uid = $this->checkUid($connection);
        return (bool)$this->getDatabase()->exec_DELETEquery(static::TABLE, 'uid=' . $uid);
    }

    /**
     * @param string $identityKey
     * @return int
     */
    public function countByIdentityKey($identityKey)
    {
        return (int)$this->getDatabase()->exec_SELECTcountRows('uid', static::TABLE, 'identity_key=' . $this->getDatabase()->fullQuoteStr($identityKey, static::TABLE));
    }

    /**
     * @param string $identityKey
     * @return Connection
     */
    public function findOneByIdentityKey($identityKey)
    {
        $properties = (array)$this->getDatabase()->exec_SELECTgetSingleRow(
            'uid,identity_key,driver,settings',
            static::TABLE,
            'identity_key=' . $this->getDatabase()->fullQuoteStr($identityKey, static::TABLE)
        );
        return Connection::fromArray($properties);
    }

    /**
     * @param string|int $uid
     * @return Connection
     */
    public function findOneByUid($uid)
    {
        $properties = (array)$this->getDatabase()->exec_SELECTgetSingleRow(
            'uid,identity_key,driver,settings',
            static::TABLE,
            'uid=' . (int)$uid
        );
        return Connection::fromArray($properties);
    }

    /**
     * @param Connection $connection
     * @return int
     */
    protected function checkUid(Connection $connection)
    {
        if (!(0 < $uid = (int)$connection->getUid())) {
            throw new \InvalidArgumentException(
                'Can not update unpersisted connection ' . $connection->getIdentityKey(),
                $uid = (int)$connection->getUid()
            );
        }
        return $uid;
    }

    /**
     * @return DatabaseConnection
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getDatabase()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
