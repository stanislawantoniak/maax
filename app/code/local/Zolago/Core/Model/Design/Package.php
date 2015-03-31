<?php

class Zolago_Core_Model_Design_Package extends Mage_Core_Model_Design_Package {

    /**
     * Get the timestamp of the newest file
     * @param $srcFiles
     * @return int|mixed|null
     */
    protected function getNewestFileTimestamp($srcFiles)
    {
        $timeStamp = null;
        foreach ($srcFiles as $file) {
            if (is_null($timeStamp)) {
//if is first file, set $timeStamp to filemtime of file
                $timeStamp = file_exists($file) ? filemtime($file) : null;
            } else {
//get max of current files filemtime and the max so far

                $timeStamp = file_exists($file) ? max($timeStamp, filemtime($file)) : null;
            }
        }
        return $timeStamp;
    }


    /**
     * Merge specified javascript files and return URL to the merged file on success
     *
     * @param $files
     * @return string
     */
    public function getMergedJsUrl($files)
    {
        $targetDir = $this->_initMergerDir('js');
        if (!$targetDir) {
            return '';
        }
        if (!Mage::app()->getStore()->isAdmin()) {
            //remerge files (if required)
            $filesTimeStamp = $this->getNewestFileTimestamp($files);
            $targetFilename = md5(implode(',', $files)) . "_" . $filesTimeStamp . '.js';
            $jsFileUrl = Mage::getBaseUrl('media', Mage::app()->getRequest()->isSecure()) . 'js/' . $targetFilename;

            if (!file_exists($targetDir . DS . $targetFilename)) {
                $mergeFilesResult = $this->_mergeFiles(
                    $files,
                    $targetDir . DS . $targetFilename,
                    false,
                    null,
                    'js'
                );
                if ($mergeFilesResult) {
                    return $jsFileUrl;
                }
            } else {
                return $jsFileUrl;
            }
        } else {
            $targetFilename = md5(implode(',', $files)) . '.js';
            $jsFileUrl = Mage::getBaseUrl('media', Mage::app()->getRequest()->isSecure()) . 'js/' . $targetFilename;
            if ($this->_mergeFiles($files, $targetDir . DS . $targetFilename, false, null, 'js')) {
                return $jsFileUrl;
            }
        }
        return '';
    }


    /**
     * Merge specified css files and return URL to the merged file on success
     *
     * @param $files
     * @return string
     */
    public function getMergedCssUrl($files)
    {
        $isSecure = Mage::app()->getRequest()->isSecure();
        $mergerDir = $isSecure ? 'css_secure' : 'css';
        $targetDir = $this->_initMergerDir($mergerDir);
        if (!$targetDir) {
            return '';
        }

        $baseMediaUrl = Mage::getBaseUrl('media', $isSecure);
        $hostname = parse_url($baseMediaUrl, PHP_URL_HOST);
        $port = parse_url($baseMediaUrl, PHP_URL_PORT);
        if (false === $port) {
            $port = $isSecure ? 443 : 80;
        }

        if (!Mage::app()->getStore()->isAdmin()) {
            //get timestamp of newest source file
            $filesTimeStamp = $this->getNewestFileTimestamp($files);
            // merge into target file
            $targetFilename = md5(implode(',', $files) . "|{$hostname}|{$port}") . "_" . $filesTimeStamp . '.css';
            if (!file_exists($targetDir . DS . $targetFilename)) {
                $mergeFilesResult = $this->_mergeFiles(
                    $files, $targetDir . DS . $targetFilename,
                    false,
                    array($this, 'beforeMergeCss'),
                    'css'
                );
                if ($mergeFilesResult) {
                    return $baseMediaUrl . $mergerDir . '/' . $targetFilename;
                }
            } else {
                return $baseMediaUrl . $mergerDir . '/' . $targetFilename;
            }
        } else {
            // merge into target file
            $targetFilename = md5(implode(',', $files) . "|{$hostname}|{$port}") . '.css';
            $mergeFilesResult = $this->_mergeFiles(
                $files, $targetDir . DS . $targetFilename,
                false,
                array($this, 'beforeMergeCss'),
                'css'
            );
            if ($mergeFilesResult) {
                return $baseMediaUrl . $mergerDir . '/' . $targetFilename;
            }
        }

        //If the file with the proper timestamp as part of its filename already exists,
        // there's no reason to check again to see if
        //we need to merge again the css files

        return '';
    }
}