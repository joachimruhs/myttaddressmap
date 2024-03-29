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
 *  (c) 2018 - 2020 Joachim Ruhs <postmaster@joachim-ruhs.de>, Web Services Ruhs
 *
 ***/


class MapJSViewHelper extends AbstractViewHelper {
	/**
	* Arguments Initialization
	*/
	public function initializeArguments() {
		$this->registerArgument('locations', 'array', 'The locations for the map', TRUE);
		$this->registerArgument('city', 'string', 'The city for the map', TRUE);
		$this->registerArgument('settings', 'mixed', 'The settings', TRUE);
	}

    /**
    * Returns the map javascript
    * 
    * @param array $arguments 
    * @param \Closure $renderChildrenClosure
    * @param RenderingContextInterface $renderingContext
    * @return string
    */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
		$locations = $arguments['locations'];
		$city = $arguments['city'];
		$settings = $arguments['settings'];
        $animation = '';
		
		$out = self::getMapJavascript($locations, $settings);
		
		$out .= '<script type="text/javascript">
            function getMarkers() {';
			if (is_array($locations)) {


				for ($i = 0; $i < count($locations); $i++) {

					if (is_array($locations[$i])) {
						$lat = $locations[$i]['latitude'];
						$lon = $locations[$i]['longitude'];
						$mapIcon = $locations[$i]['mapicon'];
						$locationName = $locations[$i]['name'];
					} else {
						$lat = $locations[$i]->getLatitude();
						$lon = $locations[$i]->getLongitude();
						$locationName = $locations[$i]->getName();
						$mapIcon = $locations[$i]->getMapicon();
					}
					$out .= 'var myLatLng = new google.maps.LatLng(' . $lat. ',' . $lon .');';
		
		
					if ($mapIcon) {
					$out .= 'marker[' . $i . '] = new google.maps.Marker({
											position: myLatLng,
											map: map,
											title: "' . str_replace('"', '\"', $locationName) .'",
											icon: "/fileadmin/ext/myttaddressmap/Resources/Public/Icons/' . $mapIcon .'",
											' . $animation . '
											map: map
											});
											mapBounds.extend(myLatLng);
		
											';
					
					
					} else {
		
					$out .= 'marker[' . $i . '] = new google.maps.Marker({
											position: myLatLng,
											title: "' . str_replace('"', '\"', $locationName) .'",
											icon: "' . $settings['defaultIcon'] . '",

										' . $animation . '
											map: map
											});
											mapBounds.extend(myLatLng);
		
											';
					}
		
		
				}
			}
//            $out .= 'map.fitBounds(mapBounds);';

            $out .= '}</script>';
		return $out;
	 }
	 
	 public static function getMapJavascript($locations, $settings) {
        $out = '<script type="text/javascript">
        var myOptions;
        var marker = [];
        var infoWindow = [];
        var map;
        var mapBounds = new google.maps.LatLngBounds();
        
        function load(){
        
            var lon;
            var lat;
        
            var zoom1 = 9;
        
            var latlng = new google.maps.LatLng(' . $settings['initialMapCoordinates'] . ');
        
             myOptions = {
              zoom: zoom1,
              center: latlng,
        //		      mapTypeId: google.maps.MapTypeId.ROADMAP,
              scaleControl: true,
			  gestureHandling: "cooperative",
			  zoomControl: true,
              zoomControlOptions: {
                    position: google.maps.ControlPosition.LEFT_TOP
                },
        
              panControl: true,
			  draggable: 1,			  
              rotateControl: true,
//              rotateControlOptions: {
//                                position: google.maps.ControlPosition.LEFT_TOP
//                            },
              disableDoubleClickZoom: 1,
			  ';


            if ($settings['mapTheme']) {
			    $themeFile = GeneralUtility::getFileAbsFileName($settings['mapTheme']);

				if (is_file($themeFile)) {
					$mapTheme = file_get_contents($themeFile);
					if (json_decode($mapTheme) == NULL) {
	//					die('Incorrect mapTheme file: ' . $settings['mapTheme']);
					} else {
		                $out .= ' styles:' . $mapTheme .',';
					}
				}
			}


            if ($settings['enableStreetViewLayer'] ?? '') {                
                $out .= '  streetViewControl: 1,
                            streetViewControlOptions: {
                                position: google.maps.ControlPosition.LEFT_TOP
                            },
                        ';
            }
        
            $out .= '
            };
        
            map = new google.maps.Map(document.getElementById("map"), myOptions);
            if (mapBounds.length > 0)
        			map.fitBounds(mapBounds);

			// 45 degree images of cities		
			map.setTilt(45);
					
            ';
            
            if ($settings['enableBicyclingLayer']) {                
                $out .= '
                var bikeLayer = new google.maps.BicyclingLayer();
                bikeLayer.setMap(map);
                ';
            }

            if ($settings['enableTrafficLayer']) {                
                $out .= '
                var trafficLayer = new google.maps.TrafficLayer();
                trafficLayer.setMap(map);
                ';
            }

            $out .= '

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
//				   map.setCenter(event.latLng);
			   });
		

			} // load
				
				
        </script>';
        return $out;
	 }
	 

	 
}

?>