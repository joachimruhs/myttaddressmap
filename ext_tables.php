<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
    {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'Myttaddressmap',
            'Map',
            'MyTTAddressMap (Map)'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'Myttaddressmap',
            'SearchForm',
            'MyTTAddressMap (SearchForm)'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'Myttaddressmap',
            'SearchResult',
            'MyTTAddressMap (SearchResult)'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'Myttaddressmap',
            'SingleView',
            'MyTTAddressMap (SingleView)'
        );

		/**
		 * Register icons
		 */
		$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
		$iconRegistry->registerIcon(
			'extension-myttaddressmap-content-element',
			\TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
			['source' => 'EXT:myttaddressmap/Resources/Public/Icons/contentElementIcon.png']
		);
    },
    $_EXTKEY
);
