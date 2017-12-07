<?php
namespace In2code\In2connector\Persistence\Storage;

use In2code\In2connector\Driver\LdapDriver;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\Comparison;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\LogicalAnd;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\LogicalNot;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\LogicalOr;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\PropertyValueInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class LdapQueryParser
 */
class LdapQueryParser
{
    /**
     * @var LdapDriver
     */
    protected $driver = null;

    /**
     * LdapQueryParser constructor.
     */
    public function __construct()
    {
        $this->driver = GeneralUtility::makeInstance(LdapDriver::class);
    }

    /**
     * @param QueryInterface $query
     * @param array $config
     * @return string
     * @throws NotImplementedException
     */
    public function parseQuery(QueryInterface $query, array $config)
    {
        $propertyMap = array_flip($config['ldap_mapping']['columns']);
        $propertyMap['uid'] = $config['ldap_mapping']['uid'];

        $constraint = $query->getConstraint();
        if ($constraint instanceof ConstraintInterface) {
            return $this->parseConstraint($constraint, $propertyMap);
        }
        return sprintf('(%s=*)', $config['ldap_mapping']['id']);
    }

    /**
     * @param ConstraintInterface $constraint
     * @param array $propertyMap
     * @return string
     * @throws NotImplementedException
     */
    protected function parseConstraint(ConstraintInterface $constraint, array $propertyMap)
    {
        if ($constraint instanceof Comparison) {
            return $this->parseComparison($constraint, $propertyMap);
        } elseif ($constraint instanceof LogicalOr) {
            return $this->parseLogicalOr($constraint, $propertyMap);
        } elseif ($constraint instanceof LogicalAnd) {
            return $this->parseLogicalAnd($constraint, $propertyMap);
        } elseif ($constraint instanceof LogicalNot) {
            return $this->parseLogicalNot($constraint, $propertyMap);
        }
        return '';
    }

    /**
     * @param PropertyValueInterface $propertyValue
     * @param $propertyMap
     * @return string
     */
    protected function parsePropertyValue(PropertyValueInterface $propertyValue, $propertyMap)
    {
        $propertyName = $propertyValue->getPropertyName();
        if (isset($propertyMap[$propertyName])) {
            return $propertyMap[$propertyName];
        }
        return $propertyName;
    }

    /**
     * @param Comparison $comparison
     * @param array $propertyMap
     * @return string
     * @throws NotImplementedException
     */
    protected function parseComparison(Comparison $comparison, array $propertyMap)
    {
        $operands = [];
        $operator = $comparison->getOperator();
        $propertyName = $comparison->getOperand1()->getPropertyName();
        $propertyValue = $this->driver->escape($comparison->getOperand2());

        if (isset($propertyMap[$propertyName])) {
            $propertyName = $propertyMap[$propertyName];
        }

        if ($operator === QueryInterface::OPERATOR_EQUAL_TO) {
            $operands[] = $propertyName;
            if (empty($propertyValue)) {
                $filter = '!(%s=*)';
            } else {
                $filter = '%s=%s';
                $operands[] = $propertyValue;
            }
        } elseif ($operator === QueryInterface::OPERATOR_EQUAL_TO_NULL) {
            $filter = '!(%s=*)';
            $operands[] = $propertyName;
        } elseif ($operator === QueryInterface::OPERATOR_LESS_THAN) {
            $filter = '!(%s>=%s)';
            $operands[] = $propertyName;
            $operands[] = $propertyValue;
        } elseif ($operator === QueryInterface::OPERATOR_LESS_THAN_OR_EQUAL_TO) {
            $filter = '%s<=%s';
            $operands[] = $propertyName;
            $operands[] = $propertyValue;
        } elseif ($operator === QueryInterface::OPERATOR_GREATER_THAN) {
            $filter = '!(%s<=%s)';
            $operands[] = $propertyName;
            $operands[] = $propertyValue;
        } elseif ($operator === QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO) {
            $filter = '%s>=%s';
            $operands[] = $propertyName;
            $operands[] = $propertyValue;
        } elseif ($operator === QueryInterface::OPERATOR_LIKE) {
            $operands[] = $propertyName;
            if (empty($propertyValue)) {
                $filter = '!(%s=*)';
            } else {
                $filter = '%s=*%s*';
                $operands[] = $propertyValue;
            }
        } elseif ($operator === QueryInterface::OPERATOR_CONTAINS) {
            $filter = '|(%s=%s)(%s=*,%s)(%s=%s,*)(%s=*,%s,*)';
            $operands = [
                $propertyName,
                $propertyValue,
            ];
            $operands = array_merge($operands, $operands, $operands, $operands);
        } elseif ($operator === QueryInterface::OPERATOR_IN) {
            $filter = '|%s';
            $possibilities = '';
            foreach ($propertyValue as $value) {
                $subComparison = new Comparison($comparison->getOperand1(), QueryInterface::OPERATOR_EQUAL_TO, $value);
                $possibilities .= $this->parseComparison($subComparison, $propertyMap);
            }
            $operands[] = $possibilities;
        } else {
            throw new NotImplementedException('The operator was not implemented (because TYPO3 didn\'t either)');
        }

        return '(' . vsprintf($filter, $operands) . ')';
    }

    /**
     * @param LogicalOr $logicalOr
     * @param array $propertyMap
     * @return string
     * @throws NotImplementedException
     */
    protected function parseLogicalOr(LogicalOr $logicalOr, array $propertyMap)
    {
        $operands = [];
        $filter = '(|%s%s)';
        $operands[] = $this->parseConstraint($logicalOr->getConstraint1(), $propertyMap);
        $operands[] = $this->parseConstraint($logicalOr->getConstraint2(), $propertyMap);
        return vsprintf($filter, $operands);
    }

    /**
     * @param LogicalAnd $logicalAnd
     * @param array $propertyMap
     * @return string
     * @throws NotImplementedException
     */
    protected function parseLogicalAnd(LogicalAnd $logicalAnd, array $propertyMap)
    {
        $operands = [];
        $filter = '(&%s%s)';
        $operands[] = $this->parseConstraint($logicalAnd->getConstraint1(), $propertyMap);
        $operands[] = $this->parseConstraint($logicalAnd->getConstraint2(), $propertyMap);
        return vsprintf($filter, $operands);
    }

    /**
     * @param LogicalNot $logicalNot
     * @param array $propertyMap
     * @return string
     * @throws NotImplementedException
     */
    protected function parseLogicalNot(LogicalNot $logicalNot, array $propertyMap)
    {
        $operands = [];
        $filter = '(!%s)';
        $operands[] = $this->parseConstraint($logicalNot->getConstraint(), $propertyMap);
        return vsprintf($filter, $operands);
    }
}
