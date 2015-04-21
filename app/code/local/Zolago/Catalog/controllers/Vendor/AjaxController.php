<?php

class Zolago_Catalog_Vendor_AjaxController
    extends Zolago_Catalog_Controller_Ajax {



    protected function _getVendorId() {
        $vendor = $this->_getSession()->getVendor();

        if ($vendor) {
            $vendorId = $vendor->getId();
        } else {
            $vendorId = '0';
        }
        return $vendorId;
    }



    public function mapByNameAction()
    {
        $data = $this->getRequest()->getPost('data', array());
        //var_export($data);
        $content = array();
        if (empty($data)) {
            $content['status'] = 0;
            $content['message'] = array('count' => 0,'message'=>Mage::helper('zolagocatalog')->__('Nothing to map'));
        }
        //var_export($content);
        $skuvS = array();

        foreach ($data as $imageFile) {
            $skuvS[] = trim(explode('.', $imageFile)[0]);
        }
//        var_export($skuvS);
        /* @var $mapper    Zolago_Catalog_Model_Mapper  */
        $mapper = $this->_prepareMapper($skuvS);
        $response = $mapper->mapByName($data);
        $content['status'] = 1;
        $content['message'] = array(
            'count' => $response['count'],
            'message'=> $response['message'],
            'pid' => $response['pid']
        );
        //var_export($content);

        //$result = $this->_formatSuccessContentForResponse($content);
        $this->_setSuccessResponse($content);
    }

    public function makeRedirectAction(){
        $pidList = $this->getRequest()->getPost('data', array());
        //var_export($pidList);
        $this->_getRedirectPath($pidList);
    }


    protected function _getPath() {
        $extendedPath = $this->_getVendorId();
        $path = 'var'.DIRECTORY_SEPARATOR.'plupload'.DIRECTORY_SEPARATOR.$extendedPath;
        return $path;
    }

    protected function _prepareMapper($skuvS = array()) {
        //var_export($this->_getVendorId());
        /* @var $mapper  Zolago_Catalog_Model_Mapper */
        $mapper = Mage::getModel('zolagocatalog/mapper');
        $mapper->setPath($this->_getPath());
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');
        $collection->addAttributeToFilter("udropship_vendor", $this->_getVendorId());
        if(!empty($skuvS)) {
            $collection->addAttributeToFilter("skuv", array('in' => $skuvS));
        }
        $collection->addAttributeToSelect(Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute'));
        $collection->addAttributeToSelect('name');

        $mapper->setCollection($collection);
        return $mapper;
    }

    protected function _getRedirectPath($pidList) {
        $extends = '';
        if ($pidList) {
            $extends = '/filter/'.
                base64_encode('massaction=1').
                '/internal_image/'.implode(',',$pidList).'/';

        }
        echo Mage::getUrl("udprod/vendor_image/".$extends);
    }
}



