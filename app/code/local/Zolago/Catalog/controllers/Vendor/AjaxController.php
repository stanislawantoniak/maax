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


    protected function _getSkuvSFromFileArray($data)
    {
        $skuvS = array();
        if (!empty($data)) {
            foreach ($data as $imageFile) {
                $skuvS[trim(explode('.', $imageFile)[0])][] = $imageFile;
            }
        }
        return $skuvS;

    }

    public function mapByNameAction()
    {
        $data = $this->getRequest()->getPost('data', array());

        $content = array();
        if (empty($data)) {
            $content['status'] = 0;
            $content['message'] = array('count' => 0,'message'=>Mage::helper('zolagocatalog')->__('Nothing to map'));
        }

        $skuvS = array();

        foreach ($data as $imageFile) {
            $skuvS[] = trim(explode('.', $imageFile)[0]);
        }

        /* @var $mapper    Zolago_Catalog_Model_Mapper  */
        $mapper = $this->_prepareMapper($skuvS);
        $response = $mapper->mapByName($data);
        if($response['count'] <= 0){
            $content['status'] = 0;
            $content['message'] = array(
                'count' => 0,
                'message'=> $response['message']
            );

        } else {
            $content['status'] = 1;
            $content['message'] = array(
                'count' => $response['count'],
                'message'=> $response['message'],
                'pid' => $response['pid']
            );
        }
        //$result = $this->_formatSuccessContentForResponse($content);
        $this->_setSuccessResponse($content);
    }


    public function mapByCSVAction()
    {
        $content = array();
        $content['status'] = 0;
        $content['message'] = "";

        $limit = $this->getRequest()->getPost('csv_file_limit', 0);
        $offset = $this->getRequest()->getPost('csv_file_offset', 0);

        $pidList = array();
        if (empty($_FILES['csv_file'])) {
            $content['message'] = Mage::helper('zolagocatalog')->__('Cant upload file');
            $this->_setSuccessResponse($content);
        }

        $file = file($_FILES['csv_file']['tmp_name']);
        if (!$file) {
            $content['message'] = Mage::helper('zolagocatalog')->__('Cant read file');
            $this->_setSuccessResponse($content);
        }
        $header = $file[0];

        unset($file[0]);

        //var_export($header);
        /* @var $parser Zolago_Image_Model_File_Parser */
        $parser = Mage::getModel('zolago_image/file_parser');
        $parser->parseHeaderColumns(trim($header));
        $parser->checkCsvFile($file);
        $importListData = $parser->createImportListChunk($file, $offset, $limit);

        if(!empty($importListData)){
            if(empty($importListData['list'])){
                $content['status'] = 0;
                $content['message'] = array(
                    'count' => 0,
                    'message'=> Mage::helper('zolagocatalog')->__('Nothing to map')
                );
                $this->_setSuccessResponse($content);
            }
            $importList = $importListData['list'];
            $skuvS = array_keys($importList);

            /* @var $mapper  Zolago_Catalog_Model_Mapper */
            $mapper = $this->_prepareMapper($skuvS);
            $response  = $mapper->mapByFile($importList);
            $count = $response['count'];
            $message = $response['message'];

            if($count <= 0){
                $out = Mage::helper('zolagocatalog')->__('Processed images: 0');
                if (is_array($message)) {
                    $out .= '<br/>'.implode('<br/>',$message);
                }
                $content['status'] = 0;
                $content['message'] = array(
                    'count' => 0,
                    'message'=> $out
                );

            } else {
                $content['status'] = 1;
                $content['message'] = array(
                    'total_count' => $importListData['total_count'],
                    'count' => $count,
                    'message'=> (!empty($message)) ? sprintf(Mage::helper('zolagocatalog')->__('Errors: ') . implode('<br/> ', $message)) : "",
                    'pid' => $response['pid']
                );
            }
        }


        //$result = $this->_formatSuccessContentForResponse($content);
        $this->_setSuccessResponse($content);

    }

    public function analizeImagesAction(){
        $data = $this->getRequest()->getPost('data', array());
        $selectedImagesCount = count($data);
        $skuvS = $this->_getSkuvSFromFileArray($data);

        /* @var $mapper    Zolago_Catalog_Model_Mapper  */
        $collectionForMapping = $this->_getCollectionForMapping(array_keys($skuvS));
        $collectionSize = $collectionForMapping->getSize();

        $filesValid = array();
        $matches = 0;
        if($collectionSize > 0){
            foreach($collectionForMapping as $collectionItem){
                if(isset($skuvS[$collectionItem->getSkuv()])){
                    $matches = $matches + count($skuvS[$collectionItem->getSkuv()]);
                    $filesValid[$collectionItem->getSkuv()] = $skuvS[$collectionItem->getSkuv()];
                }
            }
        }

        $response = array(
            'total' => $selectedImagesCount,
            'matches' => $matches,
            'list' => $filesValid
        );
        $this->_setSuccessResponse($response);
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

    protected function _getCollectionForMapping($skuvS){
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');
        /* @var $mapper  Zolago_Catalog_Model_Mapper */
        $mapper = Mage::getModel('zolagocatalog/mapper');
        $mapper->setPath($this->_getPath());
        $collection->addAttributeToFilter("udropship_vendor", $this->_getVendorId());
        $collection->addAttributeToFilter("skuv", array('in' => $skuvS));
        $collection->addAttributeToSelect(Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute'));
        $collection->addAttributeToSelect('name');

        //echo $collection->getSelect();
        return $collection;
    }


    protected function _prepareMapper($skuvS = array()) {
        /* @var $mapper  Zolago_Catalog_Model_Mapper */
        $mapper = Mage::getModel('zolagocatalog/mapper');
        $mapper->setPath($this->_getPath());
        $collection = $this->_getCollectionForMapping($skuvS);
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



