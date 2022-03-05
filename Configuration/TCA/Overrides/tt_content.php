<?php
defined('TYPO3_MODE') or die();

$_EXTKEY = 'mymap';

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
    },
    $_EXTKEY
);






$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['myttaddressmap_map'] = 'recursive,select_key,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['myttaddressmap_searchform'] = 'recursive,select_key,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['myttaddressmap_searchresult'] = 'recursive,select_key,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['myttaddressmap_singleview'] = 'recursive,select_key,pages';
