<?php
namespace Finndrop\FdsCommon\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class CopyrightViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Arguments initialisation
     */
    public function initializeArguments() {
        $this->registerArgument('owner', 'string', 'copyright owner', NULL, FALSE);
    }

    /**
     * @return string
     */
    public function render()  {

        date_default_timezone_set('Europe/Berlin');
        $timestamp = time();
        $year = date('Y', $timestamp);

        return '&copy; ' . $year . ' ' . $this->arguments['owner'];
    }

}