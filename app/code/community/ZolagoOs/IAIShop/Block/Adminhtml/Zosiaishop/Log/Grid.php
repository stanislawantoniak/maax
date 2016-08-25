<?php

/**
 */
class ZolagoOs_IAIShop_Block_Adminhtml_Zosiaishop_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {

        parent::__construct();
        $this->setId('zosiaishop_log_id');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $model = Mage::getModel('zosiaishop/log');
        $collection = $model->getCollection();
        $collection->getSelect()->join(
            array("vendors" => $model->getResource()->getTable('udropship/vendor')), //$name
            "main_table.vendor_id=vendors.vendor_id", //$cond
            array("vendor_name" => "vendor_name")//$cols = '*'
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Zolago_Payment_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper("zosiaishop");
    }

    protected function _prepareColumns()
    {
        $hlp = $this->_getHelper();

        $this->addColumn('zosiaishop_log_id',
            array(
                'header' => $hlp->__('ID'),
                'width' => '50px',
                'index' => 'id'
            )
        );

        $this->addColumn('vendor_id',
            array(
                'header' => $hlp->__('Vendor'),
                'width' => '100px',
                "type" => "options",
                'index' => 'vendor_id',
                "options" => Mage::getSingleton('zolagodropship/source')->setPath('allvendorswithdisabled')->toOptionHash(),
                'filter_index' => 'vendor_name',
                'filter_condition_callback' => array($this, '_sortByVendorName'),
            )
        );

	    $this->addColumn('log',
		    array(
			    'header' => $hlp->__('Log'),
			    'index' => 'log'
		    )
	    );

        $this->addColumn('created_at',
            array(
                'header' => $hlp->__('Created at'),
                'width' => '50px',
                'index' => 'created_at',
                "type" => "datetime"
            )
        );


        return parent::_prepareColumns();
    }

    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    function _sortByVendorName($collection, $column)
    {
        $direction = strtoupper($column->getDir());

        if ($direction) {
            $collection->getSelect()->order($column->getFilterIndex(), $direction);
        }
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $index = $column->getIndex();
        $collection->getSelect()->where("main_table.{$index} = ?", $value);

        return $this;
    }

}
