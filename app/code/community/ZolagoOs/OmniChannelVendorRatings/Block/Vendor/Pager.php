<?php
/**
  
 */

class ZolagoOs_OmniChannelVendorRatings_Block_Vendor_Pager extends Mage_Page_Block_Html_Pager
{
    protected $_availableLimit   = array(5=>5,10=>10,20=>20,50=>50,100=>100);
    protected $_dispersion       = 3;
    protected $_displayPages     = 10;
    protected $_showPerPage      = true;
    protected $_pageVarName    = 'urv_p';
    protected $_limitVarName   = 'urv_limit';
    protected $_orderVarName     = 'urv_order';
    protected $_directionVarName = 'urv_dir';
    protected $_direction        = 'desc';
    protected $_orderField       = 'created_at';
    protected $_availableOrder   = array(
        'created_at' => 'Date',
        'helpfulness_pcnt' => 'Most Helpful'
    );

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('unirgy/ratings/vendor/pager.phtml');
    }

    public function isOrderCurrent($order)
    {
        return ($order == $this->getCurrentOrder());
    }
    public function getDirectionVarName()
    {
        return $this->_directionVarName;
    }
    public function getOrderVarName()
    {
        return $this->_orderVarName;
    }
    public function getCurrentDirection()
    {
        $dir = $this->_getData('_current_grid_direction');
        if ($dir) {
            return $dir;
        }
        $directions = array('asc', 'desc');
        $dir = strtolower($this->getRequest()->getParam($this->getDirectionVarName()));
        if (!$dir || !in_array($dir, $directions)) {
            $dir = $this->_direction;
        }
        $this->setData('_current_grid_direction', $dir);
        return $dir;
    }
    public function getAvailableOrders()
    {
        return $this->_availableOrder;
    }
    public function setAvailableOrders($orders)
    {
        $this->_availableOrder = $orders;
        return $this;
    }
    public function addOrderToAvailableOrders($order, $value)
    {
        $this->_availableOrder[$order] = $value;
        return $this;
    }
    public function getCurrentOrder()
    {
        $order = $this->_getData('_current_grid_order');
        if ($order) {
            return $order;
        }
        $orders = $this->getAvailableOrders();
        $defaultOrder = $this->_orderField;
        if (!isset($orders[$defaultOrder])) {
            $keys = array_keys($orders);
            $defaultOrder = $keys[0];
        }
        $order = $this->getRequest()->getParam($this->getOrderVarName());
        if (!$order || !isset($orders[$order])) {
            $order = $defaultOrder;
        }
        $this->setData('_current_grid_order', $order);
        return $order;
    }
    public function getOrderUrl($order, $direction)
    {
        if (is_null($order)) {
            $order = $this->getCurrentOrder() ? $this->getCurrentOrder() : $this->_availableOrder[0];
        }
        return $this->getPagerUrl(array(
            $this->getOrderVarName()=>$order,
            $this->getDirectionVarName()=>$direction,
            $this->getPageVarName() => null
        ));
    }
    public function getVendor()
    {
        $vId = $this->getRequest()->getParam('id');
        $vId = $vId ? $vId : Mage::helper('umicrosite')->getCurrentVendor()->getId();
        return Mage::helper('udropship')->getVendor($vId);
    }
    public function getPagerUrl($params=array())
    {
        $urlParams = array();
        $urlParams['id']  = $this->getVendor()->getId();
        $urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        //$urlParams['_use_rewrite']   = true;
        $urlParams['_query']    = $params;
        return $this->getUrl('udratings/vendor/reviewListJson', $urlParams);
    }
    public function setCollection($collection)
    {
        $this->_collection = $collection;
        $this->_collection->setCurPage($this->getCurrentPage());
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        if ($this->getCurrentOrder()) {
            $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
            if ($this->getCurrentOrder()=='helpfulness_pcnt') {
                $this->_collection->setOrder('helpfulness_yes', $this->getCurrentDirection());
            }
        }
        $this->_setFrameInitialized(false);
        return $this;
    }
}