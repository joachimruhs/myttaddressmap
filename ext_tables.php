<?php
defined('TYPO3_MODE') || die('Access denied.');

/**
 * Register icons
 */
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconRegistry->registerIcon(
	'extension-myttaddressmap-content-element',
	\TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
	['source' => 'EXT:myttaddressmap/Resources/Public/Icons/contentElementIcon.png']
);
