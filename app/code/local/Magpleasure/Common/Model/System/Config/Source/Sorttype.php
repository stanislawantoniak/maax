<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

/**
 * Abstract Option Model
 */
class Magpleasure_Common_Model_System_Config_Source_Sorttype
    extends Magpleasure_Common_Model_System_Config_Source_Abstract
{
    public function toArray()
    {
        return array(
            Varien_Db_Select::SQL_ASC => $this->_commonHelper()->__("Ascending"),
            Varien_Db_Select::SQL_DESC => $this->_commonHelper()->__("Descending"),
        );
    }

}