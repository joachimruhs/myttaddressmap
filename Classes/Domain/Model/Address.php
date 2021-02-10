<?php
namespace WSR\Myttaddressmap\Domain\Model;

//use TYPO3\CMS\Extbase\Annotation\Lazy;
use TYPO3\CMS\Core\Core\Environment;


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
