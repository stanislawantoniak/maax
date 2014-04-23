<?php
class Zolago_Po_Model_Po_Item extends Unirgy_DropshipPo_Model_Po_Item
{
   public function getPriceInclTax() {
	   return $this->getPrice() * (1+($this->getOrderItem()->getTaxPercent()/100));
   }

}
