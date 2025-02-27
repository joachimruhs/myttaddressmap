<?php
namespace WSR\Myttaddressmap\Controller;

use TYPO3\CMS\Extbase\Annotation\Inject;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Core\Environment;

use TYPO3\CMS\Extbase\Http\ForwardResponse;


use Psr\EventDispatcher\EventDispatcherInterface;

use Symfony\Component\Filesystem\Filesystem;

/***
 *
 * This file is part of the "Myttaddressmap" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2024 Joachim Ruhs <postmaster@joachim-ruhs.de>, Web Services Ruhs
 *
 ***/

/**
 * AddressController
 */
class AddressController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

        public $conf;
  
	public function initializeObject() {
		//		$this->_GP = $this->request->getArguments();
		$configuration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$this->conf['storagePid'] = $configuration['persistence']['storagePid'];
	}

    /**
     * @var EventDispatcherInterface
     */
/*
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
*/



	/** 
	* @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher 
	*/ 
	protected $signalSlotDispatcher; 


	/**
	 * AddressRepository
	 *
	 * @var \WSR\Myttaddressmap\Domain\Repository\AddressRepository
	 */
	protected $addressRepository;

    /**
     * Inject a addressRepository to enable DI
     *
     * @param \WSR\Myttaddressmap\Domain\Repository\AddressRepository $addressRepository
     * @return void
     */
    public function injectAddressRepository(\WSR\Myttaddressmap\Domain\Repository\AddressRepository $addressRepository) {
        $this->addressRepository = $addressRepository;
    }

	/**
	 * categoryRepository
	 *
	 * @var \WSR\Myttaddressmap\Domain\Repository\CategoryRepository
	 */
	protected $categoryRepository;
	
    /**
     * Inject a categoryRepository to enable DI
     *
     * @param \WSR\Myttaddressmap\Domain\Repository\CategoryRepository $categoryRepository
     * @return void
     */
    public function injectCategoryRepository(\WSR\Myttaddressmap\Domain\Repository\CategoryRepository $categoryRepository) {
        $this->categoryRepository = $categoryRepository;
    }
	

	/**
	 * TTAddressRepository
	 *
	 * @var \FriendsOfTYPO3\TtAddress\Domain\Repository\AddressRepository
	 */
	protected $ttaddressRepository;

	
    /**
     * Inject a ttaddressRepository to enable DI
     *
     * @param \FriendsOfTYPO3\TtAddress\Domain\Repository\AddressRepository $ttaddressRepository
     * @return void
     */
    public function injectTtAddressRepository(\FriendsOfTYPO3\TtAddress\Domain\Repository\AddressRepository $ttaddressRepository) {
        $this->ttaddressRepository = $ttaddressRepository;
    }


	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {
		// not implemented yet
		$this->flashMessage('Extension: myttaddressmap',
			'Not implemented yet!',
            \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::INFO
			);
		return;
	
		$addresses = $this->addressRepository->findAll();
		//        $addresses = $this->addressRepository->findByNNNAll();
		$categories = $this->categoryRepository->findAll();

		// Get the default Settings
		$customStoragePid = $this->conf['storagePid'];
		$querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
		$querySettings->setStoragePageIds(array($customStoragePid));


		$this->categoryRepository->setDefaultQuerySettings($querySettings);
		$categories = $this->categoryRepository->findAll();


		for($i = 0; $i < count($categories); $i++) {
			$arr[$i]['uid']= $categories[$i]->getUid();
			if ($categories[$i]->getParent()) {
				$arr[$i]['parent'] = $categories[$i]->getParent()->getUid();
			} else $arr[$i]['parent'] = 0;
				
			$arr[$i]['title'] = $categories[$i]->getTitle();
		}
		$categories = $this->buildTree($arr);


		$this->view->assign('addresses', $addresses);
		$this->view->assign('locations', $addresses);
		$this->view->assign('categories', $categories);
	}

	/*
	 * build the category tree
	 *
	 * @var array $elements
	 * @var int $parentId
	 *
	 * @return array
	 */
	function buildTree(array &$elements, $parentId = 0) {
		$branch = array();
		foreach ($elements as &$element) {
			if ($element['parent'] == $parentId) {
				$children = $this->buildTree($elements, $element['uid']);
				if ($children) {
					$element['children'] = $children;
				}
				$branch[$element['uid']] = $element;
				unset($element);
			}
		}
		return $branch;
	}


	/**
	 * action show
	 *
	 * @param \WSR\Myttaddressmap\Domain\Model\Address $address
	 * @return void
	 */
	public function showAction(\WSR\Myttaddressmap\Domain\Model\Address $address)
	{
		$this->view->assign('address', $address);
	}

	
	/**
	 * populate map icon directory
	 *
	 * @return void
	 */
	public function populateMapIconDirectory() {
		$iconPath = 'fileadmin/ext/myttaddressmap/Resources/Public/Icons/';
   		if (!is_dir(Environment::getPublicPath() . '/' . $iconPath)) {
            $fileSystem = new FileSystem();
            if (Environment::getPublicPath() != Environment::getProjectPath()) {
                //  we are in composerMode
    			$sourceDir = Environment::getProjectPath() . '/vendor/wsr/myttaddressmap/Resources/Public/';
            } else {
                $sourceDir = Environment::getPublicPath() .'/typo3conf/ext/myttaddressmap/Resources/Public/';
            }
            $fileSystem->mirror($sourceDir, 'fileadmin/ext/myttaddressmap/Resources/Public/');
			$this->addFlashMessage('Directory ' . $iconPath . ' created for use with own mapIcons!', '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::INFO);
        }

/*
		if (!is_dir(Environment::getPublicPath() . '/' . $iconPath)) {
			$this->addFlashMessage('Directory ' . $iconPath . ' created for use with own mapIcons!', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::INFO);
			GeneralUtility::mkdir_deep(Environment::getPublicPath() . '/' . $iconPath);
			$sourceDir = 'typo3conf/ext/myttaddressmap/Resources/Public/Icons/';
			$files = GeneralUtility::getFilesInDir($sourceDir, 'png,gif,jpg');			
			foreach ($files as $file) {
				copy($sourceDir . $file, $iconPath . $file);
			}
		}
*/        
	}
	
	/**
	 * action ajaxSearch
	 *
	 * @return void
	 */
	public function ajaxSearchAction()
	{
		$this->populateMapIconDirectory();
		$this->updateLatLon();

		// check mapTheme
		if ($this->settings['mapTheme']) {
		    $themeFile = GeneralUtility::getFileAbsFileName($this->settings['mapTheme']);
			if (!is_file($themeFile)) {
				$this->flashMessage('Extension: myttaddressmap', \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('themeFileNotFound', 'myttaddressmap'), \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
				return;
			}
			$mapTheme = file_get_contents($themeFile);
			if (json_decode($mapTheme) == NULL) {
				$this->flashMessage('Extension: myttaddressmap', \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('invalidThemeFile', 'myttaddressmap'), \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
				return;
			}
		}		
	
		// Get the default Settings
		$customStoragePid = $this->conf['storagePid'];
        $querySettings = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
		$querySettings->setRespectStoragePage(true);
		$querySettings->setStoragePageIds(array($customStoragePid));
		
		$addresses = $this->addressRepository->findAll();

		$context = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
		$sys_language_uid = $context->getPropertyFromAspect('language', 'id'); 


    	$categories = $this->categoryRepository->findAllOverride($this->conf['storagePid'], $sys_language_uid);

//   		$this->typo3CategoryRepository->setDefaultQuerySettings($querySettings);
//		$this->typo3CategoryRepository->setDefaultOrderings(array('sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING));
//		$categories = $this->typo3CategoryRepository->findAll();

		$arr = [];
		for($i = 0; $i < count($categories); $i++) {

			// process only sys_categories of storagePid
			if (! GeneralUtility::inList($customStoragePid, $categories[$i]['pid'])) continue;
				
			$arr[$i]['uid']= $categories[$i]['uid'];
			if ($categories[$i]['parent']) {
				$arr[$i]['parent'] = $categories[$i]['parent'];
			} else $arr[$i]['parent'] = 0;
				
			$arr[$i]['title'] = $categories[$i]['title'];
		}
        if (!$arr) {
            $this->addFlashMessage('Please insert some sys_categories first!', 'Myttaddressmap', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::WARNING);
            return $this->responseFactory->createResponse()
                ->withAddedHeader('Content-Type', 'text/html; charset=utf-8')
                ->withBody($this->streamFactory->createStream($this->view->render()));
        }
		
		$categories = $this->buildTree($arr);


        $pageArguments = $this->request->getAttribute('routing');
        $pageId = $pageArguments->getPageId();

		$this->view->assign('id', $pageId);
		
		$this->view->assign('L', $sys_language_uid);
	
//		$this->view->assign('locations', $addresses);
		$this->view->assign('categories', $categories);

		$this->view->assign('locationsCount', count($addresses));
        return $this->responseFactory->createResponse()
            ->withAddedHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody($this->streamFactory->createStream($this->view->render()));
	}


	/**
	 * action singleView
	 * 
	 * @return void
	 */
	public function singleViewAction() {
		$this->_GP = $this->request->getArguments();

		if ($this->_GP['locationUid']) {// called from list link
			$location = $this->addressRepository->findByUid(intval($this->_GP['locationUid']));
		}
		else {
			$location = $this->addressRepository->findByUid(intval($this->settings['singleViewUid']));
		}
/*		
		// signal
		$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
		$ret = $signalSlotDispatcher->dispatch(__CLASS__, 'beforeSingleRenderView', array(&$location, &$this));
*/
		// event dispatch
		$event = GeneralUtility::makeInstance('WSR\Myttaddressmap\Event\SingleViewEvent');
		$event->setLocation($location);
		$this->eventDispatcher = GeneralUtility::getContainer()->get(EventDispatcherInterface::class);		
		$this->eventDispatcher->dispatch($event);
		

		$this->view->assign('location', $location);
		$this->view->assign('Lvar', $GLOBALS['TSFE']->config['config']['sys_language_uid'] ?? 0);
        return $this->responseFactory->createResponse()
            ->withAddedHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody($this->streamFactory->createStream($this->view->render()));
	}


	/**
	 * action searchForm
	 * 
	 * @param array $post
	 *  
	 * @return void
	 */
	public function searchFormAction($post = null) {
		$this->populateMapIconDirectory();

	   	$configuration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

	    $this->view->assign('GP', $_POST['tx_myttaddressmap_searchresult'] ?? []);
	    $this->view->assign('radius', intval($_POST['tx_myttaddressmap_searchresult']['radius'] ?? 0));

		$this->_GP['categories'] = $_POST['tx_myttaddressmap_searchresult']['categories'] ?? [];		
		
		// Get the default Settings
		$customStoragePid = $this->conf['storagePid'];
//		$querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');

//		$querySettings->setRespectStoragePage(true);
//		$querySettings->setStoragePageIds(GeneralUtility::intExplode(',', $customStoragePid));

//		$this->categoryRepository->setDefaultQuerySettings($querySettings);
//		$categories = $this->categoryRepository->findAll();

//		$this->typo3CategoryRepository->setDefaultQuerySettings($querySettings);
//		$this->typo3CategoryRepository->setDefaultOrderings(array('sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING));
		$context = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
		$sys_language_uid = $context->getPropertyFromAspect('language', 'id'); 

		$categories = $this->categoryRepository->findAllOverride($this->conf['storagePid'], $sys_language_uid);
//		$categories = $this->categoryRepository->findAll();

		// sanitizing categories						 
		if ($this->_GP['categories'] && preg_match('/^[0-9,]*$/', $this->_GP['categories']) != 1) {
			$this->_GP['categories'] = '';
		}		
		$categoryList = @implode(',', $this->_GP['categories']);

		$arr = [];
        if (is_array($categories)) {
			for($i = 0; $i < count($categories); $i++) {
				// process only sys_categories of storagePid
				if (! GeneralUtility::inList($customStoragePid, $categories[$i]['pid'])) continue;

				$arr[$i]['uid']= $categories[$i]['uid'];
	
				if (GeneralUtility::inList($categoryList, $arr[$i]['uid'])) $arr[$i]['selected'] = 1;
	
				if ($categories[$i]['parent']) {
					$arr[$i]['parent'] = $categories[$i]['parent'];
				} else $arr[$i]['parent'] = 0;
					
				$arr[$i]['title'] = $categories[$i]['title'];

			}
		}		
		$categories = $this->buildTree($arr);

		if (!count($arr)) {
			$this->addFlashMessage('No location categories found, please insert some first!', '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
		}

		$this->view->assign('categories', $categories);
        return $this->responseFactory->createResponse()
            ->withAddedHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody($this->streamFactory->createStream($this->view->render()));
        
	}


	/**
	 * action searchResult
	 * 
	 * @return void
	 */
	public function searchResultAction() {
		$this->updateLatLon();
		
		$this->_GP = $this->request->getArguments();
        $categories = [];

		// now get the startingpoint coordinates 
		$theAddress = array (
			'address' => $this->_GP['address'] ?? '',
			'zip' => $this->_GP['zipcode'] ?? '',
			'city' => $this->_GP['city'] ?? '',
			'country' => $this->_GP['country'] ?? '',
		);
		$latLon = $this->geocode($theAddress);

		if ($latLon->status == 'ZERO_RESULTS') {
			$this->flashMessage('Extension: myttaddressmap',
				\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('noStartingPointCoordinatesFound', 'myttaddressmap'),
				\TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::INFO);
            $params = $this->_GP;
			$params = array('post' => $params);
            return (new ForwardResponse('searchForm'))->withArguments(['post' => $params]);
		}

		if ($latLon->status != 'OK') {
			if ($latLon->status ==  '') $latLon->status = 'There was no status from Google returned. May be it help to set "useCurl" in install tool.';
			$this->flashMessage('Extension: myttaddressmap',
				$latLon->status,
				\TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::INFO);
            $params = $this->_GP;
			$params = array('post' => $params);
            return (new ForwardResponse('searchForm'))->withArguments(['post' => $params]);
		}

		if (!$this->conf['storagePid']) {
			$this->flashMessage('Extension: myttaddressmap', 'No storage pid defined! Please define some in the constant
								editor.',
								\TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
            return (new ForwardResponse('searchForm'));
		}


		// find all categories of all children
		// may be this can be commented
		//		$allCategories = $this->categoryRepository->findAllOverwrite();

		// sanitizing categories						 
        $this->_GP['categories'] = $this->_GP['categories'] ?? [];
		if ($this->_GP['categories'] && preg_match('/^[0-9,]*$/', implode(',', $this->_GP['categories'])) != 1) {
			$this->flashMessage('Extension: myttaddressmap', \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('errorInCategories', 'myttaddressmap'), \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
            return (new ForwardResponse('searchForm'));
		}						

        $categoryList = @implode(',', $this->_GP['categories'] ?? []);

		$page = 0;

		$orderBy = 'distance';

		$context = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
		$sys_language_uid = $context->getPropertyFromAspect('language', 'id'); 

        $categoryList = $this->categoryRepository->getCategoryList($categoryList, $this->conf['storagePid']);

        if ($this->settings['defaultLanguageUid'] > '') $sys_language_uid = $this->settings['defaultLanguageUid'];

		$locations = $this->addressRepository->findLocationsInRadius($latLon, $this->_GP['radius'], $categoryList,
						$this->conf['storagePid'], $sys_language_uid, $this->settings['resultLimit'], $page);

		// field images
		for ($i = 0; $i < count($locations); $i++) {
			
			$locations[$i]['description'] = str_replace("\r\n", '<br />', htmlspecialchars($locations[$i]['description'], ENT_QUOTES));
			$locations[$i]['address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', htmlspecialchars($locations[$i]['address'], ENT_QUOTES));
			
			if ($locations[$i]['image'] > 0) {
				if ($this->ttaddressRepository->findByUid($locations[$i]['uid'])) {
						$images = $this->ttaddressRepository->findByUid($locations[$i]['uid'])->getImage();
				}
				$locations[$i]['images'] =	$images;				
			}
			if ($locations[$i]['mapicon']) {			
				if (!is_file(Environment::getPublicPath() . "/fileadmin/ext/myttaddressmap/Resources/Public/Icons/" . $locations[$i]['mapicon'])) $locations[$i]['mapicon'] = 'questionmark.png';  
			}
		}

		if (is_array($locations)) {					
			if (count($locations) == 0) {
				$this->flashMessage('Extension: myttaddressmap', \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('noLocationsFound', 'myttaddressmap'), \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::INFO);
				$params = $this->_GP;
				$params = array('post' => $params);
	//			$this->redirect('searchForm', 'Address', 'myttaddressmap', $params);
			}

			for ($i = 0; $i < count($locations); $i++) {
				$categories[$i] = $category[$i][0]['categories'] ?? 0;
			}
		}

		/*
		// signal
		$ret = $this->signalSlotDispatcher->dispatch(__CLASS__, 'beforeSearchRenderView', array(&$locations, &$this));
		*/

		// event dispatch
		$event = GeneralUtility::makeInstance('WSR\Myttaddressmap\Event\SearchViewEvent');
		$event->setLocations($locations);
		$this->eventDispatcher = GeneralUtility::getContainer()->get(EventDispatcherInterface::class);		
		$this->eventDispatcher->dispatch($event);
		$locations = $event->getLocations();
		

		
		$this->view->assign('startingPoint', $latLon);
		$this->view->assign('categories', $categories);
		$this->view->assign('locations', $locations);

//		$this->view->assign("sys_language_uid", $GLOBALS['TSFE']->sys_language_uid);
		
		
        $this->view->assign('_GP', $this->_GP);
        if ( ($this->_GP['city'] || $this->_GP['zipcode'] ) || ($this->_GP['lat'] && $this->_GP['lon'] )) // from autocompleter ($this->_GP['lat'] && $this->_GP['lon'] )
            $this->view->assign('showMap', 1);

        return $this->responseFactory->createResponse()
            ->withAddedHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody($this->streamFactory->createStream($this->view->render()));
	}





	protected function updateLatLon() {
		$addresses = $this->addressRepository->updateLatLon($this->conf['storagePid']);

		for ($i = 0; $i < count($addresses); $i++) {	
			$theAddress = array (
				'uid' => $addresses[$i]['uid'],		
				'address' => $addresses[$i]['address'],		
				'zipcode' => $addresses[$i]['zip'],		
				'city' => $addresses[$i]['city'],		
				'country' => $addresses[$i]['country'],		
			);
			sleep(rand(1, 3)); // makes Google happy

			$latLon = $this->geocode($theAddress);

			$address = $this->addressRepository->findByUid($theAddress['uid']);
			if ($latLon->status == 'OK') {
				$address->setLatitude($latLon->lat);
				$address->setLongitude($latLon->lon);
				$this->addressRepository->update($address);
				$this->flashMessage('Myttaddressmap geocoder', 'Geocoded ' . $address->getName() . ' ' . $theAddress['city'] . ' ' . $theAddress['address'] . ' ' . $latLon->status,
					\TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::INFO);
			}
			else {
				$this->flashMessage('Myttaddressmap geocoder', 'could not geocode ' . $address->getName() . ' ' . $theAddress['city'] . ' ' . $theAddress['address'] . ' ' . $latLon->status,
					\TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
				$address->setMapgeocode(0);
				$this->addressRepository->update($address);
			}
				
		}
	}

		
	public function geocode($theAddress) {
		//for urlencoding
		$vars = array (
			'zipcode',
			'city',
			'address',
			'country'
		);

		foreach ($vars as $k => $v) {
			$theAddress[$v] = urlencode($theAddress[$v]);
		}
		
		$address = $theAddress['address'] ?? '';
		$city = $theAddress['city'] ?? '';
		$country = $theAddress['country'] ?? '';
		$zipcode = $theAddress['zipcode'] ?? '';


		######################################Main Geocoders#####################################

        // for geocoding we need a server API key not a browser key
        if ($this->settings['googleServerApiKey']) {
            $key = '&key=' . $this->settings['googleServerApiKey'];
        }				

        $apiURL = "https://maps.googleapis.com/maps/api/geocode/json?address=$address,+$zipcode+$city,+$country&sensor=false" . $key;

        $addressData = $this->get_webpage($apiURL);

        $latLon = new \stdClass();
        if (json_decode($addressData)->status == 'OK') {
            $coordinates[1] = json_decode($addressData)->results[0]->geometry->location->lat;
            $coordinates[0] = json_decode($addressData)->results[0]->geometry->location->lng;
    
            $latLon = new \stdClass();
            $latLon->lat = $coordinates[1];
            $latLon->lon = $coordinates[0];
        }
        $latLon->status = json_decode($addressData)->status;


		return $latLon;
	}

	function get_webpage($url) {
		//global $db;


/*
 to do 
 did we use this
 
		if (ini_get('allow_url_fopen'))
			$this->conf['useCurl'] = 0;
		else
			$this->conf['useCurl'] = 1;

		if ($this->conf['useCurl']) {
			$sessions = curl_init();
			curl_setopt($sessions, CURLOPT_URL, $url);
			curl_setopt($sessions, CURLOPT_HEADER, 0);
			curl_setopt($sessions, CURLOPT_RETURNTRANSFER, 1);
			$data = curl_exec($sessions);
			curl_close($sessions);
		} else {
			$data = \TYPO3\CMS\Core\Utility\GeneralUtility::getURL($url); 
		}
*/		
		$data = \TYPO3\CMS\Core\Utility\GeneralUtility::getURL($url); 
		return $data;
	}

	/**
	 * Flash a message
	 *
	 * @param string title 
	 * @param string message
	 * 
	 * @return void
	 */
	private function flashMessage($title, $message, $severity = \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::WARNING) {
		$this->addFlashMessage(
			$message,
			$title,
			$severity,
			$storeInSession = TRUE
		);
	}	




}
