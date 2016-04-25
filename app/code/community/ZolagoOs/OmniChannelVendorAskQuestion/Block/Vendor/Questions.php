<?php

class ZolagoOs_OmniChannelVendorAskQuestion_Block_Vendor_Questions extends Mage_Core_Block_Template
{
    protected $_collection;
    protected $_oldStoreId;
    protected $_unregUrlStore;

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        if (!Mage::registry('url_store')) {
            $this->_unregUrlStore = true;
            Mage::register('url_store', Mage::app()->getStore());
        }
        $this->_oldStoreId = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        if ($toolbar = $this->getLayout()->getBlock('udqa.grid.toolbar')) {
            $toolbar->setCollection($this->getQuestionsCollection());
        }

        return $this;
    }

    protected function _getUrlModelClass()
    {
        return 'core/url';
    }
    public function getUrl($route = '', $params = array())
    {
        if (!isset($params['_store']) && $this->_oldStoreId) {
            $params['_store'] = $this->_oldStoreId;
        }
        return parent::getUrl($route, $params);
    }

    protected function _afterToHtml($html)
    {
        if ($this->_unregUrlStore) {
            $this->_unregUrlStore = false;
            Mage::unregister('url_store');
        }
        Mage::app()->setCurrentStore($this->_oldStoreId);
        return parent::_afterToHtml($html);
    }

    protected function _applyRequestFilters($collection)
    {
        $r = Mage::app()->getRequest();
        $param = $r->getParam('filter_product_sku');
        if (!is_null($param) && $param!=='') {
            $collection->addFieldToFilter('product_sku', array('like'=>$param.'%'));
        }
        $param = $r->getParam('filter_product_name');
        if (!is_null($param) && $param!=='') {
            $collection->addFieldToFilter('product_name', array('like'=>'%'.$param.'%'));
        }
        $param = $r->getParam('filter_customer_name');
        if (!is_null($param) && $param!=='') {
            $collection->addFieldToFilter('customer_name', array('like'=>'%'.$param.'%'));
        }
        $param = $r->getParam('filter_replied');
        if (!is_null($param) && $param!=='') {
            if ($param) {
                $collection->addFieldToFilter('answer_text', array('gt'=>0,'field_expr'=>'LENGTH(#?)'));
            } else {
                $collection->addFieldToFilter('answer_text', array(array('null'=>1),array('eq'=>0,'field_expr'=>'LENGTH(#?)')));
            }
        }
        $param = $r->getParam('filter_question');
        if (!is_null($param) && $param!=='') {
            $collection->addFieldToFilter('question_text', array('like'=>'%'.$param.'%'));
        }
        $param = $r->getParam('filter_answer');
        if (!is_null($param) && $param!=='') {
            $collection->addFieldToFilter('answer_text', array('like'=>'%'.$param.'%'));
        }
        if (($v = $r->getParam('filter_order_id_from'))) {
            $collection->addFieldToFilter('order_increment_id', array('gteq'=>$v));
        }
        if (($v = $r->getParam('filter_order_id_to'))) {
            $collection->addFieldToFilter('order_increment_id', array('lteq'=>$v));
        }

        if (($v = $r->getParam('filter_question_date_from'))) {
            $collection->addFieldToFilter('question_date', array('gteq'=>Mage::helper('udropship')->dateLocaleToInternal($v, null, true)));
        }
        if (($v = $r->getParam('filter_question_date_to'))) {
            $_filterDate = Mage::app()->getLocale()->date();
            $_filterDate->set($v, Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
            $_filterDate->addDay(1);
            $_filterDate->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
            $collection->addFieldToFilter('question_date', array('lteq'=>$_filterDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));

        }
        $collection->addVendorFilter($this->getVendor()->getId());
        $collection->addApprovedQuestionsFilter();
        return $this;
    }

    public function getVendor()
    {
        return Mage::getSingleton('udropship/session')->getVendor();
    }

    public function getQuestionsCollection()
    {
        if (!$this->_collection) {
            $v = Mage::getSingleton('udropship/session')->getVendor();
            if (!$v || !$v->getId()) {
                return array();
            }
            $r = Mage::app()->getRequest();
            $res = Mage::getSingleton('core/resource');
            $collection = Mage::getModel('udqa/question')->getCollection();
            $collection->joinShipments()->joinProducts()->joinVendors();
            $collection->getSelect()->columns(array('is_replied' => new Zend_Db_Expr('if(LENGTH(answer_text)>0,1,0)')));

            $this->_applyRequestFilters($collection);

            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    public function getShipmentUrl($question)
    {
        return Mage::getUrl('udropship/vendor/', array('_query'=>'filter_order_id_from='.$question->getOrderIncrementId().'&filter_order_id_to='.$question->getOrderIncrementId()));
    }
    public function getProductUrl($question)
    {
        if (Mage::helper('udropship')->isModuleActive('udprod')) {
            return Mage::getUrl('udprod/vendor/products', array('_query'=>'filter_sku='.$question->getProductSku()));
        } elseif (Mage::helper('udropship')->isModuleActive('umicrosite')
            && Mage::getSingleton('udropship/session')->getVendor()->getShowProductsMenuItem()
        ) {
            $params = array();
            $hlp = Mage::getSingleton('adminhtml/url');
            if ($hlp->useSecretKey()) {
                $params[Mage_Adminhtml_Model_Url::SECRET_KEY_PARAM_NAME] = $hlp->getSecretKey();
            }
            $params['id'] = $question->getProductId();
            return $hlp->getUrl('adminhtml/catalog_product/edit', $params);
        } else {
            return Mage::getUrl('udropship/vendor/product', array('_query'=>'filter_sku='.$question->getProductSku()));
        }
    }
}
