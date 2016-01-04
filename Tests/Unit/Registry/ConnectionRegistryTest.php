<?php
namespace In2code\In2connector\Tests\Unit\Domain\Model;

use In2code\In2connector\Domain\Model\Dto\DriverRegistration;
use In2code\In2connector\Registry\ConnectionRegistry;
use In2code\In2connector\Tests\Unit\Registry\Fixtures\InvalidDriver;
use In2code\In2connector\Tests\Unit\Registry\Fixtures\ValidDriver;
use Prophecy\Argument;
use TYPO3\CMS\Core\Database\DatabaseConnection;
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
        $databaseProphecy = $this->prophesize(DatabaseConnection::class);
        $databaseProphecy->fullQuoteStr(Argument::exact('TX_IN2CONNECTOR'), Argument::exact('sys_registry'))->willReturn(
            'TX_IN2CONNECTOR'
        );
        $databaseProphecy->exec_SELECTgetRows(
            Argument::exact('*'),
            Argument::exact('sys_registry'),
            Argument::exact('entry_namespace = TX_IN2CONNECTOR')
        )->willReturn(
            [
                [
                    'uid' => 1,
                    'entry_namespace' => 'tx_in2connector',
                    'entry_key' => 'logs_per_page',
                    'entry_value' => 'i:13',
                ],
                [
                    'uid' => 2,
                    'entry_namespace' => 'tx_in2connector',
                    'entry_key' => 'log_level',
                    'entry_value' => 'i:5',
                ],
                [
                    'uid' => 3,
                    'entry_namespace' => 'tx_in2connector',
                    'entry_key' => 'production_context',
                    'entry_value' => 'b:1',
                ],
            ]
        );
        $GLOBALS['TYPO3_DB'] = $databaseProphecy->reveal();

        $this->subject = GeneralUtility::makeInstance(ConnectionRegistry::class);
    }

    /**
     * @test
     * @return void
     */
    public function registerDriverAddsDriverToRegisteredClasses()
    {
        $this->subject->registerDriver('validDriver', ValidDriver::class);
        $this->assertEquals(
            [
                'validDriver' => new DriverRegistration('validDriver', ValidDriver::class),
            ],
            $this->subject->getRegisteredDrivers()
        );
    }
}
