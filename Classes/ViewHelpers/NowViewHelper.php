<?php
namespace Finndrop\FdsCommon\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class NowViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Arguments initialisation
     */
    public function initializeArguments() {
        $this->registerArgument('as', 'string', 'name', 'date', FALSE);
    }

    /**
     * @return array
     */
    public function render()  {

        date_default_timezone_set('Europe/Berlin');
        $timestamp = time();

        $date['day'] = date('d', $timestamp);
        $date['month'] = date('m', $timestamp);
        $date['year'] = date('Y', $timestamp);
        $date['hour'] = date('H', $timestamp);
        $date['minute'] = date('i', $timestamp);

        if ($this->templateVariableContainer->exists($as)) {
            $backup = $this->templateVariableContainer->get($as);
            $this->templateVariableContainer->remove($as);
        }
        $this->templateVariableContainer->add($as, $date);
        $output = $this->renderChildren();
        $this->templateVariableContainer->remove($as);
        if (isset($backup)) {
            $this->templateVariableContainer->add($as, $backup);
        }
        return $output;
    }

}