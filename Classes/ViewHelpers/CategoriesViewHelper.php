<?php
namespace Finndrop\FdsCommon\ViewHelpers;

use Finndrop\FdsCommon\CategoryApi;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Category\Collection\CategoryCollection;

class CategoriesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Arguments initialisation
     */
    public function initializeArguments() {
        $this->registerArgument('puid', 'string', 'page ID', FALSE, $GLOBALS['TSFE']->id);
        $this->registerArgument('field', 'string', 'field', FALSE, 'pages');
        $this->registerArgument('alias', 'string', 'alias', FALSE, 'categories');
        //$this->registerArgument('includeTargetPage', 'boolean', FALSE, FALSE);
        $this->registerArgument('translate', 'boolean', 'translate categories', FALSE, TRUE);
    }

    /**
     * @return array
     */
    public function render()  {

        $category = new CategoryApi();
        $categories = $category->getRelatedCategories($this->arguments['puid'], 'pages');

        if ($this->templateVariableContainer->exists()) {
            $backup = $this->templateVariableContainer->get($this->arguments['alias']);
            $this->templateVariableContainer->remove($this->arguments['alias']);
        }

        $this->templateVariableContainer->add($this->arguments['alias'], $categories);
        $output = $this->renderChildren();
        $this->templateVariableContainer->remove($this->arguments['alias']);

        if (isset($backup)) {
            $this->templateVariableContainer->add($this->arguments['alias'], $backup);
        }

        return $output;
    }

}