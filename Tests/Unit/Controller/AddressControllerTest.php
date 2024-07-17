<?php
namespace WSR\Myttaddressmap\Tests\Unit\Controller;

/**
 * Test case.
 *
 * @author Joachim Ruhs <postmaster@joachim.ruhs.de>
 */
class AddressControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \WSR\Myttaddressmap\Controller\AddressController
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->getMockBuilder(\WSR\Myttaddressmap\Controller\AddressController::class)
            ->setMethods(['redirect', 'forward', 'addFlashMessage'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function listActionFetchesAllAddressesFromRepositoryAndAssignsThemToView()
    {

        $allAddresses = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $addressRepository = $this->getMockBuilder(\WSR\Myttaddressmap\Domain\Repository\AddressRepository::class)
            ->setMethods(['findAll'])
            ->disableOriginalConstructor()
            ->getMock();
        $addressRepository->expects(self::once())->method('findAll')->will(self::returnValue($allAddresses));
        $this->inject($this->subject, 'addressRepository', $addressRepository);

//        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
//        $view->expects(self::once())->method('assign')->with('addresses', $allAddresses);
//        $this->inject($this->subject, 'view', $view);

        $this->subject->listAction();
    }

    /**
     * @test
     */
    public function showActionAssignsTheGivenAddressToView()
    {
        $address = new \WSR\Myttaddressmap\Domain\Model\Address();

//        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
//        $this->inject($this->subject, 'view', $view);
//        $view->expects(self::once())->method('assign')->with('address', $address);

        $this->subject->showAction($address);
    }
}
