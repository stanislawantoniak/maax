<?php

class Zolago_Catalog_Vendor_ImageController
        extends Zolago_Dropship_Controller_Vendor_Abstract {
    /**
     * Index
     */
    public function indexAction() {
        $this->_renderPage(null, 'udprod_image_queue');
    }
    public function queueAction() {
        $this->_renderPage(null, 'udprod_image');
    }
    public function connectorAction() {
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
        $vendor = $this->_getSession()->getVendor();
        if ($vendor) {
            $extendedPath = $vendor->getId();
        } else {
            $extendedPath = '0';
        }
        $path = 'var'.DIRECTORY_SEPARATOR.'plupload'.DIRECTORY_SEPARATOR.$extendedPath;
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
     * send
     */
    public function sendAction() {
        ?>
        <!DOCTYPE html>
        <html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
                    <head>
                    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
                                     <title>Plupload - Form dump</title>
                                     </head>
                                     <body style="font: 13px Verdana; background: #eee; color: #333">

                                             <h1>Post dump</h1>

                                             <p>Shows the form items posted.</p>

                                             <table>
                                             <tr>
                                             <th>Name</th>
                                             <th>Value</th>
                                             </tr>
                                             <?php $count = 0;
        foreach ($_POST as $name => $value) {
            ?>
            <tr class="<?php echo $count % 2 == 0 ? 'alt' : ''; ?>">
                          <td><?php echo htmlentities(stripslashes($name)) ?></td>
                          <td><?php echo nl2br(htmlentities(stripslashes($value))) ?></td>
                          </tr>
                          <?php
                } ?>
        </table>

        </body>
        </html>
        <?php
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

        // Return Success JSON-RPC response
        die(' {"jsonrpc" : "2.0", "result" : null, "id" : "id"}');

    }
}


