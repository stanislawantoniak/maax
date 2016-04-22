<?php
/**
  
 */

class ZolagoOs_OmniChannel_Model_Vendor_Status
{
    public function getAllOptions()
    {
        return array(
            array('label'=>'Active', 'value'=>'A'),
            array('label'=>'Inactive', 'value'=>'I'),
        );
    }

    public function toOptionArray()
    {
        return array('A'=>'Active', 'I'=>'Inactive');
    }
}
