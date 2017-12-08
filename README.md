# in2connector - Manage connections

in2connector is a TYPO3 extension to simplify your everyday life with any kind of connection.
This extension provides two kinds of drivers to ease access to a resource. These are LDAP and SOAP.
These drivers wrap around your connection to A/D / LDAP / SAP and other kinds of API provider.

To begin with in2connector you simply require a connection with an identifier and the type of connection:

ext_localconf.php:
```
$connectionRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
    \In2code\In2connector\Registry\ConnectionRegistry::class
);
$connectionRegistry->demandConnection('myExtension|connectionPurpose', TX_IN2CONNECTOR_DRIVER_LDAP);
```

Hint: You can use any string for the connection identifier.

The go to your backend into the in2connector module and click an "add connection" to configure your connection.
Save your changes and close the settings form to go back to the connections overview.
Now you will see a little icon indicating the status of your connection and if it is erroneous it will also display an error message.

You can now use the connection's driver to search, modify, add and delete entries.

PersonRepository.php:

```
use In2code\In2connector\Driver\LdapDriver;
use In2code\In2connector\Service\ConnectionService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PersonRepository
{
    /**
     * @var LdapDriver
     */
    protected $driver = null;

    /**
     * PersonRepository constructor.
     */
    public function __construct()
    {
        $this->driver = GeneralUtility::makeInstance(ConnectionService::class)->getDriverInstanceIfAvailable('asd');
    }

    public function findAll()
    {
        return $this->driver->searchAndGetResults('', 'objectClass=*');
    }
}
```

You can, of course, register your own drivers (Rest-API with JSON or XML for example).
Have a look at the registration of the shipped drivers to get the idea.
