<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
	{

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'WSR.Myttaddressmap',
            'Map',
            [
                'Address' => 'ajaxSearch, list',
            ],
            // non-cacheable actions
            [
                'Address' => 'ajaxSearch, list'
            ]
        );

		// Plugin for AJAX-calls
		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
				'WSR.' . $extKey,
				'Ajax',
				array(
						'Ajax' => 'ajaxEid'
				),
				// non-cacheable actions
				array(
						'Ajax' => 'ajaxEid'
				)
		);


        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'WSR.Myttaddressmap',
            'SearchForm',
            [
                'Address' => 'searchForm',
            ],
            // non-cacheable actions
            [
                'Address' => 'searchForm'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'WSR.Myttaddressmap',
            'SearchResult',
            [
                'Address' => 'searchResult, searchForm',
            ],
            // non-cacheable actions
            [
                'Address' => 'searchResult, searchForm'
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'WSR.Myttaddressmap',
            'SingleView',
            [
                'Address' => 'singleView',
            ],
            // non-cacheable actions
            [
                'Address' => ''
            ]
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

	
    },
    $_EXTKEY
);


