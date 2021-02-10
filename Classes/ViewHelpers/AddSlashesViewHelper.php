<?php
namespace WSR\Myttaddressmap\ViewHelpers;
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

/**
 *
 *
 * @package TYPO3
 * @subpackage myttaddressmap
 *
 */


class AddSlashesViewHelper extends AbstractViewHelper {
	
	public function initializeArguments() {
		$this->registerArgument('text', 'string', 'text for addslashes', true, 0);
	}

	/**
	* Return string with added slashes
    * @param array $arguments 
    * @param \Closure $renderChildrenClosure
    * @param RenderingContextInterface $renderingContext
    * @return string
    */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
		return addslashes($arguments['text']);
	}	 




}
?>