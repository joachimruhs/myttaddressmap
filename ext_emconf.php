<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "myttaddressmap".
 *
 * Auto generated 10-04-2021 10:01
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'MyTTAddressMap',
  'description' => 'Google maps for tt_address with radial search and categories',
  'category' => 'plugin',
  'author' => 'Joachim Ruhs',
  'author_email' => 'postmaster@joachim.ruhs.de',
  'state' => 'beta',
  'uploadfolder' => true,
  'clearCacheOnLoad' => 0,
  'version' => '1.6.6',
  'constraints' => 
  array (
    'depends' => 
    array (
      'typo3' => '10.4.0-11.5.99',
      'tt_address' => '5.0.0-6.1.99',
      'vhs' => '6.0.5'
    ),
    'conflicts' => 
    array (
    ),
    'suggests' => 
    array (
    ),
  ),
  'clearcacheonload' => false,
  'author_company' => 'Web Services Ruhs',
);
    