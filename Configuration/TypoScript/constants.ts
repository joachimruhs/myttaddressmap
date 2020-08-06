

plugin.tx_myttaddressmap {
    view {

    # cat=plugin.tx_myttaddressmap/file; type=string; label=Path to template root (FE)
    templateRootPath = EXT:myttaddressmap/Resources/Private/Templates/
    # cat=plugin.tx_myttaddressmap/file; type=string; label=Path to template partials (FE)
    partialRootPath = EXT:myttaddressmap/Resources/Private/Partials/
    # cat=plugin.tx_myttaddressmap/file; type=string; label=Path to template layouts (FE)
    layoutRootPath = EXT:myttaddressmap/Resources/Private/Layouts/

		# customsubcategory=javascript=Javascript
		# cat=plugin.tx_myttaddressmap/javascript; type=boolean; label=Include jQuery core: Add jQuery core script. Turn it off (0), if jQuery is already added.
		includejQueryCore = 0

		# customsubcategory=css=CSS
		# cat=plugin.tx_myttaddressmap/css; type=string; label=CSS file
		cssFile = EXT:myttaddressmap/Resources/Public/CSS/myttaddressmap.css

		# cat=plugin.tx_myttaddressmap/javascript; type=string; label=jQuery library
		jQueryFile = EXT:myttaddressmap/Resources/Public/JavaScript/jquery-3.3.1.min.js

		# cat=plugin.tx_myttaddressmap/javascript; type=string; label=Javascript file
		javascriptFile = EXT:myttaddressmap/Resources/Public/JavaScript/myttaddressmap.js

  }
  persistence {
    # cat=plugin.tx_myttaddressmap//a; type=string; label=Default storage PID
    storagePid =
  }

	settings {
		# customsubcategory=googlemaps=Google maps
		# cat=plugin.tx_myttaddressmap/googlemaps; type=string; label=Default map icon: Default icon for the POI's in the map 
		defaultIcon = /typo3conf/ext/myttaddressmap/Resources/Public/Icons/pointerBlue.png

		# cat=plugin.tx_myttaddressmap/googlemaps; type=string; label=Google browser API key: Insert a Google browser API key, get one at https://console.developers.google.com
		googleBrowserApiKey = 

		# cat=plugin.tx_myttaddressmap/googlemaps; type=string; label=Google server API key: Insert a Google server API key, get one at https://console.developers.google.com
		googleServerApiKey = 

		# cat=plugin.tx_myttaddressmap/googlemaps; type=int; label=Result page ID: Result page ID
		resultPageId = 

		# cat=plugin.tx_myttaddressmap/googlemaps; type=int; label=Details page ID: Details page ID
		detailsPageId = 

		# cat=plugin.tx_myttaddressmap/googlemaps; type=int; label=Single view uid: Uid for the singleView plugin
		singleViewUid = 1

		# cat=plugin.tx_myttaddressmap/googlemaps; type=int; label=Result limit: Limit of results
		resultLimit = 300

		# cat=plugin.tx_myttaddressmap/googlemaps; type=string; label=Initial map coordinates: Initial map coordinates
		initialMapCoordinates = 48,8

		# cat=plugin.tx_myttaddressmap/googlemaps; type=boolean; label=Enable traffic layer: Add traffic layer to the maps
		enableTrafficLayer = 0

		# cat=plugin.tx_myttaddressmap/googlemaps; type=boolean; label=Enable bicycling layer: Add bicycling layer to the maps
		enableBicyclingLayer = 0

		# cat=plugin.tx_myttaddressmap/googlemaps; type=string; label=Google map theme: File with Google map theme in json format. You can generate your own here: https://mapstyle.withgoogle.com/
		mapTheme = 

		# cat=plugin.tx_myttaddressmap/googlemaps; type=string; label=Default languageUid: Use 0 in multi language sites to override selected language in Frontend and if tt_adress record are not localized. Leave it blank to use TYPO3 localization.
		defaultLanguageUid = 

	}



}
