<?php
namespace In2code\In2connector\Controller;

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
