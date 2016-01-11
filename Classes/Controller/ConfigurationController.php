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

use In2code\In2connector\Domain\Model\Dto\Configuration;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class ConfigurationController
 */
class ConfigurationController extends ActionController
{
    const CONTROLLER_NAME = 'Configuration';
    const ACTION_EDIT = 'edit';
    const ACTION_UPDATE = 'update';

    /**
     * @var \In2code\In2connector\Service\ConfigurationService
     * @inject
     */
    protected $configurationService = null;

    /**
     *
     */
    public function editAction()
    {
        $this->view->assign('configuration', $this->configurationService->getConfigurationDto());
    }

    /**
     * @param Configuration $configuration
     */
    public function updateAction(Configuration $configuration)
    {
        $this->configurationService->updateFromConfigurationDto($configuration);
        $this->redirect(self::ACTION_EDIT);
    }

    /**
     * @return string
     */
    public static function getModuleActions()
    {
        return implode(',', [self::ACTION_EDIT, self::ACTION_UPDATE]);
    }
}
