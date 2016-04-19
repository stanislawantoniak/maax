<?php
/**
  
 */

class ZolagoOs_OmniChannelVendorAskQuestion_Model_Mysql4_Question_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_eventPrefix = 'udqa_question_collection';
    protected $_eventObject = 'question_collection';

    protected $_map = array('fields' => array(
        'shipment_name' => 'shipment_grid.shipping_name',
        'order_increment_id'=>'shipment_grid.order_increment_id',
        'order_id'=>'shipment_grid.order_id',
        'shipment_increment_id'=>'shipment_grid.increment_id',
        'shipment_id'=>'shipment_grid.entity_id',
        'product_id' => 'product.entity_id',
        'product_sku' => 'product.sku'
    ));

    protected function _construct()
    {
        $this->_init('udqa/question');
    }

    public function setDateOrder($dir='DESC')
    {
        $this->setOrder('answer_date', $dir);
        $this->setOrder('question_date', $dir);
        return $this;
    }
    public function addCustomerFilter($customer)
    {
        $this->addFieldToFilter('main_table.customer_id', is_scalar($customer) ? $customer : $customer->getId());
        return $this;
    }
    public function addVendorFilter($vendor)
    {
        $this->addFieldToFilter('main_table.vendor_id', is_scalar($vendor) ? $vendor : $vendor->getId());
        return $this;
    }
    public function addApprovedQuestionsFilter()
    {
        $this->getSelect()->where('main_table.question_status=1');
        return $this;
    }
    public function addApprovedAnswersFilter()
    {
        $this->getSelect()->where('main_table.answer_status=1');
        return $this;
    }

    public function addPublicProductFilter($pId)
    {
        if ($pId instanceof Varien_Object) {
            $pId = $pId->getProductId() ? $pId->getProductId() : $pId->getEntityId();
        }
        if (!is_array($pId)) {
            $pId = array($pId);
        }
        $this->addFieldToFilter('main_table.product_id',array('in'=>$pId));
        $this->addFieldToFilter('main_table.visibility',ZolagoOs_OmniChannelVendorAskQuestion_Model_Source::UDQA_VISIBILITY_PUBLIC);
        $this->addApprovedQuestionsFilter();
        $this->addApprovedAnswersFilter();
        return $this;
    }

    public function addPendingStatusFilter()
    {
        $this->getSelect()->where('main_table.question_status=0 OR main_table.answer_status=0');
        return $this;
    }
    public function addContextFilter($value)
    {
        $this->joinProducts();
        $this->joinShipments();
        $filter = array('like'=>$value.'%');
        $columns = array(
            'shipment_grid.increment_id',
            'shipment_grid.order_increment_id',
            'product.sku',
            'product.entity_id'
        );
        $filters = array($filter,$filter,$filter,$filter);
        foreach ($this->getSelect()->getPart(Zend_Db_Select::COLUMNS) as $_selCol) {
            if (@$_selCol[2]=='product_name') {
                $columns[] = ''.$_selCol[1];
                $filters[] = $filter;
                break;
            }
        }
        $this->addFieldToFilter($columns, $filters);
        return $this;
    }
    public function joinShipments()
    {
        if ($this->getFlag('joinShipments')) return $this;
        $this->getSelect()
            ->joinLeft(
                array('shipment_grid' => $this->getTable('sales/shipment_grid')),
                'main_table.shipment_id = shipment_grid.entity_id',
                array(
                    'shipment_name' => 'shipment_grid.shipping_name',
                    'order_increment_id'=>'shipment_grid.order_increment_id',
                    'order_id'=>'shipment_grid.order_id',
                    'shipment_increment_id'=>'shipment_grid.increment_id',
                    'shipment_id'=>'shipment_grid.entity_id'
                )
            )
        ;
        $this->setFlag('joinShipments', 1);
        return $this;
    }
    public function joinProducts()
    {
        if ($this->getFlag('joinProducts')) return $this;
        $this->getSelect()
            ->joinLeft(
                array('product' => $this->getTable('catalog/product')),
                'main_table.product_id = product.entity_id',
                array(
                    'product_id' => 'product.entity_id',
                    'product_sku' => 'product.sku'
                )
            )
        ;
        $this->addProductAttributeToSelect(array('product_name'=>'name'));
        foreach ($this->getSelect()->getPart(Zend_Db_Select::COLUMNS) as $_selCol) {
            if (@$_selCol[2]=='product_name') {
                $this->_map['fields']['product_name'] = $_selCol[1];
                break;
            }
        }
        $this->setFlag('joinProducts', 1);
        return $this;
    }
    public function joinVendors()
    {
        if ($this->getFlag('joinVendors')) return $this;
        $this->getSelect()
            ->joinLeft(
            array('vendor' => $this->getTable('udropship/vendor')),
            'main_table.vendor_id = vendor.vendor_id',
            array(
                'vendor_name'  => 'vendor.vendor_name',
                'vendor_email' => 'vendor.email',
                'vendor_id'    => 'vendor.vendor_id'
            )
        )
        ;
        $this->setFlag('joinVendors', 1);
        return $this;
    }
    public function addProductAttributeToSelect($attrCode)
    {
        Mage::helper('udqa')->addProductAttributeToSelect($this->getSelect(), $attrCode, 'main_table.product_id');
        return $this;
    }
    public  function setEmptyFilter()
    {
        $this->getSelect()->where('false');
        return $this;
    }
}