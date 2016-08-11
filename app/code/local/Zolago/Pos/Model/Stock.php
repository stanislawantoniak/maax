<?php

/**
 * Class Zolago_Pos_Model_Stock
 *
 * @method string getId()
 * @method string getProductId()
 * @method string getPosId()
 * @method string getQty()
 *
 * @method $this setProductId($value)
 * @method $this setPosId($value)
 * @method $this setQty($value)
 */
class Zolago_Pos_Model_Stock extends Mage_Core_Model_Abstract {

	protected function _construct() {
		$this->_init('zolagopos/stock');
	}

}

