<?php
namespace Finndrop\fdsCommon\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Category\Collection\CategoryCollection;

/**
 */
class CategoriesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     *
     * @param string $uids
     * @param string $field
     * @param string $as
     * @param boolean $includeTargetPage
     * @return string Rendered tag
     */
    public function render($uid = NULL, $field = NULL, $as = 'categories', $includeTargetPage = FALSE) {
        if ($uid !== NULL) {
            $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'sys_category', 'uid IN (' . $uid . ')');
        } else {
            $record = $this->templateVariableContainer->get('record');
            $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                'sys_category.*',
                'sys_category, sys_category_record_mm',
                '
					sys_category.uid = sys_category_record_mm.uid_local
					AND fieldname = "' . $field . '"
					AND tablenames = "tt_content"
					AND uid_foreign = ' . $record['uid'] . '
					AND hidden = 0 AND deleted = 0'
            );
        }

        if ($GLOBALS['TSFE']->sys_language_uid > 0) {
            foreach ($rows as $key => $row) {
                $rows[$key] = $GLOBALS['TSFE']->sys_page->getRecordOverlay(
                    'sys_category',
                    $row,
                    $GLOBALS['TSFE']->sys_language_uid
                );
            }
        }

        if ($includeTargetPage === TRUE) {
            foreach ($rows as $key => $row) {
                $rows[$key]['targetPage'] = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'pages', 'tx_template_related_category = ' . $row['uid'] . $GLOBALS['TSFE']->cObj->enableFields('pages'));
                if (!is_array($rows[$key]['targetPage'])) {
                    unset($rows[$key]);
                }
            }
        }

        if ($this->templateVariableContainer->exists($as)) {
            $backup = $this->templateVariableContainer->get($as);
            $this->templateVariableContainer->remove($as);
        }
        $this->templateVariableContainer->add($as, $rows);
        $output = $this->renderChildren();
        $this->templateVariableContainer->remove($as);
        if (isset($backup)) {
            $this->templateVariableContainer->add($as, $backup);
        }
        return $output;
    }

}
