<?php
namespace Finndrop\FdsCommon\Util;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class BodyTagHelper {

    public $cObj;

    public function buildBodyTag() {

        $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'sys_category', 'uid IN (' . $GLOBALS['TSFE']->id . ')');

        $bodyClass = '';
        if (count($rows) == 1) {
            foreach ($rows as $key => $row) {
                $bodyClass .= $row . ' ';
            }
        } else {
            $bodyClass = 'neutral';
        }

        return '<body class="' . $bodyClass . '" id="page-' . $GLOBALS['TSFE']->id . '">';
    }
}