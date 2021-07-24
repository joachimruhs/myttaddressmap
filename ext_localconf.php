<?php

// Prevent script from being called directly
defined('TYPO3') or die();

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
			]
        );

		// Plugin for AJAX-calls
		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Myttaddressmap',
			'Ajax',
			[\WSR\Myttaddressmap\Controller\AjaxController::class => 'ajaxEid'],
			// non-cacheable actions
			[\WSR\Myttaddressmap\Controller\AjaxController::class => 'ajaxEid']
		);


        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Myttaddressmap',
            'SearchForm',
            [\WSR\Myttaddressmap\Controller\AddressController::class => 'searchForm'],
            // non-cacheable actions
            [\WSR\Myttaddressmap\Controller\AddressController::class => 'searchForm']
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Myttaddressmap',
            'SearchResult',
            [\WSR\Myttaddressmap\Controller\AddressController::class => 'searchResult, searchForm'],
            // non-cacheable actions
            [\WSR\Myttaddressmap\Controller\AddressController::class => 'searchResult, searchForm']
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Myttaddressmap',
            'SingleView',
            [\WSR\Myttaddressmap\Controller\AddressController::class => 'singleView'],
            // non-cacheable actions
            [\WSR\Myttaddressmap\Controller\AddressController::class => 'singleView']
        );

	// wizards
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
		'mod {
			wizards.newContentElement.wizardItems.plugins {
				elements {
					ajaxsearch {
						iconIdentifier = extension-myttaddressmap-content-element
						title = LLL:EXT:myttaddressmap/Resources/Private/Language/locallang_db.xlf:tx_myttaddressmap_domain_model_ajaxsearch
						description = LLL:EXT:myttaddressmap/Resources/Private/Language/locallang_db.xlf:tx_myttaddressmap_domain_model_ajaxsearch.description
						tt_content_defValues {
							CType = list
							list_type = myttaddressmap_map
						}
					}
				}
				show = *
			}
	   }'
	);

	
    }
);


