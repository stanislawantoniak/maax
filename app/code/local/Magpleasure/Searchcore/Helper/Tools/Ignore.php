<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */
/** Ignore Words Dictionary */
class Magpleasure_Searchcore_Helper_Tools_Ignore
{
    public function isInIgnore($word, $locale = "en_US")
    {
        ///TODO Add more locales in future
        $en_Us = array('the','of','to','and','a','in','is','it');
        return in_array($word, $en_Us);
    }
}