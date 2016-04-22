<?php
namespace Finndrop\FdsCommon\Util;

use Finndrop\FdsCommon\CategoryApi;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Category\Collection\CategoryCollection;

class BodyTagHelper
{
    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    public $cObj;

    /**
     * Generate body tag with categories as classes
     *
     * @param string $content
     * @param array $conf
     * @return string
     */
    public function buildBodyTag($content, $conf)
    {
        $category = new CategoryApi();
        $categories = $category->getRelatedCategories($GLOBALS['TSFE']->id, 'pages', $conf['parentCategory']);

        $bodyClass = '';
        if (count($categories) == 1) {
            foreach ($categories as $key => $row) {
                $bodyClass .= strtolower($row['title']);
            }
        } else {
            $bodyClass .= strtolower($conf['fallbackClass']);
        }

        return '<body class="' . $bodyClass . '" id="page-' . $GLOBALS['TSFE']->id . '">';
    }
}