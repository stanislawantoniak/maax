<?php

/**
 * Class Zolago_Pos_Model_Resource_Stock_Collection
 */
class Zolago_Pos_Model_Resource_Stock_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

	protected function _construct() {
		parent::_construct();
		$this->_init('zolagopos/stock');
	}

	/**
	 * @param Zolago_Pos_Model_Pos|int $pos
	 * @return $this
	 */
	public function addPosFilter($pos) {
		if ($pos instanceof Zolago_Pos_Model_Pos) {
			$pos = $pos->getId();
		}
		$this->addFieldToFilter("main_table.pos_id", $pos);
		return $this;
	}

	/**
	 * $prodQtyFilter[] = array(
	 *    "product_id" => $productId,
	 *    "qty"        => $qty
	 * );
	 *
	 * @param array $filterData
	 * @return $this
	 */
	public function addProductQtyFilter($filterData) {
		$condition = array();
		foreach ($filterData as $cond) {
			$condition[] =
				"(".
					$this->getConnection()->quoteInto("main_table.product_id = ?", $cond['product_id'])
					. ' AND ' .
					$this->getConnection()->quoteInto("main_table.qty >= ?", $cond['qty']) .
				")";
		}
		if (!empty($condition)) {
			$this->getSelect()->where(implode(" OR ", $condition));
		}
		return $this;
	}
}
