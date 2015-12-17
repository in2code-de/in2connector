<?php
namespace In2code\In2template\Tests\Domain\Model;

use In2code\In2connector\Registry\ConnectionRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @coversDefaultClass \In2code\In2connector\Registry\ConnectionRegistry
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
     * @covers ::requireConnection
     * @return void
     */
    public function requireConnectionThrowsExceptionIfClassDoesNotInheritCorrectly()
    {
        $this->subject->requireConnection('foo', 'bar', GeneralUtility::class);
    }

}
