<?php

class Zolago_Catalog_Vendor_ImageController
        extends Zolago_Dropship_Controller_Vendor_Abstract {
    /**
     * Index
     */

    public function indexAction() {
        Mage::register('as_frontend', true);// Tell block class to use regular URL's
        $this->_renderPage(array('default','formkey','adminhtml_head'), 'udprod_image');
    }
    public function check_galleryAction() {
        $list = $this->getRequest()->getParam('image',array());
        $products = explode(',',$list);
        foreach ($products as $id) {
            $_product = Mage::getModel('catalog/product')->load($id);
            $_product->setGalleryToCheck(0);
            $_product->getResource()->saveAttribute($_product, 'gallery_to_check');
        }
        $this->_redirect('*/*/');
    }
    protected function _getVendorId() {
        $vendor = $this->_getSession()->getVendor();
        if ($vendor) {
            $vendorId = $vendor->getId();
        } else {
            $vendorId = '0';
        }
        return $vendorId;
    }
    protected function _redirect($pidList) {
        if ($pidList) {
            $extends = '/filter/'.
                base64_encode('massaction=1').
                '/internal_image/'.implode(',',$pidList).'/';
                
        }
        header('Location: '.Mage::getUrl("udprod/vendor_image/".$extends));
        
    }
    protected function _prepareMapper() {
        $path = $this->_getPath();
        $mapper = Mage::getModel('zolagocatalog/mapper');
        $mapper->setPath($this->_getPath());
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');
        $collection->addAttributeToFilter("udropship_vendor", $this->_getVendorId());
        $collection->addAttributeToSelect(Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute'));
        $collection->addAttributeToSelect('name');
        $mapper->setCollection($collection);
        return $mapper;
    }
    public function namemapAction() {
        $mapper = $this->_prepareMapper();
        $result = $mapper->mapByName();
        $this->_getSession()->addSuccess(sprintf(Mage::helper('zolagocatalog')->__('Operation successful. Processed images: %s '),$result));
        $pidList = $mapper->getPidList();
        $this->_redirect($pidList);        
    }
    public function csvmapAction() {
        $pidList = array();
        if (!empty($_FILES['csv_file'])) {
            $file = file($_FILES['csv_file']['tmp_name']);
            if (!$file) {
                $this->_getSession()->addError(Mage::helper('zolagocatalog')->__('Cant read file'));
            } else {
                // check file
                $check = true;
                $header = $file[0];
                unset($file[0]);
                if (!preg_match('/^sku;file;order;label$/',trim($header))) {
                    $this->_getSession()->addError(Mage::helper('zolagocatalog')->__('Wrong file header'));
                } else {
                    foreach ($file as $number=>$line) {
                        if (trim($line) &&
                                (!preg_match('/^([a-zA-Z\.\-\_\ \(\)\{\}ąćłóżźęśńĘÓĄŚŻŹĆŃŁ0-9\:\/@#]+;){2}[0-9]*;([a-zA-Z\.\-\_\ \(\)\{\}ąćłóżźęśńĘÓĄŚŻŹĆŃŁ0-9]+)?$/',trim($line)))) {
                            $check = false;
                            break;
                        }
                    }
                    if (!$check) {
                        $this->_getSession()->addError(Mage::helper('zolagocatalog')->__('Wrong file format. Error at line ').' '.($number+1).':'.$line);
                    } else {
                        $mapper = $this->_prepareMapper();
                        $mapper->setFile($file);
                        $count = $mapper->mapByFile();
                        $this->_getSession()->addSuccess(sprintf(Mage::helper('zolagocatalog')->__('Operation successful. Processed images: %s '),$count));
                        $pidList = $mapper->getPidList();
                    }
                }
            }
        } else {
            $this->_getSession()->addError(Mage::helper('zolagocatalog')->__('Cant upload file'));
        }
        $this->_redirect($pidList);
    }
    public function queueAction() {
        $this->_renderPage(null, 'udprod_image');
    }

    protected function _getPath() {
        $extendedPath = $this->_getVendorId();
        $path = 'var'.DIRECTORY_SEPARATOR.'plupload'.DIRECTORY_SEPARATOR.$extendedPath;
        return $path;
    }

    public function connectorAction() {
        $extendedPath = $this->_getVendorId();
        $path = 'lib/ElFinder';
        include_once $path.DIRECTORY_SEPARATOR.'elFinderConnector.class.php';
        include_once $path.DIRECTORY_SEPARATOR.'elFinder.class.php';
        include_once $path.DIRECTORY_SEPARATOR.'elFinderVolumeDriver.class.php';
        include_once $path.DIRECTORY_SEPARATOR.'elFinderVolumeLocalFileSystem.class.php';
// Required for MySQL storage connector
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeMySQL.class.php';
// Required for FTP connector support
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeFTP.class.php';


        /**
         * Simple function to demonstrate how to control file access using "accessControl" callback.
         * This method will disable accessing files/folders starting from  '.' (dot)
         *
         * @param  string  $attr  attribute name (read|write|locked|hidden)
         * @param  string  $path  file path relative to volume root directory started with directory separator
         * @return bool|null
         **/
        function access($attr, $path, $data, $volume) {
            return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
                                                    ? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
                                                    :  null;                                    // else elFinder decide it itself
        }
        $path = $this->_getPath();
        $opts = array(
                    // 'debug' => true,
                    'roots' => array(
                        array(
                            'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
                            'path'          => $path,         // path to files (REQUIRED)
                            'URL'           => dirname($_SERVER['PHP_SELF']) . 'var/plupload/'.$extendedPath, // URL to files (REQUIRED)
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
    public function uploadAction() {
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
            $targetDir .= DIRECTORY_SEPARATOR. $vendor->getId();
        } else {
            $targetDir .= DIRECTORY_SEPARATOR. '0';
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
    protected function _checkImage($path) {
        if (getimagesize($path)) {
            return true;
        } else {
            return false;
        }
    }
}



