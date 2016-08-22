<?php

class Zolago_Catalog_Vendor_ImageController
    extends Zolago_Dropship_Controller_Vendor_Abstract
{

    const PRODUCT_IMAGE_ENABLE = 0;
    const PRODUCT_IMAGE_DISABLE = 1;


    const ZOLAGO_PRODUCT_IMAGE_UPLOAD_ERROR_FILE_TOO_BIG = 1;
    const ZOLAGO_PRODUCT_IMAGE_UPLOAD_ERROR_FILE_WRONG_FORMAT = 2;
    const ZOLAGO_PRODUCT_IMAGE_UPLOAD_ERROR_FILE_DEFINED_BY_CODE = 3;

    /**
     * Index
     */

    public function indexAction()
    {
        Mage::register('as_frontend', true);// Tell block class to use regular URL's
        $this->_renderPage(array('default', 'formkey', 'adminhtml_head'), 'udprod_image');
    }

    public function check_galleryAction()
    {
        $list = $this->getRequest()->getParam('image', array());
        $products = explode(',', $list);
        /* @var $mapper  Zolago_Catalog_Model_Mapper */
        $mapper = Mage::getModel('zolagocatalog/mapper');
        $mapper->checkGallery($list);

        // Dirty hack in order to preserve filters
        // If we want to redirect to other page we need to do modifications to url
        $referer_url = $this->_getRefererUrl();
        $this->_redirectUrl($referer_url);
    }

    protected function _getVendorId()
    {
        $vendor = $this->_getSession()->getVendor();
        if ($vendor) {
            $vendorId = $vendor->getId();
        } else {
            $vendorId = '0';
        }
        return $vendorId;
    }

    protected function _makeRedirect($pidList, $fragment = false)
    {
        $extends = '';
        if ($pidList) {
            $extends = '/filter/' .
                base64_encode('massaction=1') .
                '/internal_image/' . implode(',', $pidList) . '/';

        }
        if ($fragment) {
            header('Location: ' . Mage::getUrl("udprod/vendor_image/" . $extends, array('_fragment' => $fragment)));
        } else {
            header('Location: ' . Mage::getUrl("udprod/vendor_image/" . $extends));
        }

        exit();

    }

    protected function _prepareMapper($skuvS = array())
    {
        /* @var $mapper  Zolago_Catalog_Model_Mapper */
        $mapper = Mage::getModel('zolagocatalog/mapper');
        $mapper->setPath($this->_getPath());
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');
        $collection->addAttributeToFilter("udropship_vendor", $this->_getVendorId());
        if (!empty($skuvS)) {
            $collection->addAttributeToFilter("skuv", array('in' => $skuvS));
        }
        $collection->addAttributeToSelect(Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute'));
        $collection->addAttributeToSelect('name');
        $mapper->setCollection($collection);
        return $mapper;
    }

    public function namemapAction()
    {
        /* @var $mapperModel  Zolago_Catalog_Model_Mapper */
        $mapperModel = Mage::getModel('zolagocatalog/mapper');
        $mapperModel->setPath($this->_getPath());
        $list = $mapperModel->_getFileList();

        $skuvS = array();
        if (!empty($list)) {
            foreach ($list as $imageFile) {
                $skuvS[] = explode('.', $imageFile)[0];
            }
        }

        /* @var $mapper    Zolago_Catalog_Model_Mapper */
        $mapper = $this->_prepareMapper($skuvS);
        $response = $mapper->mapByName($list);
        $count = $response['count'];
        $message = $response['message'];

        if ($count > 0) {
            Mage::getModel('catalog/product_image')->clearCache();
            if (!empty($message))
                $this->_getSession()->addError(sprintf(Mage::helper('zolagocatalog')->__('Errors: ') . implode('<br/> ', $message)));

            $this->_getSession()->addSuccess(sprintf(Mage::helper('zolagocatalog')->__('Processed images: %s '), $count));

        } else {
            if (!empty($message))
                $this->_getSession()->addError(sprintf(Mage::helper('zolagocatalog')->__('Processed images: 0') . '<br /> ' . implode('<br/> ', $message)));

            $this->_makeRedirect(false, 'tab_1_2');
        }
        $pidList = $mapper->getPidList();
        $this->_makeRedirect($pidList);
    }


    public function mapByNameAction()
    {
        $data = $this->getRequest()->getPost('data', array());
        var_export($data);
        $result = array();
        if (empty($data)) {
            $result['status'] = 0;
            $result['message'] = array('count' => 0, 'message' => Mage::helper('zolagocatalog')->__('Nothing to map'));
        }
        //var_export($result);
        $skuvS = array();

        foreach ($data as $imageFile) {
            $skuvS[] = trim(explode('.', $imageFile)[0]);
        }
        //var_export($skuvS);
        /* @var $mapper    Zolago_Catalog_Model_Mapper */
        $mapper = $this->_prepareMapper($skuvS);
        $response = $mapper->mapByName($data);
        $result['status'] = 1;
        $result['message'] = array(
            'count' => $response['count'],
            'message' => $response['message'],
            'pid' => $response['pid']
        );
        var_export($result);
    }


    public function csvmapAction()
    {
        $pidList = array();
        try {

            if (empty($_FILES['csv_file'])) {
                Mage::throwException(Mage::helper('zolagocatalog')->__('Cant upload file'));
            }

            $file = file($_FILES['csv_file']['tmp_name']);
            if (!$file) {
                Mage::throwException(Mage::helper('zolagocatalog')->__('Cant read file'));
            }
            // check file
            $check = true;
            $header = $file[0];

            unset($file[0]);
            $parser = Mage::getModel('zolago_image/file_parser');
            $parser->parseHeaderColumns(trim($header));
            $parser->checkCsvFile($file);
            $importList = $parser->createImportListFromFile($file);
            $skuvS = array_keys($importList);

            /* @var $mapper  Zolago_Catalog_Model_Mapper */
            $mapper = $this->_prepareMapper($skuvS);
            $response = $mapper->mapByFile($importList);
            $count = $response['count'];
            $message = $response['message'];
            if ($count > 0) {
                if (!empty($message))
                    $this->_getSession()->addError(sprintf(Mage::helper('zolagocatalog')->__('Errors: ') . implode('<br/> ', $message)));

                $this->_getSession()->addSuccess(sprintf(Mage::helper('zolagocatalog')->__('Processed images: %s '), $count));
                $pidList = $mapper->getPidList();

            } else {
                $out = Mage::helper('zolagocatalog')->__('Processed images: 0');
                if (is_array($message)) {
                    $out .= '<br/>' . implode('<br/>', $message);
                }
                $this->_getSession()->addError($out);
                $this->_makeRedirect(false, 'tab_1_2');
            }

        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        Mage::getModel('catalog/product_image')->clearCache();
        $this->_makeRedirect($pidList);
    }


    public function queueAction()
    {
        $this->_renderPage(null, 'udprod_image');
    }

    protected function _getPath()
    {
        $extendedPath = $this->_getVendorId();
        $path = 'var' . DIRECTORY_SEPARATOR . 'plupload' . DIRECTORY_SEPARATOR . $extendedPath;
        return $path;
    }

    public function connectorAction()
    {
        $extendedPath = $this->_getVendorId();
        $path = 'lib/ElFinder';
        include_once $path . DIRECTORY_SEPARATOR . 'elFinderConnector.class.php';
        include_once $path . DIRECTORY_SEPARATOR . 'elFinder.class.php';
        include_once $path . DIRECTORY_SEPARATOR . 'elFinderVolumeDriver.class.php';
        include_once $path . DIRECTORY_SEPARATOR . 'elFinderVolumeLocalFileSystem.class.php';
// Required for MySQL storage connector
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeMySQL.class.php';
// Required for FTP connector support
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeFTP.class.php';


        /**
         * Simple function to demonstrate how to control file access using "accessControl" callback.
         * This method will disable accessing files/folders starting from  '.' (dot)
         *
         * @param  string $attr attribute name (read|write|locked|hidden)
         * @param  string $path file path relative to volume root directory started with directory separator
         * @return bool|null
         **/
        function access($attr, $path, $data, $volume)
        {
            return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
                ? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
                : null;                                    // else elFinder decide it itself
        }

        $path = $this->_getPath();
        $opts = array(
            // 'debug' => true,
            'roots' => array(
                array(
                    'driver' => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
                    'path' => $path,         // path to files (REQUIRED)
                    'URL' => dirname($_SERVER['PHP_SELF']) . 'var/plupload/' . $extendedPath, // URL to files (REQUIRED)
                    'accessControl' => 'access'             // disable and hide dot starting files (OPTIONAL)
                )
            )
        );

// run elFinder
        // Create target dir
        if (!file_exists($path)) {
            @mkdir($path);
        }
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();


    }

    /**
     * upload
     */
    public function uploadAction()
    {
        /**
         * upload.php
         *
         * Copyright 2013, Moxiecode Systems AB
         * Released under GPL License.
         *
         * License: http://www.plupload.com/license
         * Contributing: http://www.plupload.com/contributing
         */

#!! IMPORTANT:
#!! this file is just an example, it doesn't incorporate any security checks and 
#!! is not recommended to be used in production environment as it is. Be sure to 
#!! revise it and customize to your needs.


        // Make sure file is not cached (as it happens for example on iOS devices)
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        /*
        // Support CORS
        header("Access-Control-Allow-Origin: *");
        // other CORS headers if any...
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit; // finish preflight CORS requests here
        }
        */

        // 5 minutes execution time
        @set_time_limit(5 * 60);
        // Uncomment this one to fake upload time
        // usleep(5000);

        // Settings
        $targetDir = 'var' . DIRECTORY_SEPARATOR . "plupload";

        $vendor = $this->_getSession()->getVendor();
        if ($vendor) {
            $targetDir .= DIRECTORY_SEPARATOR . $vendor->getId();
        } else {
            $targetDir .= DIRECTORY_SEPARATOR . '0';
        }
        //$targetDir = 'uploads';
        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds


        // Create target dir
        if (!file_exists($targetDir)) {
            @mkdir($targetDir);
        }
        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;


        // Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die(' {"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }

            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}.part") {
                    continue;
                }

                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }


        // Open temp file
        if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
            die(' {"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die(' {"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die(' {"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                die(' {"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }

        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off
            rename("{$filePath}.part", $filePath);
        }
        if (!$this->_checkImage($filePath)) {
            unlink($filePath);
            die(' {"jsonrpc" : "2.0", "error" : {"code": 101, "message": "File is not image."}, "id" : "id"}');
        }
        // Return Success JSON-RPC response
        die(' {"jsonrpc" : "2.0", "result" : null, "id" : "id"}');

    }

    protected function _checkImage($path)
    {
        if (getimagesize($path)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param Zolago_Catalog_Model_Product $product
     * @throws Exception
     */
    public function setMainGalleryPage(Zolago_Catalog_Model_Product $product)
    {
        $productId = $product->getId();
        $enabledImages = Mage::getResourceModel("zolagocatalog/product_gallery")
            ->getEnabledProductImages($productId);

        if (!empty($enabledImages) && isset($enabledImages[0])) {
            $image = $enabledImages[0];

            $product->setImage($image);
            $product->getResource()->saveAttribute($product, 'image');
            $product->setSmallImage($image);
            $product->getResource()->saveAttribute($product, 'small_image');
            $product->setThumbnail($image);
            $product->getResource()->saveAttribute($product, 'thumbnail');
        }


        //3. put products to solr queue
        //catalog_converter_price_update_after
        if ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            Mage::dispatchEvent(
                "catalog_converter_price_update_after",
                array(
                    "product_ids" => array($product->getId())
                )
            );
        }
    }

    /**
     * Reorder products from Vendor Panel -> Zarządzanie zdjęciami
     * /udprod/vendor_image/
     * @return array
     * @throws Exception
     */
    public function changeProductImagesOrderAction()
    {
        $productId = $this->getRequest()->getParam("product", null);
        $imagesData = $this->getRequest()->getParam("images", array());
        if (empty($productId))
            return array();

        if (empty($imagesData))
            return array();

        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $productMediaValueTable = $resource->getTableName('catalog_product_entity_media_gallery_value');

        //1. set position
        foreach ($imagesData as $position => $value_id) {
            $where = $writeConnection->quoteInto("value_id=?", $value_id);
            $writeConnection->update(
                $productMediaValueTable,
                array("position" => $position),
                $where
            );
        }
        //2. set first image as image, small_image and thumbnail
        $product = Mage::getModel("catalog/product")->load($productId);
        $this->setMainGalleryPage($product);
    }


    /**
     * Enable/disable product image
     */
    public function toggleAvailabilityProductImageAction()
    {
        $productId = $this->getRequest()->getParam("product", null);
        $imageValue = $this->getRequest()->getParam("image_value", null);
        $action = $this->getRequest()->getParam("action", 0);

        $enabledImages = Mage::getResourceModel("zolagocatalog/product_gallery")
            ->getEnabledProductImages($productId);

        $product = Mage::getModel("catalog/product")->load($productId);
        $productStatus = $product->getStatus();

        $enableImage = self::PRODUCT_IMAGE_ENABLE;
        $disableImage = self::PRODUCT_IMAGE_DISABLE;

        $_helper = Mage::helper("zolagocatalog");

        //If product enabled then last image can't be disabled
        if (
            $action == $disableImage
            && $productStatus == Mage_Catalog_Model_Product_Status::STATUS_ENABLED
            && (count($enabledImages) <= 1)
        ) {
            $result = array(
                'status' => 0,
                'error' => $_helper->__("Product is enabled and it should have at least one enabled image.")
            );
        } else {
            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');
            $productMediaValueTable = $resource->getTableName('catalog_product_entity_media_gallery_value');

            try {
                $where = $writeConnection->quoteInto("value_id=?", $imageValue);
                if ($action == $disableImage) {

                    $writeConnection->update(
                        $productMediaValueTable,
                        array("disabled" => $disableImage),
                        $where
                    );
                } else {
                    $writeConnection->update(
                        $productMediaValueTable,
                        array("disabled" => $enableImage),
                        $where
                    );
                }
                $this->setMainGalleryPage($product);
                $result = array(
                    'status' => 1
                );

            } catch (GH_Common_Exception $e) {
                Mage::logException($e);
                $result = array(
                    'status' => 0,
                    'error' => $_helper->__("An error occurred"),
                    'errorcode' => $e->getCode()
                );
            } catch (Exception $e) {
                Mage::logException($e);
                $result = array(
                    'status' => 0,
                    'error' => $_helper->__("An error occurred"),
                    'errorcode' => $e->getCode()
                );
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Delete product image
     */
    public function deleteProductImageAction()
    {
        $productId = $this->getRequest()->getParam("product", null);
        $imageValue = $this->getRequest()->getParam("image_value", null);

        $enabledImages = Mage::getResourceModel("zolagocatalog/product_gallery")
            ->getEnabledProductImages($productId);


        $imageData = Mage::getResourceModel("zolagocatalog/product_gallery")
            ->getProductImageData($imageValue);

        $lastEnabledImage = (count($enabledImages) <= 1 && empty($imageData["disabled"])) ? TRUE : FALSE;

        $product = Mage::getModel("catalog/product")->load($productId);
        $productStatus = $product->getStatus();

        $_helper = Mage::helper("zolagocatalog");

        //If product enabled then last image can't be disabled
        if ($productStatus == Mage_Catalog_Model_Product_Status::STATUS_ENABLED && $lastEnabledImage) {
            $result = array(
                'status' => 0,
                'error' => $_helper->__("Product is enabled and it should have at least one enabled image.")
            );
        } else {


            try {
                $resource = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');
                $productMediaTable = $resource->getTableName("catalog/product_attribute_media_gallery");
                $productMediaValueTable = $resource->getTableName("catalog/product_attribute_media_gallery_value");
                $where = $writeConnection->quoteInto("value_id=?", $imageValue);

                $writeConnection->delete($productMediaTable, $where);
                $writeConnection->delete($productMediaValueTable, $where);

                $this->setMainGalleryPage($product);
                $result = array(
                    'status' => 1
                );
            } catch (GH_Common_Exception $e) {
                Mage::logException($e);
                $result = array(
                    'status' => 0,
                    'error' => $_helper->__("An error occurred"),
                    'errorcode' => $e->getCode()
                );
            } catch (Exception $e) {
                Mage::logException($e);
                $result = array(
                    'status' => 0,
                    'error' => $_helper->__("An error occurred"),
                    'errorcode' => $e->getCode()
                );
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function uploadProductImageAction()
    {
        $productId = $this->getRequest()->getParam("product", null);
        /** @var Zolago_Catalog_Model_Product $product */
        $product = Mage::getModel("catalog/product")->load($productId);

        $_helper = Mage::helper("zolagocatalog");
        /* @var $_helperGHCommon GH_Common_Helper_Data */
        $_helperGHCommon = Mage::helper('ghcommon');
        $file = isset($_FILES["vendor_image_upload"]) ? $_FILES["vendor_image_upload"] : array();
        $size = !empty($file) ? $file["size"] : 1000000;


        $maxUploadFileSize = $_helperGHCommon->getMaxUploadFileSize();
        if (empty($file) || ($size >= $maxUploadFileSize)) { //5MB
            $result = array(
                'error' => $_helper->__("Files are too large. File must be less than %sMB.", round($maxUploadFileSize / (1024 * 1024), 1)),
                "type" => self::ZOLAGO_PRODUCT_IMAGE_UPLOAD_ERROR_FILE_TOO_BIG
            );
        } else {

            try {
                $uploader = new Mage_Core_Model_File_Uploader('vendor_image_upload');
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                $uploader->addValidateCallback(
                    'catalog_product_image',
                    Mage::helper('catalog/image'), 'validateUploadFile');
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $result = $uploader->save(Mage::getSingleton('catalog/product_media_config')->getBaseTmpMediaPath());

                $imagePath = $result["path"] . $result["file"];

                $label = $product->getName();
                Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                $product->addImageToMediaGallery($imagePath, null, false, false, $label);

                //fixes url change on product save problem (task: re#1922)
                $product->setExcludeUrlRewrite(true);
                $product->save();

                $result["content"] = Mage::helper("zolagocatalog/image")->generateProductGallery($productId);

                $this->setMainGalleryPage($product);
            } catch (Exception $e) {
                if ($e->getCode() == 0) {
                    $result = array(
                        'error' => $_helper->__("Disallowed file type. Please upload jpg, jpeg, gif or png."),
                        "type" => self::ZOLAGO_PRODUCT_IMAGE_UPLOAD_ERROR_FILE_WRONG_FORMAT,
                        'errorcode' => $e->getCode());
                } else {
                    $result = array(
                        'error' => $_helper->__("An error occurred"),
                        "type" => self::ZOLAGO_PRODUCT_IMAGE_UPLOAD_ERROR_FILE_DEFINED_BY_CODE,
                        'errorcode' => $e->getCode());
                }

            }
        }


        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}



