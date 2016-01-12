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
 */

use In2code\In2connector\Domain\Model\Cato\ConnectionLinker;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class DashboardController
 */
class DashboardController extends ActionController
{
    const CONTROLLER_NAME = 'Dashboard';
    const ACTION_INDEX = 'index';

    /**
     * @var \In2code\In2connector\Domain\Repository\LogRepository
     * @inject
     */
    protected $logRepository = null;

    /**
     * @var \In2code\In2connector\Domain\Repository\ConnectionRepository
     * @inject
     */
    protected $connectionRepository = null;

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
     *
     */
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
     * @return string
     */
    public static function getModuleActions()
    {
        return implode(',', [self::ACTION_INDEX]);
    }
}
