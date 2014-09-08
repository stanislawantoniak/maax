<?php
/**
 * Box for category banners
 */
class Zolago_Modago_Block_Dropshipmicrositepro_Vendor_Category_Banner extends Zolago_Modago_Block_Dropshipmicrositepro_Vendor_Banner {
    protected $boxTypes = array(
						Zolago_Banner_Model_Banner_Type::TYPE_SLIDER, 
						Zolago_Banner_Model_Banner_Type::TYPE_BOX,
						Zolago_Banner_Model_Banner_Type::TYPE_INSPIRATION,
					);

    public function getInspirations() {
        $request = $this->_prepareRequest();
        $request->setType(Zolago_Banner_Model_Banner_Type::TYPE_INSPIRATION);
        $finder = $this->getFinder();
        return $finder->request($request);
    }
}