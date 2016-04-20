<?php
/**
  
 */
interface ZolagoOs_OmniChannel_Model_Label_Interface_Carrier
{
    public function requestLabel($track);

    public function refundLabel($track);

    public function collectTracking($v, $trackIds);
}
