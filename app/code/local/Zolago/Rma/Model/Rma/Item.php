<?php

class Zolago_Rma_Model_Rma_Item extends ZolagoOs_Rma_Model_Rma_Item
{

	/**
	 * @return Mage_Catalog_Helper_Image
	 */
	public function getProductThumbHelper() {
		$thumb = Mage::getResourceModel("catalog/product")->getAttributeRawValue(
				$this->getProductId(),
				'thumbnail',
				$this->getRma()->getStoreId()
		);
		$product = Mage::getModel("catalog/product")->
			setId($this->getProductId())->
			setThumbnail($thumb);
		
		return Mage::helper("catalog/image")->init($product, 'thumbnail');
	}
	/**
	 * @return Zolago_Po_Model_Po_Item
	 */
	public function getPoItem() {
		if(!$this->getData("po_item")){
			$poItem = Mage::getModel("zolagopo/po_item")->load($this->getUdpoItemId());
			$this->setData("po_item", $poItem);
		}
		return $this->getData('po_item');
	}
	
	public function getFinalSku() {
	   if($this->getData('vendor_simple_sku')){
		   return $this->getData('vendor_simple_sku');
	   }
	   return $this->getData('vendor_sku');
	}

    public function getItemConditionName()
    {
        $id = $this->getItemCondition();

        $itemConditionsA = Mage::helper('urma')->getItemConditionTitles();
        $code = $id;
        if (!empty($itemConditionsA)) {
            foreach ($itemConditionsA as $idD => $dataD) {
                if ($id == $idD) {
                    $code = $dataD;
					break;
                }
            }
        }
        return Mage::helper('urma')->getItemConditionTitle($code);
    }
}
