<?php

namespace WSR\Myttaddressmap\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\Response;

/***
 *
 * This file is part of the "Booking" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2018 - 2022 Joachim Ruhs <postmaster@joachim-ruhs.de>, Web Services Ruhs
 *
 ***/

/**
 *
 *
 * @package myttaddressmap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * 
 */
class AjaxController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var LanguageService
	 */
	public $languageService;
	
	/**
	 * CustomerServerAssignment constructor.
	 */
	public function __construct()
	{
		/** @var LanguageService $languageService */
		$this->languageService = GeneralUtility::makeInstance('TYPO3\CMS\Core\Localization\LanguageService');
		$this->languageService->init(trim($_POST['tx_myttaddressmap_ajax']['language']));
	}

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
	 * action ajaxPage
	 * @return \string JSON
	 */
	public function ajaxPageAction() {
		// not used yet 
		$requestArguments = $this->request->getArguments();
		return json_encode($requestArguments);
	}
	
	/**
	 * action ajaxEidGeocode
	 * @return \stdclass $latLon
	 */
	public function ajaxEidGeocodeAction() {
		$requestArguments = $this->request->getParsedBody()['tx_myttaddressmap_ajax'];

		$address = urlencode($requestArguments['address']);
		$country = urlencode($requestArguments['country']);
		
		if ($this->settings['googleServerApiKey']) {
			$key = '&key=' . $this->settings['googleServerApiKey'];
		}				

		$apiURL = "https://maps.googleapis.com/maps/api/geocode/json?address=$address,+$country" . $key;
		$addressData = $this->get_webpage($apiURL);

		$coordinates[1] = json_decode($addressData)->results[0]->geometry->location->lat;
		$coordinates[0] = json_decode($addressData)->results[0]->geometry->location->lng;

		$latLon = new \stdClass();
		$latLon->lat = (float) $coordinates[1];
		$latLon->lon = (float) $coordinates[0];
		$latLon->status = json_decode($addressData)->status;

		return $latLon;
	}



	function get_webpage($url) {
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
		return $data;
	}


	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @param TYPO3\CMS\Core\Http\Response      $response
	 */
	public function indexAction(ServerRequestInterface $request, Response $response)
	{
		switch ($request->getMethod()) {
			case 'GET':
				$response = $this->processGetRequest($request, $response);
				break;
			case 'POST':
				$response = $this->processPostRequest($request, $response);
				break;
			default:
				$response->withStatus(405, 'Method not allowed');
		}
	
		return $response;
	}

	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @param \Psr\Http\Message\Response $response
	 */
	protected function processGetRequest(ServerRequestInterface $request, Response $response) {
		$response->withHeader('Content-type', ['text/html; charset=UTF-8']);
		$response->getBody()->write($view->render());
	}

	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @param TYPO3\CMS\Core\Http\Response      $response
	 */
	protected function processPostRequest(ServerRequestInterface $request, Response $response)
	{
		$queryParams = $request->getQueryParams();
	
		$frontend = $GLOBALS['TSFE'];

		/** @var TypoScriptService $typoScriptService */
		$typoScriptService = GeneralUtility::makeInstance('TYPO3\CMS\Core\TypoScript\TypoScriptService');
		$this->configuration = $typoScriptService->convertTypoScriptArrayToPlainArray($frontend->tmpl->setup['plugin.']['tx_myttaddressmap.']);
		$this->settings = $this->configuration['settings'];
		$this->conf['storagePid'] = $this->configuration['persistence']['storagePid'];
	
		$this->request = $request;
		$out = $this->ajaxEidAction();
	
	    $response->getBody()->write($out);
		return;

		//    $response->getBody()->write(json_encode($queryParams));
		//    $response->getBody()->write($out);
		
		/** @var Response $response */
		//$response = GeneralUtility::makeInstance(Response::class);
		//$response->getBody()->write($out);
		//return $response;
/*		
		$view = $this->getView();
		$hasErrors = false;
		// ... some logic
	
		if ($hasErrors) {
			$response->withHeader('Content-type', ['text/html; charset=UTF-8']);
			$response->getBody()->write($view->render());
		} else {
			$response->withHeader('Content-type', ['application/json; charset=UTF-8']);
			$response->getBody()->write(json_encode(['success' => true]));
		}
*/
	}


	/**
	 * @return \TYPO3\CMS\Fluid\View\StandaloneView
	 */
	protected function getView() {
	//    $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
		$templateService = GeneralUtility::makeInstance(TemplateService::class);
		// get the rootline
	//    $rootLine = $pageRepository->getRootLine($pageRepository->getDomainStartPage(GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY')));
		$rootlineUtility = GeneralUtility::makeInstance(RootlineUtility::class, 0);
	
		$rootLine = $rootlineUtility->get();
	
		// initialize template service and generate typoscript configuration
		$templateService->init();
		$templateService->runThroughTemplates($rootLine);
		$templateService->generateConfig();
	
		$fluidView = new StandaloneView();
		$fluidView->setLayoutRootPaths($templateService->setup['plugin.']['tx_yourext.']['view.']['layoutRootPaths.']);
		$fluidView->setTemplateRootPaths($templateService->setup['plugin.']['tx_yourext.']['view.']['templateRootPaths.']);
		$fluidView->setPartialRootPaths($templateService->setup['plugin.']['tx_yourext.']['view.']['partialRootPaths.']);
		$fluidView->getRequest()->setControllerExtensionName('YourExt');
		$fluidView->setTemplate('index');
	
		return $fluidView;
	}








	/**
	 * action ajaxEid
	 * @return string html
	 */
	public function ajaxEidAction() {
		$requestArguments = $this->request->getParsedBody()['tx_myttaddressmap_ajax'];
        $categoryList = '';
        $categories = [];
		if ($requestArguments['categories'] ?? [])
			$categoryList = @implode(',', $requestArguments['categories']);
		// sanitizing categories						 
		if ($categoryList && preg_match('/^[0-9,]*$/', $categoryList) != 1) {
			$categoryList = '';
		}		
	
		if ($this->settings['defaultLanguageUid'] > '') {
			$this->language = $this->settings['defaultLanguageUid'];
		} else {
			$this->language = $requestArguments['language'];		
		}		

// NEW
		// to minimize Google Server API requests
		// only geocode if no coordinates are given
		if ($requestArguments['lat'] == '' || $requestArguments['lon'] == '') {
			$latLon = $this->ajaxEidGeocodeAction();
		} else {
			$latLon = new \stdClass();
			$latLon->status = 'OK';
			$latLon->lat = (float) $requestArguments['lat'];
			$latLon->lon = (float) $requestArguments['lon'];
			
			if ($latLon->lat == 0 || $latLon->lon == 0)
				$latLon = $this->ajaxEidGeocodeAction();
		}
		if ($latLon->status != 'OK') {
			if ($latLon->status ==  '') $latLon->status = 'There was no status from Google returned. May be it helps to set "useCurl" in install tool.';

			$out = '<div class="ajaxMessage error">Geocoding Error: ' . $latLon->status . '</div>';
			$out .= '<script	type="text/javascript">
			$(".ajaxMessage").fadeIn(2000);
			</script>';
			return $out;
		} else {
//			$out .= '<script	type="text/javascript">
//				$("#tx_myttaddressmap_lat").val(' . $latLon->lat . ');
//				$("#tx_myttaddressmap_lon").val(' . $latLon->lon . ');
//			</script>';
		}
		
		$this->_GP['radius'] = (float) $requestArguments['radius'];

		$limit = $this->settings['resultLimit'];

		$page = intval($requestArguments['page']);
		if ($page == -1) {
			$limit = 1000;
			$page = 0;
		}

		if (!$requestArguments['address']) {
			$orderBy = 'city';
		} else {
			$orderBy = 'distance';
		}			




		if ($requestArguments['address'] == '') { // search locations of country
			$locations = $this->addressRepository->findLocationsOfCountry($latLon, $requestArguments['country'], $categoryList, $this->conf['storagePid'], $this->language, $limit, $page, $orderBy);
			$allLocations = $this->addressRepository->findLocationsOfCountry($latLon, $requestArguments['country'], $categoryList, $this->conf['storagePid'], $this->language, 1000, 0);
		} else {
			$locations = $this->addressRepository->findLocationsInRadius($latLon, $this->_GP['radius'], $categoryList, $this->conf['storagePid'], $this->language, $limit, $page);
			$allLocations = $this->addressRepository->findLocationsInRadius($latLon, $this->_GP['radius'], $categoryList, $this->conf['storagePid'], $this->language, 1000, 0);
		}

		// get all locations for the more-button
//		$allLocations = $this->addressRepository->findLocationsInRadius($latLon, $this->_GP['radius'], $this->_GP['categories'], $this->conf['storagePid'], 1000, 0);

		// field images
		for ($i = 0; $i < count($locations); $i++) {
			$locations[$i]['description'] = str_replace(array("\r\n", "\r", "\n"), '<br />', htmlspecialchars($locations[$i]['description'], ENT_QUOTES));
			$locations[$i]['infoWindowDescription'] = str_replace(array("\r\n", "\r", "\n"), '<br />', $locations[$i]['description']);  
			$address = $locations[$i]['address'];
			$locations[$i]['address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', $locations[$i]['address']);  

			$locations[$i]['infoWindowAddress'] = str_replace(array("\r\n", "\r", "\n"), '<br />', htmlspecialchars($address, ENT_QUOTES));

			if ($locations[$i]['image'] > 0) {
				if ($this->ttaddressRepository->findByUid($locations[$i]['uid'])) {
					$images = $this->ttaddressRepository->findByUid($locations[$i]['uid'])->getImage();
				}
				$locations[$i]['images'] =	$images;				
			}
		}

		if (count($locations) == 0) {
			$out = '<div class="ajaxMessage">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('noLocationsFound', 'myttaddressmap') .'</div>';
			$out .= '<script	type="text/javascript">';
			// remove marker from map
			$out .= 'for (var i = 0; i < marker.length; i++) {
				if (marker[i] !== undefined) {
					marker[i].setMap(null);
				}
			}
			$(".ajaxMessage").fadeIn(2000);
			</script>';
			return $out;
		}
			
		$out = $this->getMarkerJS($locations, $categories, $latLon, $this->_GP['radius']);
		
		// get  the loctions list
		
		if ($requestArguments['page'] != -1) { // do not show the list for page loading 
			$labels = [
				'distance' => $this->translate('distance'),
				'address' => $this->translate('address'),
				'zip' => $this->translate('zip'),
				'city' => $this->translate('city'),
				'country' => $this->translate('country'),
				'phone' => $this->translate('phone'),
				'email' => $this->translate('email'),
				'fax' => $this->translate('fax'),
				'route' => $this->translate('route'),

			];
			$out .= $this->getLocationsList($locations, $categories, $allLocations, $labels);
		}
		
		return $out;
	}


	function getChildren($arr, $id, $children) {
		for ($i = 0; $i < count($arr); $i++) {
			if ($arr[$i]['parent'] == $id) {
//				$children .= ',' . $arr[$i]['uid'];
				$children = $this->getChildren($arr, $arr[$i]['uid'], $children);
			}
		}
		
		return $id . ',' . $children;
//		return $children;
	}




	protected function getMarkerJS($locations, $categories, $latLon, $radius) {

		$out = '<script	type="text/javascript">';

		// remove marker from map
		$out .= 'for (i = 0; i < marker.length; i++) {
			if (marker[i] !== undefined) {
				marker[i].setMap(null);
			}
		}
		marker = [];
		';


		for ($i = 0; $i < count($locations); $i++) {


			$lat = $locations[$i]['latitude'];
			$lon = $locations[$i]['longitude'];
			
			if (!$lat) continue;

			$out .= 'var myLatLng = new google.maps.LatLng(' . $lat . ', ' . $lon .');';

			if ($locations[$i]['mapicon']) {
				//if (!is_file(Environment::getPublicPath() . "/fileadmin/ext/myttaddressmap/Resources/Public/Icons/" . $locations[$i]['mapicon'])) $locations[$i]['mapicon'] = 'questionmark.png';  
				$out .= 'marker[' . $i . '] = new google.maps.Marker({
									position: myLatLng,
									map: map,
									title: "' . str_replace('"', '\"', $locations[$i]['name']) .'",
									icon: "/fileadmin/ext/myttaddressmap/Resources/Public/Icons/' . $locations[$i]['mapicon'] .'"
									});
									mapBounds.extend(myLatLng);
									';
			
			
			} else {

				$out .= 'marker[' . $i . '] = new google.maps.Marker({
									position: myLatLng,
									map: map,
									title: "' . str_replace('"', '\"', $locations[$i]['name']) .'",
									icon: "' . $this->settings['defaultIcon'] . '"
									});
									mapBounds.extend(myLatLng);
									';
			}
		


			// infoWindows
			$out .= $this->renderFluidTemplate('AjaxLocationListInfoWindow.html', array('location' => $locations[$i], 'categories' => $categories, 'i' => $i,
																						'startingPoint' => $latLon, 'settings' => $this->settings));

			
		} // for



		$out .= 'map.fitBounds(mapBounds);';		
		return $out . '</script>';
	}
	
	function getLocationsList($locations, $categories, $allLocations, $labels) {

		$out .= $this->renderFluidTemplate('AjaxLocationList.html', array('locations' => $locations, 'categories' => $categories, 'labels' => $labels,
																		  'settings' => $this->settings, 'locationsCount' => count($allLocations)));
		return $out;
	}
	
	
	/**
	 * Renders the fluid template
	 * @param string $template
	 * @param array $assign
	 * @return string
	 */
	public function renderFluidTemplate($template, Array $assign = array()) {
      	$configuration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

		$templateRootPath = $this->configuration['view']['templateRootPaths'][1];

		if (!$templateRootPath) 	
		$templateRootPath = $this->configuration['view']['templateRootPath'][0];

		
		$templatePath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($templateRootPath . 'Address/' . $template);
		$view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$view->setTemplatePathAndFilename($templatePath);
		$view->assignMultiple($assign);


		return $view->render();
	}


	/**
	 * Returns the translation of $key
	 *
	 * @param string $key
	 * @return string
	 */
	protected function translate($key)
	{
		return $this->languageService->sL('LLL:EXT:myttaddressmap/Resources/Private/Language/locallang.xlf:' . $key);
	}

	
}

?>
