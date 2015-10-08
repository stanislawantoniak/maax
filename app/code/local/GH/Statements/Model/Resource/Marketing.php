<?php

/**
 * Resource for refund statement
 */
class GH_Statements_Model_Resource_Marketing extends Mage_Core_Model_Resource_Db_Abstract {

    protected function _construct() {
        $this->_init('ghstatements/marketing','id');
    }

	/**
	 * $data should look like:
	 * array(
	 *      array(
	 *          'statement_id'              => $statement->getId(),
	 *          'product_id'                => $marketingCost->getProductId(),
	 *          'product_sku'               => $product->getSku(),
	 *          'product_vendor_sku'        => $product->getSkuv(),
	 *          'product_name'              => $product->getName(),
	 *          'marketing_cost_type_id'    => $marketingCost->getTypeId(),
	 *          'marketing_cost_type_name'  => $marketingCost->getTypeName(),
	 *          'date'                      => $marketingCost->getDate(),
	 *          'value'                     => $marketingCost->getBillingCost()
	 *      ),
	 *      array(
	 *          ...
	 *      )
	 * );
	 *
	 * @param array $data
	 */
	public function appendMarketings($data) {
		$writeConnection = $this->_getWriteAdapter();
		$writeConnection->insertMultiple($this->getTable('ghstatements/marketing'), $data);
	}
}