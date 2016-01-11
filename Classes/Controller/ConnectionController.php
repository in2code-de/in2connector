<?php
namespace In2code\In2connector\Controller;

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
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class ConnectionController
 */
class ConnectionController extends ActionController
{
    const CONTROLLER_NAME = 'Connection';
    const ACTION_NEW_FROM_DEMAND = 'newFromDemand';
    const ACTION_NEW = 'new';
    const ACTION_CREATE = 'create';
    const ACTION_CONFIGURE = 'configure';
    const ACTION_SET_CONFIG = 'setConfig';

    /**
     * @var \In2code\In2connector\Domain\Repository\ConnectionRepository
     * @inject
     */
    protected $connectionRepository = null;

    /**
     * @param string $identityKey
     * @param string $driverName
     */
    public function newFromDemandAction($identityKey, $driverName)
    {
        $connection = $this->objectManager->get(Connection::class);
        $connection->setDriver($driverName);
        $connection->setIdentityKey($identityKey);
        $this->connectionRepository->addAndPersist($connection);
        $this->redirect(self::ACTION_CONFIGURE, null, null, ['connection' => $connection]);
    }

    /**
     *
     */
    public function newAction()
    {
    }

    /**
     * @param Connection $connection
     */
    public function createAction(Connection $connection)
    {
        $this->connectionRepository->addAndPersist($connection);
        $this->redirect(self::ACTION_CONFIGURE, null, null, ['connection' => $connection]);
    }

    /**
     * @param Connection $connection
     */
    public function configureAction(Connection $connection)
    {
        $this->view->assign('connection', $connection);
    }

    /**
     * @param Connection $connection
     */
    public function setConfigAction(Connection $connection)
    {
        $this->connectionRepository->updateAndPersist($connection);
        $this->redirect(self::ACTION_CONFIGURE, null, null, ['connection' => $connection]);
    }

    /**
     * @return string
     */
    public static function getModuleActions()
    {
        return implode(',',
            [
                self::ACTION_NEW_FROM_DEMAND,
                self::ACTION_NEW,
                self::ACTION_CREATE,
                self::ACTION_CONFIGURE,
                self::ACTION_SET_CONFIG,
            ]
        );
    }
}
