<?php
namespace In2code\In2connector\Persistence\Storage;

use In2code\In2connector\Service\ConnectionService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\DomainObject\AbstractValueObject;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\BackendInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbBackend;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class DynamicStorageBackend
 */
class DynamicStorageBackend implements BackendInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var DataMapper
     */
    protected $dataMapper = null;

    /**
     * @var ConnectionService
     */
    protected $connectionService = null;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var BackendInterface[]
     */
    protected $instances = [];

    /**
     * DynamicStorageBackend constructor.
     *
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->dataMapper = $this->objectManager->get(DataMapper::class);
        $this->connectionService = $this->objectManager->get(ConnectionService::class);
        $this->config = $this->objectManager->get(ConfigurationManager::class)->getConfiguration('Framework');
    }

    /**
     * @param string $tableName
     * @return BackendInterface
     */
    protected function getBackendForTable($tableName)
    {
        if (!isset($this->instances[$tableName])) {
            if ($this->connectionService->hasConnection($tableName)) {
                $this->instances[$tableName] = $this->objectManager->get(LdapBackend::class);
            } else {
                $this->instances[$tableName] = $this->objectManager->get(Typo3DbBackend::class);
            }
        }
        return $this->instances[$tableName];
    }

    /**
     * @param QueryInterface $query
     * @return BackendInterface
     * @throws Exception
     */
    protected function getBackendForQuery(QueryInterface $query)
    {
        return $this->getBackendForTable($this->dataMapper->getDataMap($query->getType())->getTableName());
    }

    /**
     * @param AbstractValueObject $object
     * @return BackendInterface
     */
    protected function getBackendForObject(AbstractValueObject $object)
    {
        $a = '';
    }

    /**
     * @param string $tableName
     * @param array $fieldValues
     * @param bool $isRelation
     * @return int
     */
    public function addRow($tableName, array $fieldValues, $isRelation = false)
    {
        return $this->getBackendForTable($tableName)->addRow($tableName, $fieldValues, $isRelation);
    }

    /**
     * @param string $tableName
     * @param array $fieldValues
     * @param bool $isRelation
     * @return mixed
     */
    public function updateRow($tableName, array $fieldValues, $isRelation = false)
    {
        return $this->getBackendForTable($tableName)->updateRow($tableName, $fieldValues, $isRelation);
    }

    /**
     * @param string $tableName
     * @param array $fieldValues
     * @return bool
     */
    public function updateRelationTableRow($tableName, array $fieldValues)
    {
        return $this->getBackendForTable($tableName)->updateRelationTableRow($tableName, $fieldValues);
    }

    /**
     * @param string $tableName
     * @param array $where
     * @param bool $isRelation
     * @return mixed
     */
    public function removeRow($tableName, array $where, $isRelation = false)
    {
        return $this->getBackendForTable($tableName)->removeRow($tableName, $where, $isRelation);
    }

    /**
     * @param string $tableName
     * @param array $where
     * @param string $columnName
     * @return mixed
     */
    public function getMaxValueFromTable($tableName, array $where, $columnName)
    {
        return $this->getBackendForTable($tableName)->getMaxValueFromTable($tableName, $where, $columnName);
    }

    /**
     * @param QueryInterface $query
     * @return int
     * @throws Exception
     */
    public function getObjectCountByQuery(QueryInterface $query)
    {
        return $this->getBackendForQuery($query)->getObjectCountByQuery($query);
    }

    /**
     * @param QueryInterface $query
     * @return array
     * @throws Exception
     */
    public function getObjectDataByQuery(QueryInterface $query)
    {
        return $this->getBackendForQuery($query)->getObjectDataByQuery($query);
    }

    /**
     * @param AbstractValueObject $object
     * @return mixed
     */
    public function getUidOfAlreadyPersistedValueObject(AbstractValueObject $object)
    {
        return $this->getBackendForObject($object)->getUidOfAlreadyPersistedValueObject($object);
    }
}
