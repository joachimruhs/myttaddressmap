<?php
defined('TYPO3_MODE') || die('Access denied.');


$tmp_myttaddressmap_columns = array(
	'mapicon' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:myttaddressmap/Resources/Private/Language/locallang_db.xlf:tx_myttaddressmap_domain_model_address.mapicon',
		'config' => [
		    'type' => 'select',
			'renderType' => 'selectSingle',
		    'items' => [
		        [ '', 0 ],
			],
			'fileFolder' => 'fileadmin/ext/myttaddressmap/Resources/Public/Icons/',
			'fileFolder_extList' => 'png,jpg,jpeg,gif',
			'fileFolder_recursions' => 0,
			'showIconTable' => 1,
			'fieldWizard' => [
	            'selectIcons' => [
	                'disabled' => false,
	            ],
	        ],

		    'size' => 1,
		    'minitems' => 0,
		    'maxitems' => 1,
		],
	),
	'mapgeocode' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:myttaddressmap/Resources/Private/Language/locallang_db.xlf:tx_myttaddressmap_domain_model_address.geocode',
		'config' => array(
			'type' => 'check',
			'default' => '1',
		),
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address',$tmp_myttaddressmap_columns);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'mapicon,mapgeocode;;,', '', 'after:title');

$GLOBALS['TCA']['tt_address']['types']['Tx_Myttaddressmap_Address']['showitem'] = $TCA['tt_address']['types']['0']['showitem'];
$GLOBALS['TCA']['tt_address']['types']['Tx_Myttaddressmap_Address']['showitem'] .= ',--div--;LLL:EXT:myttaddressmap/Resources/Private/Language/locallang_db.xlf:tx_myttaddressmap_domain_model_address,';
$GLOBALS['TCA']['fe_users']['types']['Tx_Myttaddressmap_Address']['showitem'] .= 'mapicon, mapgeocode';


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_myttaddressmap_domain_model_address', 'EXT:myttaddressmap/Resources/Private/Language/locallang_csh_tx_myttaddressmap_domain_model_address.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_myttaddressmap_domain_model_address');

