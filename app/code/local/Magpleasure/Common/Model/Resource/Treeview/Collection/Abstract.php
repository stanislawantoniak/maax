<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

/**
 * Abstract Resource Collection
 */
abstract class Magpleasure_Common_Model_Resource_Treeview_Collection_Abstract
    extends Magpleasure_Common_Model_Resource_Collection_Abstract
{
    abstract public function addRootFilter();
}