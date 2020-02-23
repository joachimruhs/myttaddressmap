<?php
namespace WSR\Myttaddressmap\Tests\Unit\Domain\Model;

/**
 * Test case.
 *
 * @author Joachim Ruhs <postmaster@joachim.ruhs.de>
 */
class AddressTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \WSR\Myttaddressmap\Domain\Model\Address
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new \WSR\Myttaddressmap\Domain\Model\Address();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getNameReturnsInitialValueForString()
    {
        self::assertSame(
            '',
            $this->subject->getName()
        );

    }

    /**
     * @test
     */
    public function setNameForStringSetsName()
    {
        $this->subject->setName('Conceived at T3CON10');

        self::assertAttributeEquals(
            'Conceived at T3CON10',
            'name',
            $this->subject
        );

    }
}
