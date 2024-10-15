<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "myttaddressmap".
 *
 * Auto generated 30-07-2022 14:47
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'MyTTAddressMap',
  'description' => 'Google maps for tt_address with radial search and categories',
  'category' => 'plugin',
  'version' => '2.3.1',
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
      'typo3' => '12.4.0-13.4.99',
      'tt_address' => '9.0.0-9.1.99',
    ),
    'conflicts' => 
    array (
    ),
    'suggests' => 
    array (
    ),
  ),
);

