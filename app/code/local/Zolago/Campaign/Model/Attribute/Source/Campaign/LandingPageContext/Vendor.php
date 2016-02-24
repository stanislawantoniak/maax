<?php

class Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext_Vendor
{

    /**
     * @return array
     */
    public function toOptionHash()
    {
        $vendors = array();

        $vendorCollection = Mage::getModel("udropship/vendor")->getCollection();
        $vendorCollection->setOrder("vendor_name", "ASC");

        foreach ($vendorCollection as $vendorCollectionItem) {
            $vendors[$vendorCollectionItem->getVendorId()] = $vendorCollectionItem->getVendorName();
        }
        return $vendors;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array("" => Mage::helper("zolagocampaign")->__("--- Select ---"));
        $optionsHash = $this->toOptionHash();
        if (!empty($optionsHash)) {
            foreach ($optionsHash as $value => $label) {
                $options[] = array('value' => $value, 'label' => $label);
            }
        }
        return $options;
    }

}