<?php


class Zolago_Catalog_Model_Resource_Stock_Data extends Mage_Core_Model_Resource_Db_Abstract
{


    public  function _construct()
    {
        $this->_init('zolagopos/pos');
    }




    /**
     * Calculate stock on open orders
     * @param $merchant
     * @return array
     */
    public function calculateStockOpenOrders($merchant)
    {
        $adapter = $this->getReadConnection();
        $select = $adapter->select();
        $select
            ->from('udropship_po_item AS po_item',
                array(
                    'sku' => 'po_item.sku',
                    'qty' => 'SUM(po_item.qty)'
                )
            )
            ->join(
                array('products' => 'catalog_product_entity'),
                'po_item.product_id=products.entity_id',
                array()
            )
            ->join(
                array('po' => 'udropship_po'),
                'po.entity_id=po_item.parent_id',
                array()
            )
            ->where("products.type_id=?", Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->where("po.udropship_vendor=?", (int)$merchant)
            ->where('po.udropship_status NOT IN (?)',
                array(
                    Unirgy_DropshipPo_Model_Source::UDPO_STATUS_EXPORTED,
                    Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PARTIAL,
                    Unirgy_DropshipPo_Model_Source::UDPO_STATUS_SHIPPED,
                    Unirgy_DropshipPo_Model_Source::UDPO_STATUS_CANCELED,
                    Unirgy_DropshipPo_Model_Source::UDPO_STATUS_DELIVERED,
                    Unirgy_DropshipPo_Model_Source::UDPO_STATUS_EXPORTED,
                    Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_RECEIVED,
                    Unirgy_DropshipPo_Model_Source::UDPO_STATUS_STOCKPO_EXPORTED
                )
            )
            ->group('po_item.sku');

        $result = $adapter->fetchAssoc($select);

        return $result;
    }

}