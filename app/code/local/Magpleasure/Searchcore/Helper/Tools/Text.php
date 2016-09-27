<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */
/** Text Transformations Helper */
class Magpleasure_Searchcore_Helper_Tools_Text
{
    /**
     * Default Helper
     *
     * @return Magpleasure_Searchcore_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('searchcore');
    }
    
    public function htmlToWords($value)
    {
        $stringsHelper = $this->_helper()->getCommon()->getStrings();

        $text = $stringsHelper->sanitize($value);
        $text = $stringsHelper->removePunctuation($text);

        return $text;
    }

}