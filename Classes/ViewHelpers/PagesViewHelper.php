<?php
namespace Finndrop\FdsCommon\ViewHelpers;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Category\Collection\CategoryCollection;
use TYPO3\CMS\Extbase\Service\FlexFormService;
use Famelo\FameloCommon\CategoryApi;

/**
 */
class PagesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @var \TYPO3\CMS\Extbase\Service\FlexFormService
     * @inject
     */
    protected $flexFormService;

    /**
     *
     * @param integer $pageUid
     * @param string $as
     * @return string Rendered tag
     */
    public function render($pageUid, $as = 'page') {
        $pageUid = str_replace("pages_", "", $pageUid);
        $row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
            '*',
            'tt_content',
            'pid = ' . intval($pageUid) . $GLOBALS['TSFE']->cObj->enableFields('tt_content')
        );
        if ($GLOBALS['TSFE']->sys_language_uid > 0) {
            $row = $GLOBALS['TSFE']->sys_page->getRecordOverlay(
                'tt_content',
                $row,
                $GLOBALS['TSFE']->sys_language_uid
            );
        }
        $row['flexform'] = $this->flexFormService->convertFlexFormContentToArray($row['pi_flexform']);

        if ($this->templateVariableContainer->exists($as)) {
            $backup = $this->templateVariableContainer->get($as);
            $this->templateVariableContainer->remove($as);
        }
        $this->templateVariableContainer->add($as, $row);
        $output = $this->renderChildren();
        $this->templateVariableContainer->remove($as);
        if (isset($backup)) {
            $this->templateVariableContainer->add($as, $backup);
        }
        return $output;
    }

}
