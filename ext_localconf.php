<?php

// Prevent script from being called directly
defined('TYPO3') or die();

use \TYPO3\CMS\Extbase\Utility\ExtensionUtility;

// encapsulate all locally defined variables
call_user_func(
    function()
	{

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Myttaddressmap',
            'Map',
            [
				\WSR\Myttaddressmap\Controller\AddressController::class => 'ajaxSearch, list'
			],
            // non-cacheable actions
            [
				\WSR\Myttaddressmap\Controller\AddressController::class => 'ajaxSearch, list'
			],
            ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
        );

		// Plugin for AJAX-calls
		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Myttaddressmap',
			'Ajax',
			[\WSR\Myttaddressmap\Controller\AjaxController::class => 'ajaxEid'],
			// non-cacheable actions
			[\WSR\Myttaddressmap\Controller\AjaxController::class => 'ajaxEid'],
            ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
        );


        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Myttaddressmap',
            'SearchForm',
            [\WSR\Myttaddressmap\Controller\AddressController::class => 'searchForm'],
            // non-cacheable actions
            [\WSR\Myttaddressmap\Controller\AddressController::class => 'searchForm'],
            ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Myttaddressmap',
            'SearchResult',
            [\WSR\Myttaddressmap\Controller\AddressController::class => 'searchResult, searchForm'],
            // non-cacheable actions
            [\WSR\Myttaddressmap\Controller\AddressController::class => 'searchResult, searchForm'],
            ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Myttaddressmap',
            'SingleView',
            [\WSR\Myttaddressmap\Controller\AddressController::class => 'singleView'],
            // non-cacheable actions
            [\WSR\Myttaddressmap\Controller\AddressController::class => 'singleView'],
            ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
        );

    }
);


