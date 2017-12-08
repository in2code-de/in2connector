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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use VerteXVaaR\Logs\Domain\Model\Filter;
use VerteXVaaR\Logs\Log\Reader\DatabaseReader;

/**
 * Class ConnectionController
 */
class ConnectionController extends ActionController
{
    use TranslationTrait;
    const ACTION_CONFIGURE = 'configure';
    const ACTION_INDEX = 'index';

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

    /**
     * @var \VerteXVaaR\Logs\Log\Reader\DatabaseReader
     */
    protected $logReader = null;

    /**
     * ConnectionController constructor.
     */
    public function initializeObject()
    {
        $this->logReader = $this->objectManager->get(DatabaseReader::class, ['logTable' => 'tx_in2connector_log']);
    }

    /**
     *
     */
    public function indexAction()
    {
        $this->view->assign(
            'connectionLinker',
            new ConnectionLinker(
                $this->connectionRepository->findAll(),
                $this->connectionRegistry->getDemandedConnections()
            )
        );
        $filter = new Filter(false);
        $filter->setLimit(250);
        $filter->setFullMessage(true);
        $filter->setLevel(7);
        $filter->setShowData(true);
        $this->view->assign('logs', $this->logReader->findByFilter($filter));
    }

    /**
     * @param string $identityKey
     * @param string $driverName
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function newFromDemandAction($identityKey, $driverName)
    {
        $connection = GeneralUtility::makeInstance(Connection::class);
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
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function createAction(Connection $connection)
    {
        $this->connectionRepository->addAndPersist($connection);
        $this->redirect(self::ACTION_CONFIGURE, null, null, ['connection' => $connection]);
    }

    /**
     * @param int $connection
     */
    public function configureAction($connection)
    {
        $connection = $this->connectionRepository->findOneByUid($connection);
        $connectionRegistry = GeneralUtility::makeInstance(ConnectionRegistry::class);
        $isDemanded = $connectionRegistry->hasDemandedConnection($connection->getIdentityKey());
        $this->view->assign('isDemanded', $isDemanded);
        $this->view->assign('connection', $connection);
    }

    /**
     * @param array $connection
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function setConfigAction(array $connection)
    {
        $connection = Connection::fromArray($connection);
        $this->connectionRepository->updateAndPersist($connection);
        $this->redirect(self::ACTION_CONFIGURE, null, null, ['connection' => $connection]);
    }

    /**
     * @param Connection $connection
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function deleteAction(Connection $connection)
    {
        $connectionRegistry = GeneralUtility::makeInstance(ConnectionRegistry::class);
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
