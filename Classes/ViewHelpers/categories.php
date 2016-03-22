<?php
namespace Finndrop\fdsCommon\ViewHelpers;
​
class CategoriesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     *
     * @param string $pageUid
     * @param string $categroyUids
     * @param string $as
     * @return string Rendered tag
     */
    public function render($pageUid = NULL, $categroyUids = NULL, $as = 'categories')
    {
        if($pageUid !== Null){
            $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                'sys_category.*',
                'sys_category, sys_category_record_mm',
                '
				sys_category.uid = sys_category_record_mm.uid_local
				AND fieldname = "categories"
        		AND tablenames = "pages"
				AND uid_foreign = ' . $pageUid . ' ' .
                $GLOBALS['TSFE']->cObj->enableFields('sys_category')
            );
            ​
        }
        ​
        if($categroyUids !== Null){
            $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                '*',
                'sys_category',
                'uid IN (' . $categroyUids . ')' .
                $GLOBALS['TSFE']->cObj->enableFields('sys_category'),
                'sys_category.sorting DESC'
            );
        }
        ​
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
