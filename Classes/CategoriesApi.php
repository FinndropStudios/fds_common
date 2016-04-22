<?php
namespace Finndrop\FdsCommon;

/**
 * CategoryApi
 */
class CategoryApi {

    public static function getCategories($categoryUids, $recursive = FALSE) {
        $categories = array();
        foreach (explode(',', $categoryUids) as $baseCategory) {
            $category = static::getCategory($baseCategory);
            if ($recursive) {
                $category['children'] = static::getChildCategories($baseCategory);
            }
            $categories[] = $category;
        }
        return $categories;
    }

    public static function expandCategoryList($categoryUids) {
        $keys = array_keys(static::getChildCategories($categoryUids));
        $keys[] = $categoryUids;
        return implode(',', $keys);
    }

    public static function getChildCategories($parentUids, $children = array()) {
        $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            '*',
            'sys_category',
            'parent IN (' . $parentUids . ')' .
            $GLOBALS['TSFE']->cObj->enableFields('sys_category'),
            '',
            '',
            '',
            'uid'
        );
        $children = array_replace($children, (array) $rows);
        if (count($rows) > 0) {
            $children = static::getChildCategories(implode(',', array_keys($rows)), $children);
        }
        return $children;
    }

    public static function getCategory($uid) {
        $row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
            '*',
            'sys_category',
            'uid IN (' . $uid . ')' .
            $GLOBALS['TSFE']->cObj->enableFields('sys_category')
        );
        return $row;
    }

    public static function getRelatedCategories($uids, $tablenames = 'tt_content', $parent = 0, $field = 'categories') {
        return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'sys_category.*',
            'sys_category, sys_category_record_mm',
            '
				sys_category.uid = sys_category_record_mm.uid_local
				AND fieldname = "' . $field . '"
				AND tablenames = "' . $tablenames . '"
				AND parent = "' . $parent . '"
				AND uid_foreign IN (' . $uids . ') ' .
            $GLOBALS['TSFE']->cObj->enableFields('sys_category')
        );
    }

    public function getItemsByCategories($categories, $tableName, $field = NULL, $limit = 10, $offset = 0) {
        if (is_array($categories)) {
            foreach ($categories as $key => $category) {
                if (is_array($category)) {
                    $categories[$key] = $category['uid'];
                }
            }
            $categories = implode(',', $categories);
        }
        $query = $tableName . '.uid = sys_category_record_mm.uid_foreign
		AND tablenames = "' . $tableName . '"
		AND uid_local IN (' . $categories . ') ' .
            $GLOBALS['TSFE']->cObj->enableFields($tableName);

        if ($field !== NULL) {
            $query .= ' AND fieldname = "' . $field . '"';
        }

        return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            $tableName . '.*',
            $tableName . ', sys_category_record_mm',
            $query,
            $tableName . '.uid',
            '',
            $offset . ',' . $limit
        );
    }

    public static function permutate() {
        $groups = array();
        foreach (func_get_args() as $argument) {
            if (is_string($argument)) {
                $argument = explode(',', $argument);
            }
            $groups[] = $argument;
        }

        $permutations = [];
        $iteration = 0;

        while (1) {
            $num = $iteration++;
            $pick = array();

            foreach($groups as $group) {
                $r = $num % count($group);
                $num = ($num - $r) / count($group);
                $pick[] = $group[$r];
            }

            if ($num > 0) break;

            $permutations[] = $pick;
        }

        $categories = array();
        foreach ($permutations as $key => $value) {
            $categories[] = '(' . implode(' AND ', $value) . ')';
        }
        return implode(' OR ', $categories);
    }

}
