<?php
namespace In2code\In2connector\Controller;

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
     *
     */
    public function indexAction()
    {
        $this->view->assign('logs', $this->logRepository->findWithLimit());
        $this->view->assign(
            'connectionLinker',
            new ConnectionLinker(
                $this->connectionRepository->findAll()->toArray(),
                $this->connectionRegistry->getDemandedConnections()
            )
        );
    }

    /**
     * @return string
     */
    public static function getModuleActions()
    {
        return implode(',', [self::ACTION_INDEX]);
    }
}
