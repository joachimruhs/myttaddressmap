<?php

declare(strict_types=1);

namespace WSR\Myttaddressmap\Controller;

use FriendsOfTYPO3\TtAddress\Domain\Repository\AddressRepository as TtAddressRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use WSR\Myttaddressmap\Domain\Repository\AddressRepository;
use WSR\Myttaddressmap\Domain\Repository\CategoryRepository;


//NEU
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * AJAX/eIDx controller for myttaddressmap.
 *
 * This controller deliberately does not use constructor injection because the
 * existing eIDx dispatcher passes the current page UID as the first constructor
 * argument.
 */
final class AjaxController
{
    private int $pageId;
    
    private ?ServerRequestInterface $request = null;
    
    private ?AddressRepository $addressRepository = null;
    
    private ?CategoryRepository $categoryRepository = null;
    
    private ?TtAddressRepository $ttAddressRepository = null;
    
    private ?ViewFactoryInterface $viewFactory = null;
    
    private ?LanguageService $languageService = null;
    
    /**
     * TypoScript configuration below plugin.tx_myttaddressmap.
     *
     * @var array<string|int, mixed>
     */
    private array $configuration = [];
    
    /**
     * Extension settings.
     *
     * @var array<string|int, mixed>
     */
    private array $settings = [];
    
    /**
     * Internal configuration values.
     *
     * @var array<string, mixed>
     */
    private array $conf = [];
    
    private string|int $language = 0;
    
    private string $locale = '';
    // NEU CR
    private readonly LanguageServiceFactory $languageServiceFactory;
    
    /**
     * The legacy eIDx dispatcher passes the page UID here.
     */
    public function __construct(int $pageId = 0)
    {
        $this->pageId = $pageId;
      
    }
    
    /**
     * Entry point used by the existing eIDx dispatcher.
     */
    public function indexAction(
        ServerRequestInterface $request,
        ?Response $response = null
        ): ResponseInterface {
            $response ??= new Response();
            
            return match (strtoupper($request->getMethod())) {
                'POST' => $this->processPostRequest($request, $response),
                'GET' => $this->processGetRequest($request, $response),
                default => $this->createResponse(
                    $response,
                    'Method not allowed',
                    405,
                    'text/plain; charset=utf-8'
                    ),
            };
    }
    
    private function processGetRequest(
        ServerRequestInterface $request,
        Response $response
        ): ResponseInterface {
            return $this->createResponse(
                $response,
                'This endpoint expects a POST request.',
                405,
                'text/plain; charset=utf-8'
                );
    }
    
    private function processPostRequest(
        ServerRequestInterface $request,
        Response $response
        ): ResponseInterface {
            $this->request = $request;
            
            try {
                $this->initializeDependencies();
                $this->initializeTypoScriptConfiguration($request);
                
                $output = $this->ajaxEidAction();
                
                return $this->createResponse(
                    $response,
                    $output,
                    200,
                    'text/html; charset=utf-8'
                    );
            } catch (\Throwable $exception) {
                // Do not expose stack traces or internal paths in the AJAX response.
                return $this->createResponse(
                    $response,
                    'Myttaddressmap AJAX error: ' . htmlspecialchars(
                        $exception->getMessage(),
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                        ),
                    500,
                    'text/plain; charset=utf-8'
                    );
            }
    }
    
    /**
     * Loads services after construction because the legacy dispatcher passes
     * the page UID to the constructor.
     */
    private function initializeDependencies(): void
    {
        $this->categoryRepository = GeneralUtility::makeInstance(CategoryRepository::class);
        $this->addressRepository = GeneralUtility::makeInstance(AddressRepository::class);
        $this->ttAddressRepository = GeneralUtility::makeInstance(TtAddressRepository::class);
        $this->viewFactory = GeneralUtility::getContainer()->get(ViewFactoryInterface::class);
    }
    
    private function initializeTypoScriptConfiguration(
        ServerRequestInterface $request
        ): void {
            $frontendTypoScript = $request->getAttribute('frontend.typoscript');
            
            if ($frontendTypoScript === null || !method_exists($frontendTypoScript, 'getSetupArray')) {
                throw new \RuntimeException(
                    'The request does not contain initialized frontend TypoScript.',
                    1773487831
                    );
            }
            
            $setup = $frontendTypoScript->getSetupArray();
            
            $this->configuration = $setup['plugin.']['tx_myttaddressmap.'] ?? [];
            $this->settings = $this->configuration['settings.'] ?? [];
            $this->conf['storagePid'] =
            $this->configuration['persistence.']['storagePid']
            ?? $this->pageId;
    }
    
    /**
     * Main AJAX action.
     */
    private function ajaxEidAction(): string
    {
        
 
        $request = $this->requireRequest();
        $parsedBody = $request->getParsedBody();
        
        if (!is_array($parsedBody)) {
            throw new \InvalidArgumentException(
                'The request body is missing or invalid.',
                1773487832
                );
        }
        
        $requestArguments = $parsedBody['tx_myttaddressmap_ajax'] ?? null;
        
        if (!is_array($requestArguments)) {
            throw new \InvalidArgumentException(
                'The request parameter tx_myttaddressmap_ajax is missing.',
                1773487833
                );
        }
        
        $this->initializeLanguage($request, $requestArguments);
   
        $categoryList = $this->sanitizeCategoryList(
            $requestArguments['categories'] ?? []
            );
        
        $categoryList = $this->requireCategoryRepository()->getCategoryList(
            $categoryList,
            $this->conf['storagePid']
            );
        
        $languageUid = $requestArguments['language'] ?? 0;
        if (($this->settings['defaultLanguageUid'] ?? '') !== '') {
            $languageUid = (int)$this->settings['defaultLanguageUid'];
        }
        $this->language = $languageUid;

        $latLon = $this->resolveCoordinates($requestArguments);
        
        if (($latLon->status ?? '') !== 'OK') {
            $status = (string)($latLon->status ?? '');
            if ($status === '') {
                $status = 'Google returned no geocoding status.';
            }
            
            return '<div class="ajaxMessage error">Geocoding Error: '
                . htmlspecialchars($status, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '</div>'
                    . '<script type="text/javascript">'
                        . 'document.querySelectorAll(".ajaxMessage").forEach(function(element) {'
                            . 'element.style.display = "block";'
                                . '});'
                                    . '</script>';
        }
        
        $radius = (float)($requestArguments['radius'] ?? 0);
        $limit = max(1, (int)($this->settings['resultLimit'] ?? 20));
        $page = (int)($requestArguments['page'] ?? 0);
        
        if ($page === -1) {
            $limit = 1000;
            $page = 0;
        }

        $addressSearch = trim((string)($requestArguments['address'] ?? ''));
        $country = trim((string)($requestArguments['country'] ?? ''));
        $orderBy = $addressSearch === '' ? 'city' : 'distance';
        
        $repository = $this->requireAddressRepository();
        $storagePid = $this->conf['storagePid'];
        
        if ($addressSearch === '') {
            $locations = $repository->findLocationsOfCountry(
                $latLon,
                $country,
                $categoryList,
                $storagePid,
                $this->language,
                $limit,
                $page,
                $orderBy
                );
            
            $allLocations = $repository->findLocationsOfCountry(
                $latLon,
                $country,
                $categoryList,
                $storagePid,
                $this->language,
                1000,
                0,
                $orderBy
                );
        } else {
            $locations = $repository->findLocationsInRadius(
                $latLon,
                $radius,
                $categoryList,
                $storagePid,
                $this->language,
                $limit,
                $page
                );
            
            $allLocations = $repository->findLocationsInRadius(
                $latLon,
                $radius,
                $categoryList,
                $storagePid,
                $this->language,
                1000,
                0
                );
        }
        
        $locations = is_array($locations) ? $locations : [];
        $allLocations = is_array($allLocations) ? $allLocations : [];
        $locations = $this->prepareLocations($locations);
        
        if ($locations === []) {
            return '<div class="ajaxMessage">'
                . htmlspecialchars(
                    // REQUEST ERGÄNZT
                    $this->translate('noLocationsFound',$request),
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                    )
                    . '</div>'
                        . '<script type="text/javascript">'
                            . 'if (typeof marker !== "undefined") {'
                                . 'for (var i = 0; i < marker.length; i++) {'
                                    . 'if (marker[i] !== undefined && marker[i].setMap) { marker[i].setMap(null); }'
                                        . '}'
                                            . '}'
                                                . '</script>';
        }
  
        $categories = [];
        $output = $this->getMarkerJavaScript(
            $locations,
            $categories,
            $latLon,
            $radius
            );
   // NEU REQUEST ERGÄNZT
        if ((int)($requestArguments['page'] ?? 0) !== -1) {
            $labels = [
                'distance' => $this->translate('distance',$request),
                'address' => $this->translate('address',$request),
                'zip' => $this->translate('zip',$request),
                'city' => $this->translate('city',$request),
                'country' => $this->translate('country',$request),
                'phone' => $this->translate('phone',$request),
                'email' => $this->translate('email',$request),
                'fax' => $this->translate('fax',$request),
                'route' => $this->translate('route',$request),
            ];
         // ENDE NEU  
            $output .= $this->getLocationsList(
                $locations,
                $categories,
                $allLocations,
                $labels
                );
        }
     
        return $output;
    }
    
    /**
     * @param array<string|int, mixed> $requestArguments
     */
    private function resolveCoordinates(array $requestArguments): \stdClass
    {
        $latitude = (float)($requestArguments['lat'] ?? 0);
        $longitude = (float)($requestArguments['lon'] ?? 0);
        
        if ($latitude !== 0.0 && $longitude !== 0.0) {
            $coordinates = new \stdClass();
            $coordinates->status = 'OK';
            $coordinates->lat = $latitude;
            $coordinates->lon = $longitude;
            
            return $coordinates;
        }
        
        return $this->geocodeAddress(
            (string)($requestArguments['address'] ?? ''),
            (string)($requestArguments['country'] ?? '')
            );
    }
    
    private function geocodeAddress(string $address, string $country): \stdClass
    {
        $query = $address . ', ' . $country;
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='
            . rawurlencode($query);
            
            $apiKey = trim((string)($this->settings['googleServerApiKey'] ?? ''));
            if ($apiKey !== '') {
                $url .= '&key=' . rawurlencode($apiKey);
            }
            
            $responseBody = GeneralUtility::getURL($url);
            $decoded = is_string($responseBody)
            ? json_decode($responseBody, false, 512, JSON_THROW_ON_ERROR)
            : null;
            
            $result = new \stdClass();
            $result->status = is_object($decoded)
            ? (string)($decoded->status ?? '')
            : '';
            
            if (
                $result->status === 'OK'
                && isset($decoded->results[0]->geometry->location->lat)
                && isset($decoded->results[0]->geometry->location->lng)
                ) {
                    $result->lat = (float)$decoded->results[0]->geometry->location->lat;
                    $result->lon = (float)$decoded->results[0]->geometry->location->lng;
                }
                
                return $result;
    }
    
    /**
     * @param array<int, array<string, mixed>> $locations
     * @return array<int, array<string, mixed>>
     */
    private function prepareLocations(array $locations): array
    {
        $repository = $this->ttAddressRepository;
        
        foreach ($locations as &$location) {
            $description = (string)($location['description'] ?? '');
            $address = (string)($location['address'] ?? '');
            
            $escapedDescription = htmlspecialchars(
                $description,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
                );
            
            $location['description'] = nl2br($escapedDescription, false);
            $location['infoWindowDescription'] = $location['description'];
            $location['address'] = nl2br($address, false);
            $location['infoWindowAddress'] = nl2br(
                htmlspecialchars(
                    $address,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                    ),
                false
                );
            
            if (
                (int)($location['image'] ?? 0) > 0
                && $repository !== null
                && isset($location['uid'])
                ) {
                    $addressObject = $repository->findByUid((int)$location['uid']);
                    if ($addressObject !== null) {
                        $location['images'] = $addressObject->getImage();
                    }
                }
        }
        unset($location);
        
        return $locations;
    }
    
    /**
     * @param array<int, array<string, mixed>> $locations
     * @param array<int|string, mixed> $categories
     */
    private function getMarkerJavaScript(
        array $locations,
        array $categories,
        \stdClass $latLon,
        float $radius
        ): string {
            $output = '<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>';
            $output .= '<script type="text/javascript">';
            $output .= '
            if (typeof marker !== "undefined") {
                for (var i = 0; i < marker.length; i++) {
                    if (marker[i] !== undefined && marker[i].setMap) {
                        marker[i].setMap(null);
                    }
                }
            }
            marker = [];
        ';
            
            foreach ($locations as $index => $location) {
                $latitude = (float)($location['latitude'] ?? 0);
                $longitude = (float)($location['longitude'] ?? 0);
                
                if ($latitude === 0.0) {
                    continue;
                }
                
                $title = json_encode(
                    (string)($location['name'] ?? ''),
                    JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    );
                
                $mapIcon = trim((string)($location['mapicon'] ?? ''));
                $iconUrl = $mapIcon !== ''
                    ? '/fileadmin/ext/myttaddressmap/Resources/Public/Icons/' . rawurlencode($mapIcon)
                    : (string)($this->settings['defaultIcon'] ?? '');
                    
                    $encodedIconUrl = json_encode(
                        $iconUrl,
                        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES
                        );
                    
                    $output .= sprintf(
                        '
                var myLatLng%d = new google.maps.LatLng(%F, %F);
                var markerIcon%d = document.createElement("img");
                markerIcon%d.src = %s;
                marker[%d] = new google.maps.marker.AdvancedMarkerElement({
                    position: myLatLng%d,
                    map: map,
                    title: %s
                });
                mapBounds.extend(myLatLng%d);
                marker[%d].append(markerIcon%d);
                ',
                        $index,
                        $latitude,
                        $longitude,
                        $index,
                        $index,
                        $encodedIconUrl,
                        $index,
                        $index,
                        $title,
                        $index,
                        $index,
                        $index
                        );
                    
                    $output .= $this->renderFluidTemplate(
                        'AjaxLocationListInfoWindow',
                        [
                            'location' => $location,
                            'categories' => $categories,
                            'i' => $index,
                            'startingPoint' => $latLon,
                            'settings' => $this->settings,
                        ]
                        );
            }
            
            if (!empty($this->settings['enableMarkerClusterer'])) {
                $output .= '
                markerClusterer = new markerClusterer.MarkerClusterer({
                    map: window.map,
                    markers: marker
                });
            ';
            }
            
            $output .= '
            map.fitBounds(mapBounds);
        ';
            
            if (count($locations) <= 1) {
                $output .= 'map.setZoom(16);';
            }
            
            return $output . '</script>';
    }
    
    /**
     * @param array<int, array<string, mixed>> $locations
     * @param array<int|string, mixed> $categories
     * @param array<int, array<string, mixed>> $allLocations
     * @param array<string, string> $labels
     */
    private function getLocationsList(
        array $locations,
        array $categories,
        array $allLocations,
        array $labels
        ): string {
            return $this->renderFluidTemplate(
                'AjaxLocationList',
                [
                    'locations' => $locations,
                    'categories' => $categories,
                    'labels' => $labels,
                    'settings' => $this->settings,
                    'locationsCount' => count($allLocations),
                ]
                );
    }
    
    /**
     * @param array<string, mixed> $variables
     */
    private function renderFluidTemplate(
        string $templateName,
        array $variables
        ): string {
            $request = $this->requireRequest();
            $viewFactory = $this->requireViewFactory();
            
            $viewConfiguration = $this->configuration['view.'] ?? [];
            
            $templateRootPaths = $this->normalizeTypoScriptPaths(
                $viewConfiguration['templateRootPaths.']
                ?? ['EXT:myttaddressmap/Resources/Private/Templates']
                );
            
            $partialRootPaths = $this->normalizeTypoScriptPaths(
                $viewConfiguration['partialRootPaths.']
                ?? ['EXT:myttaddressmap/Resources/Private/Partials']
                );
            
            $layoutRootPaths = $this->normalizeTypoScriptPaths(
                $viewConfiguration['layoutRootPaths.']
                ?? ['EXT:myttaddressmap/Resources/Private/Layouts']
                );
            
            $viewFactoryData = new ViewFactoryData(
                templateRootPaths: $templateRootPaths,
                partialRootPaths: $partialRootPaths,
                layoutRootPaths: $layoutRootPaths,
                request: $request,
                );
            
            $view = $viewFactory->create($viewFactoryData);
            $view->assignMultiple($variables);
            
            return $view->render(
                'Address/' . preg_replace('/\.html$/i', '', $templateName)
                );
    }
    
    /**
     * @param mixed $paths
     * @return array<int|string, string>
     */
    private function normalizeTypoScriptPaths(mixed $paths): array
    {
        if (is_string($paths) && $paths !== '') {
            return [$paths];
        }
        
        if (!is_array($paths)) {
            return [];
        }
        
        $normalized = [];
        foreach ($paths as $key => $path) {
            if (is_string($path) && trim($path) !== '') {
                $normalized[$key] = $path;
            }
        }
        
        return $normalized;
    }
    
    /**
     * @param mixed $categories
     */
    private function sanitizeCategoryList(mixed $categories): string
    {
        if (!is_array($categories)) {
            return '';
        }
        
        $categoryIds = [];
        foreach ($categories as $category) {
            $categoryId = filter_var($category, FILTER_VALIDATE_INT);
            if ($categoryId !== false && $categoryId >= 0) {
                $categoryIds[] = (int)$categoryId;
            }
        }
        
        return implode(',', array_unique($categoryIds));
    }
    
    /**
     * @param array<string|int, mixed> $requestArguments
     */
    private function initializeLanguage(
        ServerRequestInterface $request,
        array $requestArguments
        ): void {
            $requestedLanguageId = (int)($requestArguments['language'] ?? 0);
            $site = $request->getAttribute('site');
            
            if ($site !== null && method_exists($site, 'getLanguageById')) {
                try {
                    $siteLanguage = $site->getLanguageById($requestedLanguageId);
                    $this->locale = (string)$siteLanguage->getLocale();
                    $languageCode = $siteLanguage->getTypo3Language();
                    
                    if ($languageCode === '') {
                        $languageCode = explode('_', str_replace('-', '_', $this->locale))[0];
                    }
                    
                    $this->languageService = GeneralUtility::makeInstance(
                        LanguageServiceFactory::class
                        )->create($languageCode);
                        return;
                } catch (\Throwable) {
                    // Fall back to the request language below.
                }
            }
            
            $languageAspect = $request->getAttribute('language');
            $languageCode = 'default';
            
            if ($languageAspect !== null && method_exists($languageAspect, 'getLocale')) {
                $this->locale = (string)$languageAspect->getLocale();
                $languageCode = explode('_', str_replace('-', '_', $this->locale))[0] ?: 'default';
            }
            
            $this->languageService = GeneralUtility::makeInstance(
                LanguageServiceFactory::class
                )->create($languageCode);
    }
    //  NEU FUNKTION TRANSLATE
    private function translate(
        string $key,
        ServerRequestInterface $request
        ): string {
            $siteLanguage = $request->getAttribute('language');
            
            if (!$siteLanguage instanceof SiteLanguage) {
                $site = $request->getAttribute('site');
                
                if ($site instanceof Site) {
                    $siteLanguage = $site->getDefaultLanguage();
                }
            }
            
            if (!$siteLanguage instanceof SiteLanguage) {
                return $key;
            }
            
            $languageServiceFactory = GeneralUtility::makeInstance(
                LanguageServiceFactory::class
                );
            
            $languageService = $languageServiceFactory
            ->createFromSiteLanguage($siteLanguage);
            
            $labelReference =
            'LLL:EXT:myttaddressmap/Resources/Private/Language/locallang.xlf:'
                . $key;
                
                $translated = $languageService->sL($labelReference);
                
                return $translated !== '' ? $translated : $key;
    }
    
    private function createResponse(
        Response $response,
        string $content,
        int $status = 200,
        string $contentType = 'text/html; charset=utf-8'
        ): ResponseInterface {
            $response = $response
            ->withStatus($status)
            ->withHeader('Content-Type', $contentType);
            
            $response->getBody()->write($content);
            
            return $response;
    }
    
    private function requireRequest(): ServerRequestInterface
    {
        if (!$this->request instanceof ServerRequestInterface) {
            throw new \LogicException(
                'The AJAX request has not been initialized.',
                1773487834
                );
        }
        
        return $this->request;
    }
    
    private function requireAddressRepository(): AddressRepository
    {
        if (!$this->addressRepository instanceof AddressRepository) {
            throw new \LogicException(
                'The address repository has not been initialized.',
                1773487835
                );
        }
        
        return $this->addressRepository;
    }
    
    private function requireCategoryRepository(): CategoryRepository
    {
        if (!$this->categoryRepository instanceof CategoryRepository) {
            throw new \LogicException(
                'The category repository has not been initialized.',
                1773487836
                );
        }
        
        return $this->categoryRepository;
    }
    
    private function requireViewFactory(): ViewFactoryInterface
    {
        if (!$this->viewFactory instanceof ViewFactoryInterface) {
            throw new \LogicException(
                'The Fluid view factory has not been initialized.',
                1773487837
                );
        }
        
        return $this->viewFactory;
    }
}
