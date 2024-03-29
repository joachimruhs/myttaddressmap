﻿.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


What does it do?
================

The extension enables you to show tt_address locations in responsive Google maps.
You can do a radial search for locations and display them in a list and a responsive Google map with Google infoWindows. 
The extension is based on multiple plugins. This way you can use more than one plugin of the extension on the same page. 
A single view of the location is implemented too, this can be used to display just a Google map without doing a search when the locationUid is given
in the constant editor of TYPO3. 

Within the Ajaxsearch you can show all address-POIs of a country if you leave the address field empty and select the country of your choice.


What's new?
^^^^^^^^^^^

Mixed localizations implemented. To use this localize tt_address records with sys_language_uid = -1 and sys_category records with the
normal sys_language_uid and in the constant editor set defaultLanguageUid = -1.

TYPO3 9.x compatibility dropped. Signals removed and events PSR-14 implemented.


Screenshots
^^^^^^^^^^^

**Search form** 

.. image:: ../Images/Introduction/SearchForm.png
	:width: 200px
	:alt: Search form

**Search result with Google map** 

.. image:: ../Images/Introduction/SearchResult.png
	:width: 500px
	:alt: Search Result

**MyTTAddressMap (Map) result** 

.. image:: ../Images/Introduction/AjaxSearch.png
	:width: 500px
	:alt: Ajax Search Result

**MyTTAddressMap (Map) result with Retro theme** 

.. image:: ../Images/Introduction/AjaxSearchRetro.png
	:width: 500px
	:alt: Ajax Search Result Retro Theme


    

