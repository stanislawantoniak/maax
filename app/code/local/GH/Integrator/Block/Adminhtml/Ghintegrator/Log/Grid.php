<?php

/**
 * Class Zolago_Payment_Block_Adminhtml_Vendor_Invoice_Grid
 */
class GH_Integrator_Block_Adminhtml_Ghintegrator_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {

        parent::__construct();
        $this->setId('ghintegrator_log_id');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ghintegrator/log')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Zolago_Payment_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper("ghintegrator");
    }

    protected function _prepareColumns()
    {
        $hlp = $this->_getHelper();

        $this->addColumn('ghintegrator_log_id',
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
			    "options" => Mage::getSingleton('zolagodropship/source')->setPath('allvendorswithdisabled')->toOptionHash()
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

}
