<?php

class Zolago_Rma_Model_Rma_Item extends Unirgy_Rma_Model_Rma_Item
{
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
        $itemConditions = Mage::getStoreConfig('urma/general/item_conditions');

        $itemConditionsA = json_decode($itemConditions);

        $code = $id;
        if (!empty($itemConditionsA)) {
            foreach ($itemConditionsA as $idD => $dataD) {
                if ($id == $idD)
                    $code = $dataD->code;
            }
        }

        return Mage::helper('urma')->getItemConditionTitle($code);
    }
}
