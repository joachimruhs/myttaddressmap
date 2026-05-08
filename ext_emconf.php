<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "myttaddressmap".
 *
 * Auto generated 09-01-2026 21:05
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'MyTTAddressMap',
  'description' => 'Google maps for tt_address with radial search and categories',
  'category' => 'plugin',
  'version' => '2.7.1',
  'state' => 'beta',
  'uploadfolder' => true,
  'clearcacheonload' => false,
  'author' => 'Joachim Ruhs',
  'author_email' => 'postmaster@joachim.ruhs.de',
  'author_company' => 'Web Services Ruhs',
  'constraints' => 
  array (
    'depends' => 
    array (
      'typo3' => '13.4.0-14.3.99',
      'tt_address' => '10.0.0-10.0.99',
    ),
    'conflicts' => 
    array (
    ),
    'suggests' => 
    array (
    ),
  ),
);

