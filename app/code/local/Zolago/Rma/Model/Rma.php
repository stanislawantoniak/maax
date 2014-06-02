<?php

class Zolago_Rma_Model_Rma extends Unirgy_Rma_Model_Rma
{
	/**
	 * @return Zolago_Po_Model_Po
	 */
	public function getPo() {
		if(!$this->hasData("po")){
			$po = Mage::getModel("zolagopo/po");
			$po->load($this->getUdpoId());
			$this->setData("po", $po);
		}
		return $this->getData("po");
	}
}
