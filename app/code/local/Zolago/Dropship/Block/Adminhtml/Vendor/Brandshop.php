<?php
/**
 * Brandshop settings grid
 */
class Zolago_Dropship_Block_Adminhtml_Vendor_Brandshop extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct()
    {
        parent::__construct();
        $this->setId('brandshopGrid');
//        $this->setDefaultSort('created_at');
    }
    public function getVendorId() {
        return $this->getRequest()->getParam('id');
    }
    protected function _prepareCollection()
    {
        $vendor = $this->getVendorId();
        $model = Mage::getModel('udropship/vendor');
        
        $collection = $model->getCollection();
        $select = $collection->getSelect();
        $adapter = $select->getAdapter();
        $name = Mage::getSingleton('core/resource')->getTableName('zolagodropship/vendor_brandshop');
        $select->joinLeft(array(
                'brandshop' => $name,
            ),	
            'main_table.vendor_id=brandshop.brandshop_id AND '.$adapter->quoteInto('brandshop.vendor_id=?',$vendor),
            array(
                'description'=>'description',
                'brandshop_can_ask' => 'can_ask',
                'brandshop_can_add_product' => 'can_add_product',
                'brandshop_index_by_google' => 'index_by_google',
                'main_vendor_id' => 'main_table.vendor_id',
            )
        );
        $this->setCollection($collection);
        
        parent::_prepareCollection();
        
        return $this;
    }
    protected function _addColumnFilterToCollection($column)
    {
        $id = $column->getId();
        $value = $column->getFilter()->getValue();
        $select = $this->getCollection()->getSelect();
        $adapter = $select->getAdapter();
        switch ($id) {
            case 'vendor_id':
                if ($value) {
                    $select->where($adapter->quoteInto('main_table.vendor_id=?',$value));
                }
                return $this;
            case  'can_ask':
                if ($value === '0') {
                    $select->where('(brandshop.can_ask is null or brandshop.can_ask = false)');
                } elseif ($value) {
                    $select->where('brandshop.can_ask=true');
                }
                return $this;
            case  'can_add_product':
                echo $value;
                if ($value === '0') {
                    $select->where('(brandshop.can_add_product is null or brandshop.can_add_product = false)');
                } elseif ($value) {
                    $select->where('brandshop.can_add_product=true');
                }
                return $this;
            case  'index_by_google':
                echo $value;
                if ($value === '0') {
                    $select->where("(brandshop.index_by_google is null or brandshop.index_by_google = '0')");
                } elseif ($value) {
                    $select->where('brandshop.index_by_google=?', $value);
                }
                return $this;
            default:;
        }
        parent::_addColumnFilterToCollection($column);
        return $this;
    }

    protected function _prepareColumns()
    {

        $this->addColumn('vendor_id', array(
            'header'        => Mage::helper('zolagodropship')->__('ID'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'vendor_id',
        ));

        $this->addColumn('brandshop_grid_vendor_name', array(
            'header'        => Mage::helper('zolagodropship')->__('Vendor name'),
            'align'         => 'left',
            'width'         => '100px',
            'index'         => 'vendor_name',
        ));

        $this->addColumn('description', array(
            'header'        => Mage::helper('zolagodropship')->__('Description on vendor page'),
            'align'         => 'left',
            'index'         => 'description',
            'type'          => 'text',
            'truncate'      => 50,
            'nl2br'         => true,
            'escape'        => true,
        ));
        $this->addColumn('can_ask', array(
            'header'        => Mage::helper('zolagodropship')->__('Customer can ask'),
            'align'         => 'center',
            'renderer' 		=> 'zolagodropship/adminhtml_vendor_brandshop_renderer',
            'filter' 		=> 'zolagodropship/adminhtml_vendor_brandshop_filter',
            'index'         => 'brandshop_can_ask',
        ));
        $this->addColumn('can_add_product', array(
            'header'        => Mage::helper('zolagodropship')->__('Can add product'),
            'align'         => 'center',
            'index'         => 'brandshop_can_add_product',
            'renderer' 		=> 'zolagodropship/adminhtml_vendor_brandshop_renderer',
            'filter' 		=> 'zolagodropship/adminhtml_vendor_brandshop_filter',
        ));
        $indexByGoogleOptions = Mage::getSingleton('zolagodropship/source')
            ->setPath('vendorindexbygoogle')
            ->toOptionHash();

        $this->addColumn('index_by_google', array(
            'header'        => Mage::helper('zolagodropship')->__('Index By Google'),
            'align'         => 'center',
            'index'         => 'brandshop_index_by_google',
            "type" => "options",
            "options" => $indexByGoogleOptions,
            'renderer' 		=> 'zolagodropship/adminhtml_vendor_brandshop_RendererIndexByGoogle'
        ));

        return parent::_prepareColumns();
    }
    
    /**
     * row url
     */
     public function getRowUrl($row) {
         return $this->getUrl('*/*/brandshopEdit', array('brandshop_id'=>$row->getId(),'vendor_id'=>$this->getVendorId()));
     }

}