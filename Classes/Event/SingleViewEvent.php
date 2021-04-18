<?php

namespace WSR\Myttaddressmap\Event;

use Psr\EventDispatcher\EventDispatcherInterface;

class SingleViewEvent
{
    /**
     * name
     * 
     * @var string
     */
    protected $name = '';

    /**
     * location
     * 
     * @var array
     */
	protected $location = [];	
	
/*	
    public function modifyLocation($location): void
    {
    }
*/	
    public function setName($name): void
    {
		$this->name = $name;
	}	
	
    public function getName(): string
    {
		return $this->name;
	}	

    public function setLocation($location): void
    {
		$this->location = $location;
	}	
    public function getLocation()
    {
		return $this->location;
	}	

}