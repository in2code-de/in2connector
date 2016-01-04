<?php
namespace In2code\In2connector\Controller;

use In2code\In2connector\Logging\LoggerTrait;
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
     * @var \In2code\In2connector\Domain\Repository\LogEntryRepository
     * @inject
     */
    protected $logEntryRepository = null;

    use LoggerTrait;

    public function __construct()
    {
        parent::__construct();
        $this->getLogger()->debug('Instantiated Controller ' . get_class($this));
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->view->assign('connections', $this->connectionRequirementsResolver->getConnectionsForRequirements());
        $this->view->assign('orphanedConnections', $this->connectionRequirementsResolver->getOrphanedConnections());
        $this->view->assign('logEntries', $this->logEntryRepository->findLatest(20));
    }
}
