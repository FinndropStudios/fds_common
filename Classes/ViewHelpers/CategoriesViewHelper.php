<?php
namespace Finndrop\FdsCommon\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Category\Collection\CategoryCollection;

/**
 * Get all sys_categories of any page
 */
class CategoriesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Arguments initialisation
     */
    public function initializeArguments() {
        $this->registerArgument('uid', 'string', NULL, TRUE);
        $this->registerArgument('field', 'string', NULL, FALSE);
        $this->registerArgument('as', 'string', 'categories', FALSE);
        $this->registerArgument('includeTargetPage', 'boolean', FALSE, FALSE);
    }

    /**
     * @return string Rendered tag
     */
    public function render() {
        if ($this->arguments['uid'] !== NULL) {
            $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'sys_category', 'uid IN (' . $this->arguments['uid'] . ')');
        } else {
            $record = $this->templateVariableContainer->get('record');
            $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                'sys_category.*',
                'sys_category, sys_category_record_mm',
                '
					sys_category.uid = sys_category_record_mm.uid_local
					AND fieldname = "' . $this->arguments['field'] . '"
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

        if ($this->arguments['includeTargetPage'] === TRUE) {
            foreach ($rows as $key => $row) {
                $rows[$key]['targetPage'] = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'pages', 'tx_template_related_category = ' . $row['uid'] . $GLOBALS['TSFE']->cObj->enableFields('pages'));
                if (!is_array($rows[$key]['targetPage'])) {
                    unset($rows[$key]);
                }
            }
        }

        if ($this->templateVariableContainer->exists($this->arguments['as'])) {
            $backup = $this->templateVariableContainer->get($this->arguments['as']);
            $this->templateVariableContainer->remove($this->arguments['as']);
        }

        $this->templateVariableContainer->add($this->arguments['as'], $rows);
        $output = $this->renderChildren();
        $this->templateVariableContainer->remove($this->arguments['as']);

        if (isset($backup)) {
            $this->templateVariableContainer->add($this->arguments['as'], $backup);
        }

        return $output;
    }
}
