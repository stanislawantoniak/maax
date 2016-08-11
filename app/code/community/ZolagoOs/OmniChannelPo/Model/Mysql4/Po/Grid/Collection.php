<?php
/**
  
 */

class ZolagoOs_OmniChannelPo_Model_Mysql4_Po_Grid_Collection extends ZolagoOs_OmniChannelPo_Model_Mysql4_Po_Collection
{
    protected $_eventPrefix = 'udpo_po_grid_collection';
    protected $_eventObject = 'po_grid_collection';

    protected function _construct()
    {
        parent::_construct();
        $this->setMainTable('udpo/po_grid');
    }


}
