<?php
namespace In2code\In2connector\Controller;

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
