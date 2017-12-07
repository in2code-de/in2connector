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
     * @var LdapQueryParser
     */
    protected $ldapQueryParser = [];

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
        $this->ldapQueryParser = GeneralUtility::makeInstance(LdapQueryParser::class);
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

    /**
     * @param string $tableName
     * @param array $fieldValues
     * @param bool $isRelation
     * @return bool
     * @throws InvalidDriverException
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function updateRow($tableName, array $fieldValues, $isRelation = false)
    {
        $config = $this->getConfig($tableName);
        $driver = $this->getDriver($tableName);
        $mapping = array_flip($config['ldap_mapping']['columns']);

        $uid = (int)$fieldValues['uid'];
        unset($fieldValues['uid']);

        $row = [];
        foreach ($fieldValues as $name => $value) {
            $row[isset($mapping[$name]) ? $mapping[$name] : $name] = $value;
        }

        $result = $driver->search('', '(' . $config['ldap_mapping']['uid'] . '=' . $uid . ')');
        $entry = $driver->fetchFirst($result);
        $distinguishedName = $driver->getDnOfEntry($entry);

        return $driver->modify($distinguishedName, $row);
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

    /**
     * @param QueryInterface $query
     * @return bool|int
     * @throws InvalidDriverException
     * @throws NotImplementedException
     */
    public function getObjectCountByQuery(QueryInterface $query)
    {
        $config = $this->getConfigForQuery($query);
        $driver = $this->getDriver($config['mapping']['tableName']);
        $filter = $this->ldapQueryParser->parseQuery($query, $config);
        return $driver->searchAndCountResults('', $filter);
    }

    /**
     * @param QueryInterface $query
     * @return array
     * @throws NotImplementedException
     * @throws InvalidDriverException
     */
    public function getObjectDataByQuery(QueryInterface $query)
    {
        $config = $this->getConfigForQuery($query);
        $filter = $this->ldapQueryParser->parseQuery($query, $config);
        $driver = $this->getDriver($config['mapping']['tableName']);

        $results = $driver->listAndGetResults(
            '',
            $filter,
            array_merge([$config['ldap_mapping']['uid']], array_keys($config['ldap_mapping']['columns']))
        );
        unset($results['count']);

        $rows = [];

        foreach ($results as $result) {
            $row = [
                'uid' => (int)$result[$config['ldap_mapping']['uid']][0],
                'pid' => 0,
            ];
            foreach ($config['ldap_mapping']['columns'] as $ldapKey => $localKey) {
                if (isset($result[$ldapKey][0])) {
                    $row[$localKey] = $result[$ldapKey][0];
                } else {
                    $row[$localKey] = null;
                }
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
     * @param string $identityKey
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

    /**
     * @param string $tableName
     * @return array
     * @throws \Exception
     */
    protected function getConfig($tableName)
    {
        foreach ($this->config as $config) {
            if (isset($config['mapping']['tableName']) && $config['mapping']['tableName'] === $tableName) {
                return $config;
            }
        }
        throw new \Exception('Could not identify config for table name');
    }

    /**
     * @param QueryInterface $query
     * @return mixed
     */
    protected function getConfigForQuery(QueryInterface $query)
    {
        $class = $query->getType();
        if (!isset($this->config[$class]['ldap_mapping'])) {
            throw new \InvalidArgumentException('Class ' . $class . ' is not configured');
        }
        return $this->config[$class];
    }
}
