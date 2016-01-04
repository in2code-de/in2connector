<?php
namespace In2code\In2connector\Domain\Model\Dto;

/**
 * Class DriverRegistration
 */
class DriverRegistration
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $class = '';

    /**
     * DriverRegistration constructor.
     *
     * @param string $name
     * @param string $class
     */
    public function __construct($name, $class)
    {
        $this->name = $name;
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }
}
