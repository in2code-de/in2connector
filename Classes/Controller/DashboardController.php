<?php
namespace In2code\In2connector\Controller;

use In2code\In2connector\Domain\Model\Configuration;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class DashboardController
 */
class DashboardController extends ActionController
{
    const ACTION_INDEX = 'index';
    const ACTION_EDIT_CONFIGURATION = 'editConfiguration';
    const ACTION_UPDATE_CONFIGURATION = 'updateConfiguration';

    /**
     * @var \In2code\In2connector\Domain\Service\ConnectionRequirementsResolver
     * @inject
     */
    protected $connectionRequirementsResolver = null;

    /**
     * @var \In2code\In2connector\Domain\Repository\LogEntryRepository
     * @inject
     */
    protected $logEntryRepository = null;

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->view->assign(
            'connections',
            $this->connectionRequirementsResolver->getConnectionsForRequirements()
        );
        $this->view->assign(
            'orphanedConnections',
            $this->connectionRequirementsResolver->getOrphanedConnections()
        );
        $this->view->assign(
            'logEntries',
            $this->logEntryRepository->findLatest()
        );
    }

    /**
     *
     */
    public function editConfigurationAction()
    {
        $this->view->assign(
            'configuration',
            new Configuration()
        );
    }

    /**
     * @param Configuration $configuration
     */
    public function updateConfigurationAction(Configuration $configuration)
    {
        $configuration->persist();
        $this->redirect(self::ACTION_EDIT_CONFIGURATION);
    }

    /**
     * @return string
     */
    public static function getModuleActions()
    {
        return self::ACTION_INDEX . ',' . self::ACTION_EDIT_CONFIGURATION . ',' . self::ACTION_UPDATE_CONFIGURATION;
    }
}
