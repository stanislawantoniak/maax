<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

/**
 * URL Key generator
 */
class Magpleasure_Blog_Helper_Url_Key extends Mage_Core_Helper_Abstract
{
    public function generate($title)
    {
        $title = preg_replace('/[«»""!?,.!@£$%^&*{};:()]+/', '', strtolower($title));
        $key=preg_replace('/[^A-Za-z0-9-]+/', '-', $title);
        return $key;
    }

}