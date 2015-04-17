<?php
class Zolago_Solrsearch_Block_Category_Vendor_Header extends Zolago_Solrsearch_Block_Category_View {


    public function getMobileVendorHeader()
    {
        if($this->isContentMode()){
            return $this->getMobileVendorHeaderPanel();
        } else {
            return '';
        }

    }

    public function getDesktopVendorHeader()
    {
        if($this->isContentMode()){
            return $this->getDesktopVendorHeaderPanel();
        } else {
            return '';
        }

    }

}
