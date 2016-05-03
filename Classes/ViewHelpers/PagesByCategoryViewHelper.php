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
use Famelo\ContentTargeting\Core;
use Famelo\FameloCommon\CategoryApi;

/**
 */
class PagesByCategoryViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @var \TYPO3\CMS\Extbase\Service\FlexFormService
     * @inject
     */
    protected $flexFormService;

    /**
     *
     * @param mixed $categories
     * @param mixed $baseCategory
     * @param string $as
     * @param mixed $uid
     * @return string Rendered tag
     */
    public function render($categories = NULL, $baseCategory = NULL, $as = 'pages', $uid = NULL) {
        if ($categories !== NULL) {
            foreach ($categories as $key => $value) {
                $categories[$key] = $value['uid'];
            }
            $categories = CategoryApi::expandCategoryList(implode(',', $categories));
            if ($baseCategory !== NULL) {
                $categories = CategoryApi::permutate($categories, $baseCategory);
            }
            $pages = Core::getTableRows($categories, 'pages', '1=1', 5);
        }

        if ($uid !== NULL) {
            $where = 'uid IN (' . str_replace('pages_', '', $uid) . ')';
            if ('FE' === TYPO3_MODE) {
                $where.= $GLOBALS['TSFE']->cObj->enableFields('pages');
            }
            $pages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                '*',
                'pages',
                $where
            );
        }

        if ($GLOBALS['TSFE']->sys_language_uid > 0) {
            $pageUids = array();
            foreach ($pages as $key => $page) {
                $pageUids[] = $page['uid'];
            }
            $pageOverlays = (array) $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                '*',
                'pages_language_overlay',
                '
				sys_language_uid = ' . $GLOBALS['TSFE']->sys_language_uid . '
				AND pid IN (' . implode(',', $pageUids) . ')'.
                $GLOBALS['TSFE']->cObj->enableFields('pages_language_overlay'),
                NULL,
                NULL,
                NULL,
                'pid'
            );
        }

        foreach ($pages as $key => $row) {
            $pages[$key]['flexform'] = $this->flexFormService->convertFlexFormContentToArray($row['tx_fed_page_flexform']);

            if (isset($pageOverlays[$row['uid']])) {
                unset($pageOverlays[$row['uid']]['uid']);
                unset($pageOverlays[$row['uid']]['pid']);
                $pageOverlays[$row['uid']]['flexform'] = $this->flexFormService->convertFlexFormContentToArray($pageOverlays[$row['uid']]['tx_fed_page_flexform']);;
                $pages[$key] = array_replace($pages[$key], $pageOverlays[$row['uid']]);
            }
        }

        if ($this->templateVariableContainer->exists($as)) {
            $backup = $this->templateVariableContainer->get($as);
            $this->templateVariableContainer->remove($as);
        }
        $this->templateVariableContainer->add($as, $pages);
        $output = $this->renderChildren();
        $this->templateVariableContainer->remove($as);
        if (isset($backup)) {
            $this->templateVariableContainer->add($as, $backup);
        }
        return $output;
    }

}
