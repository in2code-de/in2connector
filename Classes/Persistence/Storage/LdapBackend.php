<?php
namespace In2code\In2connector\Persistence\Storage;

use In2code\In2connector\Driver\LdapDriver;
use In2code\In2connector\Registry\Exceptions\InvalidDriverException;
use In2code\In2connector\Service\ConnectionService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\BackendInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class LdapBackend implements BackendInterface
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * LdapBackend constructor.
     *
     * @throws InvalidConfigurationTypeException
     */
    public function __construct()
    {
        $config = GeneralUtility::makeInstance(ObjectManager::class)
                                ->get(ConfigurationManager::class)
                                ->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK);
        $this->config = $config['persistence']['classes'];
    }

    public function addRow($tableName, array $fieldValues, $isRelation = false)
    {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
            func_get_args(),
            __FILE__ . '@' . __LINE__,
            20,
            false,
            true,
            false,
            array()
        );
        die;
    }

    public function updateRow($tableName, array $fieldValues, $isRelation = false)
    {
        $this->getDriver($tableName);
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
            func_get_args(),
            __FILE__ . '@' . __LINE__,
            20,
            false,
            true,
            false,
            array()
        );
        die;
    }

    public function updateRelationTableRow($tableName, array $fieldValues)
    {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
            func_get_args(),
            __FILE__ . '@' . __LINE__,
            20,
            false,
            true,
            false,
            array()
        );
        die;
    }

    public function removeRow($tableName, array $where, $isRelation = false)
    {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
            func_get_args(),
            __FILE__ . '@' . __LINE__,
            20,
            false,
            true,
            false,
            array()
        );
        die;
    }

    public function getMaxValueFromTable($tableName, array $where, $columnName)
    {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
            func_get_args(),
            __FILE__ . '@' . __LINE__,
            20,
            false,
            true,
            false,
            array()
        );
        die;
    }

    public function getObjectCountByQuery(QueryInterface $query)
    {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
            func_get_args(),
            __FILE__ . '@' . __LINE__,
            20,
            false,
            true,
            false,
            array()
        );
        die;
    }

    /**
     * @param QueryInterface $query
     * @return array
     * @throws NotImplementedException
     * @throws InvalidDriverException
     */
    public function getObjectDataByQuery(QueryInterface $query)
    {
        $class = $query->getType();

        if (!isset($this->config[$class]['ldap_mapping'])) {
            throw new \InvalidArgumentException('Class ' . $class . ' is no configured');
        }
        $config = $this->config[$class];

        if (null !== $constraint = $query->getConstraint()) {
            throw new NotImplementedException('Constraints in LDAP Backend');
        } else {
            $filter = $config['ldap_mapping']['id'] . '=*';
        }

        $driver = $this->getDriver($config['mapping']['tableName']);

        $results = $driver->searchAndGetResults('', $filter);
        unset($results['count']);

        $rows = [];

        foreach ($results as $result) {
            $row = [
                'uid' => (int)$result[$config['ldap_mapping']['uid']][0],
                'pid' => 0,
            ];
            foreach ($config['ldap_mapping']['columns'] as $ldapKey => $localKey) {
                $row[$localKey] = $result[$ldapKey][0];
            }
            $rows[] = $row;
        }

        return $rows;
    }

    public function getUidOfAlreadyPersistedValueObject(\TYPO3\CMS\Extbase\DomainObject\AbstractValueObject $object)
    {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
            func_get_args(),
            __FILE__ . '@' . __LINE__,
            20,
            false,
            true,
            false,
            array()
        );
        die;
    }

    /**
     * @param string $class
     * @return LdapDriver
     * @throws InvalidDriverException
     */
    protected function getDriver($identityKey)
    {
        $driver = GeneralUtility::makeInstance(ConnectionService::class)->getDriverInstanceIfAvailable($identityKey);
        if (!($driver instanceof LdapDriver)) {
            throw new InvalidDriverException('The driver for ' . $identityKey . ' does not exist or work');
        }
        return $driver;
    }
}
