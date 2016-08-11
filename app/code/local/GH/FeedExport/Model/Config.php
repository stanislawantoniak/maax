<?php

/**
 * Class GH_FeedExport_Model_Config
 */
class GH_FeedExport_Model_Config extends Mirasvit_FeedExport_Model_Config {


    public function getTmpBasePath()
    {
        $dir = Mage::getBaseDir('var').DS.'feed';
        if (!file_exists($dir)) {
            mkdir($dir);
        }

        return $dir;
    }

    public function getTmpPath($key)
    {
        return $this->getTmpBasePath().DS.'tmp'.DS.$key;
    }

}