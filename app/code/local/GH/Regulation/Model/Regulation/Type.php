<?php

/**
 * Class GH_Regulation_Model_Regulation_Type
 * @method string getRegulationTypeId()
 * @method string getRegulationKindId()
 * @method string getName()
 */
class GH_Regulation_Model_Regulation_Type extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('ghregulation/regulation_type');
    }

}