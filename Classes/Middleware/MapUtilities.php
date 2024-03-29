<?php

namespace WSR\Myttaddressmap\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Service\TypoScriptService;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Http\Response;

/***
 *
 * This file is part of the "Myttaddressmap" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2018 - 2020 Joachim Ruhs <postmaster@joachim-ruhs.de>, Web Services Ruhs
 *
 ***/




class MapUtilities implements MiddlewareInterface {
		
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {

		/** @var NormalizedParams $normalizedParams */
		$normalizedParams = $request->getAttribute('normalizedParams');
		$typo3SiteUrl = $normalizedParams->getSiteUrl(); // Same as GeneralUtility::getIndpEnv('TYPO3_SITE_URL')

		$requestArguments = $request->getParsedBody()['tx_myttaddressmap_ajax'] ?? [];

		// Remove any output produced until now
		ob_clean();


		// continue only if action is ajaxPsr of extension myleaflet
		if (!isset($requestArguments['action']) || $requestArguments['action'] != 'ajaxPsr') return $handler->handle($request);

		$ajaxController = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('WSR\Myttaddressmap\Controller\AjaxController');

		$response = GeneralUtility::makeInstance(Response::class);
		$response->withHeader('Content-type', ['text/html; charset=UTF-8']);


		$out = $ajaxController->indexAction($request, $response);
		$response->getBody()->write($out);
        return $response;

	
//		$response = GeneralUtility::makeInstance(Response::class);
//		$response->getBody()->write($out);
	
		// the following code never reached!
        //$response = $handler->handle($request);
 
		// Set caching header for cache servers (like NGINX) in seconds
//        $response = $response->withHeader('X-Accel-Expires', '60');
//		$response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
 
//		$response->getBody()->write('I\'m content fetched via AJAX.' . json_encode($requestArguments));
//        return $response;

    }


}
