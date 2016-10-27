<?php
/**
 * config for product description editor
 */

class Zolago_Catalog_Model_Wysiwyg_Config extends Mage_Cms_Model_Wysiwyg_Config {
    
    
    /**
     * Check whether Wysiwyg is enabled or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        $storeId = $this->getStoreId();
        if (!is_null($storeId)) {
            $wysiwygState = Mage::getStoreConfig('cms/wysiwyg/enabled_product', $storeId);
        } else {
            $wysiwygState = Mage::getStoreConfig('cms/wysiwyg/enabled_product');
        }
        return in_array($wysiwygState, array(self::WYSIWYG_ENABLED, self::WYSIWYG_HIDDEN));
    }

    /**
     * Check whether Wysiwyg is loaded on demand or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return Mage::getStoreConfig('cms/wysiwyg/enabled_product') == self::WYSIWYG_HIDDEN;
    }

}