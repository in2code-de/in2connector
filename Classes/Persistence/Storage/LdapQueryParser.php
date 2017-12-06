<?php
namespace In2code\In2connector\Persistence\Storage;

use TYPO3\CMS\Extbase\Persistence\Generic\Qom\Comparison;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\LogicalOr;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\PropertyValueInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class LdapQueryParser
 */
class LdapQueryParser
{
    /**
     * @param QueryInterface $query
     * @param array $propertyMap
     * @return string
     */
    public function parseQuery(QueryInterface $query, array $propertyMap)
    {
        $constraint = $query->getConstraint();
        $filter = $this->parseConstraint($constraint, $propertyMap);
        return $filter;
    }

    /**
     * @param ConstraintInterface $constraint
     * @param array $propertyMap
     * @return string
     */
    protected function parseConstraint(ConstraintInterface $constraint, array $propertyMap)
    {
        if ($constraint instanceof Comparison) {
            return $this->parseComparison($constraint, $propertyMap);
        } elseif ($constraint instanceof LogicalOr) {
            return $this->parseLogicalOr($constraint, $propertyMap);
        } else {
            \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
                $constraint
                ,
                __FILE__ . '@' . __LINE__,
                20,
                false,
                true,
                false,
                array()
            );
            die;
        }
    }

    /**
     * @param $operand
     * @return mixed
     */
    protected function parseOperand($operand)
    {
        if (is_string($operand)) {
            return $operand;
        }
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
            $operand
            ,
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
     * @param Comparison $constraint
     * @param array $propertyMap
     * @return string
     */
    protected function parseComparison(Comparison $constraint, array $propertyMap)
    {
        $filter = '';
        $operands = [];

        $operator = $constraint->getOperator();
        if ($operator === QueryInterface::OPERATOR_EQUAL_TO) {
            $operands[] = $this->parsePropertyValue($constraint->getOperand1(), $propertyMap);
            if (empty($constraint->getOperand2())) {
                $filter = '(!(%s=*))';
            } else {
                $filter = '(%s=%s)';
                $operands[] = $this->parseOperand($constraint->getOperand2());
            }
        } elseif ($operator === QueryInterface::OPERATOR_EQUAL_TO_NULL) {
            $filter = sprintf('(!(%s=*))', $this->parseOperand($constraint->getOperand1()));
        } elseif ($operator === QueryInterface::OPERATOR_NOT_EQUAL_TO) {
            $filter = '(%s=%s)';
        } elseif ($operator === QueryInterface::OPERATOR_NOT_EQUAL_TO_NULL) {
            $filter = '(%s=%s)';
        } elseif ($operator === QueryInterface::OPERATOR_LESS_THAN) {
            $filter = '(%s=%s)';
        } elseif ($operator === QueryInterface::OPERATOR_LESS_THAN_OR_EQUAL_TO) {
            $filter = '(%s=%s)';
        } elseif ($operator === QueryInterface::OPERATOR_GREATER_THAN) {
            $filter = '(%s=%s)';
        } elseif ($operator === QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO) {
            $filter = '(%s=%s)';
        } elseif ($operator === QueryInterface::OPERATOR_LIKE) {
            $operands[] = $this->parsePropertyValue($constraint->getOperand1(), $propertyMap);
            if (empty($constraint->getOperand2())) {
                $filter = '(!(%s=*))';
            } else {
                $filter = '(%s=*%s*)';
                $operands[] = $this->parseOperand($constraint->getOperand2());
            }
        } elseif ($operator === QueryInterface::OPERATOR_CONTAINS) {
            $filter = '(%s=%s)';
        } elseif ($operator === QueryInterface::OPERATOR_IN) {
            $filter = '(%s=%s)';
        } elseif ($operator === QueryInterface::OPERATOR_IS_NULL) {
            $filter = '(%s=%s)';
        } elseif ($operator === QueryInterface::OPERATOR_IS_EMPTY) {
            $filter = '(%s=%s)';
        }

        return vsprintf($filter, $operands);
    }

    /**
     * @param LogicalOr $logicalOr
     * @param array $propertyMap
     * @return string
     */
    protected function parseLogicalOr(LogicalOr $logicalOr, array $propertyMap)
    {
        $operands = [];
        $filter = '(|%s%s)';
        $operands[] = $this->parseConstraint($logicalOr->getConstraint1(), $propertyMap);
        $operands[] = $this->parseConstraint($logicalOr->getConstraint2(), $propertyMap);
        return vsprintf($filter, $operands);
    }
}
