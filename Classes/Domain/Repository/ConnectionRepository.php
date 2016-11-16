<?php
namespace In2code\In2connector\Domain\Repository;

/*
 * Copyright notice
 *
 * (c) 2015-2016 Oliver Eglseder <oliver.eglseder@in2code.de>, in2code GmbH
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
