<?php
namespace In2code\In2connector\Controller;

use In2code\In2connector\Domain\Model\LdapConnection;

/**
 * Class ConnectionController
 */
class LdapConnectionController extends AbstractConnectionController
{
    const ACTION_CREATE = 'create';
    const ACTION_EDIT = 'edit';
    const ACTION_UPDATE = 'update';

    /**
     * @var \In2code\In2connector\Domain\Repository\LdapConnectionRepository
     * @inject
     */
    protected $ldapConnectionRepository = null;

    /**
     * @param string $package
     * @param string $identityKey
     * @return void
     */
    public function createAction($package, $identityKey)
    {
        $ldapConnection = new LdapConnection();
        $ldapConnection->setPackage($package);
        $ldapConnection->setIdentityKey($identityKey);
        $this->view->assign('ldapConnection', $ldapConnection);
    }

    /**
     * @param LdapConnection $connection
     */
    public function editAction(LdapConnection $connection)
    {
        $this->view->assign('connection', $connection);
    }

    /**
     * @param LdapConnection $connection
     * @return void
     */
    public function updateAction(LdapConnection $connection)
    {
        $this->ldapConnectionRepository->update($connection);
        $this->redirect(self::ACTION_EDIT, null, null, ['connection' => $connection]);
    }

    /**
     * @return string
     */
    public static function getModuleActions()
    {
        return self::ACTION_EDIT . ',' . self::ACTION_UPDATE . ',' . self::ACTION_CREATE;
    }
}
