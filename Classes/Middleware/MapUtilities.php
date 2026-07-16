<?php

declare(strict_types=1);

namespace WSR\Myttaddressmap\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use WSR\Myttaddressmap\Controller\AjaxController;

final class MapUtilities implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $parsedBody = $request->getParsedBody();

        $requestArguments = is_array($parsedBody)
            ? ($parsedBody['tx_myttaddressmap_ajax'] ?? [])
            : [];

        /*
         * Nur den AJAX-Aufruf dieser Extension behandeln.
         */
        if (
            !is_array($requestArguments)
            || ($requestArguments['action'] ?? '') !== 'ajaxPsr'
        ) {
            return $handler->handle($request);
        }

        /*
         * Wichtig für TYPO3 14:
         *
         * Der AjaxController verwendet Extbase-Repositories.
         * Da der Aufruf außerhalb des normalen Extbase-Bootstraps erfolgt,
         * muss der aktuelle PSR-7-Request ausdrücklich am
         * ConfigurationManager gesetzt werden.
         *
         * Dies muss geschehen, bevor AddressRepository oder
         * CategoryRepository erzeugt werden.
         */
        $configurationManager = GeneralUtility::getContainer()->get(
            ConfigurationManagerInterface::class
        );

        $configurationManager->setRequest($request);

        /*
         * Seiten-ID aus dem Request lesen.
         */
        $queryParams = $request->getQueryParams();

        $pageId = isset($queryParams['id'])
            ? (int)$queryParams['id']
            : 0;

        /*
         * Der Controller erwartet im Konstruktor nur die Seiten-ID.
         */
        $ajaxController = GeneralUtility::makeInstance(
            AjaxController::class,
            $pageId
        );

        $response = GeneralUtility::makeInstance(Response::class);

        /*
         * Der Controller liefert bereits eine vollständige
         * PSR-7-Response zurück.
         */
        return $ajaxController->indexAction($request, $response);
    }
}

