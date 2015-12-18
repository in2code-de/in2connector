<?php
namespace In2code\In2connector\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class DashboardController
 */
class DashboardController extends ActionController
{
    /**
     * @var \In2code\In2connector\Domain\Service\ConnectionRequirementsResolver
     * @inject
     */
    protected $connectionRequirementsResolver = null;

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->view->assign('connections', $this->connectionRequirementsResolver->getConnectionsForRequirements());
        $this->view->assign('orphanedConnections', $this->connectionRequirementsResolver->getOrphanedConnections());
    }
}
