<?php

/**
 * Bundle Stock Status Indexer Resource Model
 */
class ZolagoOs_OmniChannel_Model_StockIndexer_EE11300_Bundle extends Mage_Bundle_Model_Resource_Indexer_Stock
{
    /**
     * Clean temporary bundle options stock data
     *
     * @return Mage_Bundle_Model_Resource_Indexer_Stock
     */
    protected function _cleanBundleOptionStockData()
    {
        if ($this->_getWriteAdapter()->getTransactionLevel() == 0) {
            $this->_getWriteAdapter()->truncateTable($this->_getBundleOptionTable());
        } else {
            $this->_getWriteAdapter()->delete($this->_getBundleOptionTable());
        }
        return $this;
    }
}
