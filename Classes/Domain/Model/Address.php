<?php
namespace WSR\Myttaddressmap\Domain\Model;

//use TYPO3\CMS\Extbase\Annotation\Lazy;
use TYPO3\CMS\Core\Core\Environment;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2018-2020 Joachim Ruhs <postmaster@joachim-ruhs.de>, Web Services Ruhs
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Address
 */
class Address extends \FriendsOfTYPO3\TtAddress\Domain\Model\Address 
{

//     * @Extbase\ORM\Lazy



    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     */
    protected $categoriesX;


    /**
     * name
     * 
     * @var string
     */
    protected $name = '';


	/**
	 * mapicon
	 * 
	 * @var string
	 */
	protected $mapicon = '';

	/**
	 * mapgeocode
	 * 
	 * @var string
	 */
	protected $mapgeocode = '';

	/**
	 * Returns the mapicon
	 * 
	 * @return string $mapicon
	 */
	public function getMapicon() {
		if ($this->mapicon) {
			if (!is_file(Environment::getPublicPath() . "/fileadmin/ext/myttaddressmap/Resources/Public/Icons/" . $this->mapicon)) $this->mapicon = 'questionmark.png';
		}
		
		return $this->mapicon;
	}

	/**
	 * Sets the mapicon
	 * 
	 * @param string $mapicon
	 * @return void
	 */
	public function setMapicon($mapicon) {
		$this->mapicon = $mapicon;
	}


	/**
	 * Returns mapgeocode
	 * 
	 * @return string $mapgeocode
	 */
	public function getmapgeocode() {
		return $this->mapgeocode;
	}

	/**
	 * Sets mapgeocode
	 * 
	 * @param string $mapgeocode
	 * @return void
	 */
	public function setMapgeocode($mapgeocode) {
		$this->mapgeocode = $mapgeocode;
	}






    /**
     * Get categories
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     */
    public function getCategoriesX()
    {
        return $this->categories;
    }

    /**
     * Get first category
     *
     * @return Category
     */
    public function getFirstCategory()
    {
        $categories = $this->getCategories();
        if (!is_null($categories)) {
            $categories->rewind();
            return $categories->current();
        } else {
            return null;
        }
    }











    /**
     * Returns the name
     * 
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name
     * 
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
