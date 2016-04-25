<?php

/**
 * Uploader block for Wysiwyg Images
*/
class ZolagoOs_OmniChannel_Block_Vendor_Wysiwyg_Images_Content_Uploader extends Mage_Adminhtml_Block_Media_Uploader
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('unirgy/dropship/vendor/wysiwyg/browser/content/uploader.phtml');
        $params = $this->getConfig()->getParams();
        $type = $this->_getMediaType();
        $allowed = Mage::getSingleton('udropship/wysiwyg_images_storage')->getAllowedExtensions($type);
        $labels = array();
        $files = array();
        foreach ($allowed as $ext) {
            $labels[] = '.' . $ext;
            $files[] = '*.' . $ext;
        }
        $this->getConfig()
            ->setUrl(Mage::getModel('core/url')->addSessionParam()->getUrl('*/*/upload', array('type' => $type)))
            ->setParams($params)
            ->setFileField('image')
            ->setFilters(array(
                'images' => array(
                    'label' => $this->helper('cms')->__('Images (%s)', implode(', ', $labels)),
                    'files' => $files
                )
            ));
    }

    /**
     * Return current media type based on request or data
     * @return string
     */
    protected function _getMediaType()
    {
        if ($this->hasData('media_type')) {
            return $this->_getData('media_type');
        }
        return $this->getRequest()->getParam('type');
    }
}
