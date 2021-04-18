<?php

namespace WSR\Myttaddressmap\Event;

use Psr\EventDispatcher\EventDispatcherInterface;

class SearchViewEvent
{

    /**
     * locations
     * 
     * @var array
     */
	protected $locations = [];	

    public function setLocations($locations): void
    {
		$this->locations = $locations;
	}	
    public function getLocations()
    {
		return $this->locations;
	}	

}