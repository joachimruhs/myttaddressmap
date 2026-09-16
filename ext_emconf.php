<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "myttaddressmap".
 *
 * Auto generated 15-09-2026 12:07
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'MyTTAddressMap',
  'description' => 'Google maps for tt_address with radial search and categories',
  'category' => 'plugin',
  'version' => '3.0.5',
  'state' => 'beta',
  'uploadfolder' => true,
  'clearcacheonload' => false,
  'author' => 'Joachim Ruhs',
  'author_email' => 'postmaster@joachim-ruhs.de',
  'author_company' => 'Web Services Ruhs',
  'constraints' => 
  array (
    'depends' => 
    array (
      'typo3' => '13.0.0-14.99.99',
      'frontend' => '13.0.0-14.99.99',
    ),
    'conflicts' => 
    array (
    ),
    'suggests' => 
    array (
    ),
  ),
  'autoload' => 
  array (
    'psr-4' => 
    array (
      'WSR\\Myttaddressmap\\' => 'Classes/',
    ),
  ),
);

