<?php
namespace In2code\In2connector\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class LogEntry
 */
class LogEntry extends AbstractEntity
{
    /**
     * @var string
     */
    protected $requestId = '';

    /**
     * @var float
     */
    protected $timeMicro = 0.0;

    /**
     * @var string
     */
    protected $component = '';

    /**
     * @var int
     */
    protected $level = 0;

    /**
     * @var string
     */
    protected $message = '';

    /**
     * @var string
     */
    protected $data = '';

    /**
     * @return string
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @param string $requestId
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    }

    /**
     * @return float
     */
    public function getTimeMicro()
    {
        return $this->timeMicro;
    }

    /**
     * @param float $timeMicro
     */
    public function setTimeMicro($timeMicro)
    {
        $this->timeMicro = $timeMicro;
    }

    /**
     * @return string
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * @param string $component
     */
    public function setComponent($component)
    {
        $this->component = $component;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}
