<?php
namespace In2code\In2connector\Controller;

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

use In2code\In2connector\Domain\Model\Cato\ConnectionLinker;
use In2code\In2connector\Domain\Model\Connection;
use In2code\In2connector\Registry\ConnectionRegistry;
use In2code\In2connector\Translation\TranslationTrait;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class ConnectionController
 */
class ConnectionController extends ActionController
{
    use TranslationTrait;
    const ACTION_CONFIGURE = 'configure';
    const ACTION_INDEX = 'index';

    /**
     * @var \In2code\In2connector\Domain\Repository\LogRepository
     * @inject
     */
    protected $logRepository = null;

    /**
     * @var \In2code\In2connector\Registry\ConnectionRegistry
     * @inject
     */
    protected $connectionRegistry = null;

    /**
     * @var \In2code\In2connector\Service\ConfigurationService
     * @inject
     */
    protected $configurationService = null;

    /**
     * @var \In2code\In2connector\Domain\Repository\ConnectionRepository
     * @inject
     */
    protected $connectionRepository = null;

    public function indexAction()
    {
        $this->view->assign('logs', $this->logRepository->findAll());
        $this->view->assign(
            'connectionLinker',
            new ConnectionLinker(
                $this->connectionRepository->findAll()->toArray(),
                $this->connectionRegistry->getDemandedConnections()
            )
        );
        $this->view->assign('logsPerPage', $this->configurationService->getLogsPerPage());

    }

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
        $connectionRegistry = $this->objectManager->get(ConnectionRegistry::class);
        $isDemanded = $connectionRegistry->hasDemandedConnection($connection->getIdentityKey());
        $this->view->assign('isDemanded', $isDemanded);
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
     * @param Connection $connection
     */
    public function deleteAction(Connection $connection)
    {
        $connectionRegistry = $this->objectManager->get(ConnectionRegistry::class);
        if ($connectionRegistry->hasDemandedConnection($connection->getIdentityKey())) {
            $this->addFlashMessage(
                $this->translate(
                    'controller.connection.delete.connection_is_demanded',
                    [$connection->getIdentityKey()]
                ),
                $this->translate('controller.connection.delete.delete_failed'),
                AbstractMessage::ERROR
            );
        } else {
            $this->connectionRepository->removeAndPersist($connection);
        }
        $this->redirect(self::ACTION_INDEX);
    }
}
