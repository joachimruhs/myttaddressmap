<?php
namespace WSR\Myttaddressmap\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/***************************************************************
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
 *
 *
 * @package TYPO3
 * @subpackage myttaddressmap
 *
 */


class Nl2brViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {
	
	public function initializeArguments() {
		$this->registerArgument('text', 'string', 'text for nl2br', true, 0);
		$this->registerArgument('htmlSpecialChars', 'integer', 'flag for htmlspecialchars', true, 0);
	}

	/**
	* Return string with nl2br
	*
	* Return string with added <br />
    * @param array $arguments 
    * @param \Closure $renderChildrenClosure
    * @param RenderingContextInterface $renderingContext
    * @return string
    */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		if ($arguments['htmlSpecialChars']) {
			return str_replace(array("\r\n", "\r", "\n"), '<br />', htmlspecialchars($arguments['text'], ENT_QUOTES));
		} else {
			return str_replace(array("\r\n", "\r", "\n"), '<br />', $arguments['text']);
		}
	}	 




}
?>