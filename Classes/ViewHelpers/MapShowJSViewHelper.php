<?php
namespace WSR\Myttaddressmap\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/***
 *
 * This file is part of the "Myttaddressmap" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2018 - 2026 Joachim Ruhs <postmaster@joachim-ruhs.de>, Web Services Ruhs
 *
 ***/


class MapShowJSViewHelper extends AbstractViewHelper {
	/**
	* Arguments Initialization
	*/
	public function initializeArguments(): void {
		$this->registerArgument('location', 'mixed', 'The locations for the map', TRUE);
		$this->registerArgument('city', 'string', 'The city for the map', TRUE);
		$this->registerArgument('settings', 'mixed', 'The settings', TRUE);
	}


     /**
    * Returns the map javascript
    * 
    * @param array $arguments 
        * @return string
    */
    public function render(): string {
		 $location = $this->arguments['location'];
		 $city = $this->arguments['city'];
		 $settings = $this->arguments['settings'];

		$out = self::getMapJavascript($location, $settings);
		$out .= '<script type="text/javascript">function getMarkers() {';
			$lat = $location->getLatitude();
			$lon = $location->getLongitude();
			
			$out .= 'var myLatLng = new google.maps.LatLng(' . $lat. ',' . $lon .');';

			if ($location->getMapicon()) {
//  icon: "/fileadmin/ext/myttaddressmap/Resources/Public/Icons/' . $location->getMapicon() .'"
				$out .= '
						const markerIcon = document.createElement("img");
						markerIcon.src = "/fileadmin/ext/myttaddressmap/Resources/Public/Icons/' . $location->getMapicon() .'";
				';
				$out .= 'marker[0] = new google.maps.marker.AdvancedMarkerElement({
										position: myLatLng,
										map: map,
										title: "' . str_replace('"', '\"', $location->getName()) .'",
										});
										//mapBounds.extend(myLatLng);
										';
				$out .= 'marker[0].append(markerIcon);';
			} else {
				$out .= '
						const markerIcon = document.createElement("img");
						markerIcon.src = "' . $settings['defaultIcon'] . '";
				';
				$out .= 'marker[0] = new google.maps.marker.AdvancedMarkerElement({
										position: myLatLng,
										map: map,
										title: "' . str_replace('"', '\"', $location->getName()) .'",
										});
										//mapBounds.extend(myLatLng);
										';
				$out .= 'marker[0].append(markerIcon);';
			}

		$out .= '}</script>';
		return $out;
	 }
	 
	 public static function getMapJavascript($location, $settings) {
		$out = '<script type="text/javascript">
		var myOptions;
		var marker = [];
		var infoWindow = [];
		var map;
        var mapBounds = new google.maps.LatLngBounds();

		function load(){
			var circle = null;
		    var circleRadius = 1.5; // Miles

			var lon;
			var lat;

			var zoom1 = 16;

		    var latlng = new google.maps.LatLng(' . $location->getLatitude() . ',' . $location->getLongitude() . ');
		     myOptions = {
			  mapId: "' . ($settings['mapId'] ?? null ?: 'DEMO_MAP_ID') . '",
		      zoom: zoom1,
		      center: latlng,
		      mapTypeId: google.maps.MapTypeId.ROADMAP,
		      scaleControl: 1,
			  zoomControl: 1,
			  gestureHandling: "cooperative",

		//	  panControl: false,
		      disableDoubleClickZoom: 1,
//			  scrollwheel: true,
			';

			$out .= '			
		 	  streetViewControl: 1
		    };
		    map = new google.maps.Map(document.getElementById("map"), myOptions);
//			map.fitBounds(mapBounds);


		function addMarker(location) {
		  marker = new google.maps.Marker({
		    position: location,
		    map: map
		  });
		  markersArray.push(marker);
		}

		function removeMarker(marker) {
			if(marker.setMap != null) marker.setMap(null);
		}

		function showMarker(marker) {
		     marker.setMap(map);
		}

			getMarkers();

		// panning for mobile devices
		google.maps.event.addListener(map, "click",function(event) {
		   //map.setZoom(9);
//		   map.setCenter(event.latLng);
	   });
			
		} // load
		</script>';
		return $out;
	 }
	 
	 
}
