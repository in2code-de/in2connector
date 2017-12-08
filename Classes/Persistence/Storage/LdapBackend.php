<?php
namespace In2code\In2connector\Persistence\Storage;

use In2code\In2connector\Driver\LdapDriver;
use In2code\In2connector\Registry\Exceptions\InvalidDriverException;
use In2code\In2connector\Service\ConnectionService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\DomainObject\AbstractValueObject;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\BackendInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class LdapBackend
 */
class LdapBackend implements BackendInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

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
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $config = $this->objectManager->get(ConfigurationManager::class)
                                      ->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK);
        $this->config = $config['persistence']['classes'];
        $this->ldapQueryParser = $this->objectManager->get(LdapQueryParser::class);
    }

    /**
     * @param string $tableName
     * @param array $fieldValues
     * @param bool $isRelation
     * @return int
     * @throws InvalidDriverException
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addRow($tableName, array $fieldValues, $isRelation = false)
    {
        $config = $this->getConfigForTable($tableName);
        $driver = $this->getDriver($tableName);

        $typo3Columns = array_flip($config['ldap_mapping']['columns']);
        $row = $this->mapValues($fieldValues, $typo3Columns);

        $row['objectClass'] = $config['ldap_mapping']['objectClass'];
        $row = $this->setAutoValues($row, $driver);

        $rdnAttribute = $config['ldap_mapping']['rdnAttribute'];
        $driver->add(sprintf('%s=%s', $rdnAttribute, $row[$rdnAttribute]), $row);
        return $row[$typo3Columns['uid']];
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
        $config = $this->getConfigForTable($tableName);
        $driver = $this->getDriver($tableName);

        $typo3Columns = array_flip($config['ldap_mapping']['columns']);
        $row = $this->mapValues($fieldValues, $typo3Columns);

        $result = $driver->search('', '(' . $typo3Columns['uid'] . '=' . $fieldValues['uid'] . ')');
        $entry = $driver->fetchFirst($result);
        $distinguishedName = $driver->getDnOfEntry($entry);

        return $driver->modify($distinguishedName, $row);
    }

    /**
     * @param string $tableName
     * @param array $fieldValues
     * @return bool|void
     * @throws NotImplementedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function updateRelationTableRow($tableName, array $fieldValues)
    {
        throw new NotImplementedException('updateRelationTableRow is not yet supported');
    }

    /**
     * @param string $tableName
     * @param array $where
     * @param bool $isRelation
     * @return bool
     * @throws InvalidDriverException
     * @throws NotImplementedException
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function removeRow($tableName, array $where, $isRelation = false)
    {
        $config = $this->getConfigForTable($tableName);
        $driver = $this->getDriver($config['mapping']['tableName']);
        $query = $this->objectManager->get(Query::class, $config['class']);
        $constraints = [];
        foreach ($where as $field => $value) {
            $constraints[] = $query->equals($field, $value);
        }
        $query->matching($query->logicalAnd($constraints));
        $filter = $this->ldapQueryParser->parseQuery($query, $config);
        $result = $driver->search('', $filter, ['dn']);
        $count = $driver->countResults($result);
        $success = true;
        if ($count > 0) {
            $entries = $driver->getResults($result);
            unset($entries['count']);
            foreach ($entries as $entry) {
                $driver->delete($entry['dn']) ?: $success = false;
            }
        }
        return $success;
    }

    /**
     * @param string $tableName
     * @param array $where
     * @param string $columnName
     * @return mixed|void
     * @throws NotImplementedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getMaxValueFromTable($tableName, array $where, $columnName)
    {
        throw new NotImplementedException('getMaxValueFromTable is not used by TYPO3');
    }

    /**
     * @param QueryInterface $query
     * @return bool|int
     * @throws InvalidDriverException
     * @throws NotImplementedException
     */
    public function getObjectCountByQuery(QueryInterface $query)
    {
        $config = $this->getConfigForClass($query->getType());
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
        $config = $this->getConfigForClass($query->getType());
        $filter = $this->ldapQueryParser->parseQuery($query, $config);
        $driver = $this->getDriver($config['mapping']['tableName']);

        $ldapColumns = $config['ldap_mapping']['columns'];
        $typo3Columns = array_flip($ldapColumns);
        $extbaseColumns = [];
        foreach ($config['mapping']['columns'] as $colName => $colConf) {
            if (!isset($typo3Columns[$colName])) {
                $extbaseColumns[$colName] = $colConf;
            }
        }

        $results = $driver->listAndGetResults('', $filter, array_keys($ldapColumns));
        unset($results['count']);

        $rows = [];

        foreach ($results as $result) {
            $row = [
                'uid' => (int)$result[$typo3Columns['uid']][0],
                'pid' => 0,
            ];
            foreach ($ldapColumns as $ldapKey => $localKey) {
                if (isset($result[$ldapKey][0])) {
                    unset($result[$ldapKey]['count']);
                    $row[$localKey] = implode(',', $result[$ldapKey]);
                } else {
                    $row[$localKey] = null;
                }
            }
            foreach ($extbaseColumns as $colName => $colConf) {
                if ($colConf['config']['type'] === 'inline') {
                    $row[$colName] = 1;
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param AbstractValueObject $object
     * @return mixed|void
     * @throws NotImplementedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getUidOfAlreadyPersistedValueObject(AbstractValueObject $object)
    {
        throw new NotImplementedException('Value Objects are not supported by LdapBackend');
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
    protected function getConfigForTable($tableName)
    {
        foreach ($this->config as $class => $config) {
            if (isset($config['mapping']['tableName']) && $config['mapping']['tableName'] === $tableName) {
                return $this->getConfigForClass($class);
            }
        }
        throw new \Exception('Could not identify config for table name');
    }

    /**
     * @param string $class
     * @return array
     */
    protected function getConfigForClass($class)
    {
        if (!isset($this->config[$class]['ldap_mapping'])) {
            throw new \InvalidArgumentException('Class ' . $class . ' is not configured');
        }
        return array_merge($this->config[$class], ['class' => $class]);
    }

    /**
     * @param array $fieldValues
     * @param array $typo3Columns
     * @return array
     */
    protected function mapValues(array $fieldValues, array $typo3Columns)
    {
        $row = [];
        foreach ($fieldValues as $typo3Name => $value) {
            if (isset($typo3Columns[$typo3Name])) {
                $ldapName = $typo3Columns[$typo3Name];
                if ($typo3Name === 'uid') {
                    $value = (int)$value;
                }
                $row[$ldapName] = $value;
            }
        }
        return $row;
    }

    /**
     * @param array $row
     * @param LdapDriver $driver
     * @return array
     */
    protected function setAutoValues(array $row, LdapDriver $driver)
    {
        if (in_array('posixAccount', $row['objectClass'])) {
            $uidNumber = 1000;
            $results = $driver->searchAndGetResults('', '(objectClass=posixAccount)', ['uidNumber']);
            unset($results['count']);
            foreach ($results as $result) {
                $uidNumber = max($uidNumber, $result['uidnumber'][0]);
            }
            $row['uidnumber'] = $uidNumber + 1;
        }
        return $row;
    }
}
