<?php
namespace In2code\In2template\Tests\Domain\Model;

use In2code\In2connector\Domain\Model\SoapConnection;
use In2code\In2connector\Exceptions\ConnectionTypeNotSupportedException;
use In2code\In2connector\Registry\ConnectionRegistry;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ConnectionRegistryTest
 */
class ConnectionRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConnectionRegistry
     */
    private $subject;

    /**
     *
     */
    public function setUp()
    {
        $this->subject = GeneralUtility::makeInstance(ConnectionRegistry::class);
    }

    /**
     * @test
     * @return void
     */
    public function requireConnectionThrowsExceptionIfClassDoesNotInheritCorrectly()
    {
        $this->setExpectedException(
            ConnectionTypeNotSupportedException::class,
            'The connection class ' . htmlspecialchars(GeneralUtility::class) . ' is not supported',
            1450371768
        );
        $this->subject->requireConnection('foo', 'bar', GeneralUtility::class);
        $this->assertSame([], $this->subject->getRequiredConnections());
    }

    /**
     * @test
     * @return void
     */
    public function requireConnectionAddsGivenConnectionToArray()
    {
        $this->subject->requireConnection('foo', 'bar', SoapConnection::class);
        $this->assertSame(['foo' => ['bar' => SoapConnection::class]], $this->subject->getRequiredConnections());
    }

    /**
     * @test
     * @return void
     */
    public function connectionRegistryIsSingleton()
    {
        $this->subject->requireConnection('foo', 'bar', SoapConnection::class);
        $connectionRegistry = GeneralUtility::makeInstance(ConnectionRegistry::class);
        $this->assertInstanceOf(SingletonInterface::class, $connectionRegistry);
        $this->assertSame(
            ['foo' => ['bar' => SoapConnection::class]],
            $connectionRegistry->getRequiredConnections()
        );
    }
}
