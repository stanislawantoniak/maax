<?php

/**
 * Directoty tree renderer for Cms Wysiwyg Images
 */
class ZolagoOs_OmniChannel_Block_Vendor_Wysiwyg_Images_Tree extends Mage_Core_Block_Template
{
    public function getTreeJson()
    {
        $helper = Mage::helper('udropship/wysiwyg_images');
        $storageRoot = $helper->getStorageRoot();
        $collection = Mage::registry('storage')->getDirsCollection($helper->getCurrentPath());
        $jsonArray = array();
        foreach ($collection as $item) {
            $jsonArray[] = array(
                'text'  => $helper->getShortFilename($item->getBasename(), 20),
                'id'    => $helper->convertPathToId($item->getFilename()),
                'cls'   => 'folder'
            );
        }
        return Zend_Json::encode($jsonArray);
    }

    public function getTreeLoaderUrl()
    {
        return $this->getUrl('*/*/treeJson');
    }

    public function getRootNodeName()
    {
        return $this->helper('cms')->__('Storage Root');
    }

    public function getTreeCurrentPath()
    {
        $treePath = '/root';
        if ($path = Mage::registry('storage')->getSession()->getCurrentPath()) {
            $helper = Mage::helper('udropship/wysiwyg_images');
            $path = str_replace($helper->getStorageRoot(), '', $path);
            $relative = '';
            foreach (explode(DS, $path) as $dirName) {
                if ($dirName) {
                    $relative .= DS . $dirName;
                    $treePath .= '/' . $helper->idEncode($relative);
                }
            }
        }
        return $treePath;
    }
}
