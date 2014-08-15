<?php

class Zolago_Banner_Helper_Data extends Mage_Core_Helper_Abstract {

    public function setBannerTypeUrl(){
        return Mage::getUrl('zolagobanner/vendor/setType');
    }

    public function bannerTypeUrl($campaignId)
    {
        return Mage::getUrl('zolagobanner/vendor/type', array('campaign_id' => $campaignId));
    }

    public function bannerEditUrl($campaignId, $type)
    {
        return Mage::getUrl('zolagobanner/vendor/edit', array('type' => $type, 'campaign_id' => $campaignId));
    }

    public function getHideEditFields()
    {
        $hideFields = explode(',', Mage::getStoreConfig('udropship/microsite/hide_product_attributes'));
        $hideFields[] = 'udropship_vendor';
        //$hideFields[] = 'tier_price';
        $hideFields[] = 'gallery';
        $hideFields[] = 'media_gallery';
        $hideFields[] = 'small_image';
        $hideFields[] = 'thumbnail';
        $hideFields[] = 'image';
        $hideFields[] = 'recurring_profile';
        $hideFields[] = '';
        return $hideFields;
    }
    public function getEditFieldsConfig()
    {
        $entityType = Mage::getSingleton('eav/config')->getEntityType('catalog_product');
        $hideFields = $this->getHideEditFields();
        $attrs = $entityType->getAttributeCollection()
            ->addFieldToFilter('is_visible', 1)
            ->setOrder('frontend_label', 'asc');
        $editFields = array();
        $paValues = array();
        foreach ($attrs as $a) {
            if (!in_array($a->getAttributeCode(), $hideFields)) {
                $paValues['product.'.$a->getAttributeCode()] = $a->getFrontendLabel().' ['.$a->getAttributeCode().']';
            }
        }
        $editFields['product']['label'] = 'Product Attributes';
        $editFields['product']['values'] = $paValues;

        $editFields['system']['label'] = 'System Attributes';
        $editFields['system']['values'] = array(
            'system.product_categories' => Mage::helper('catalog')->__('Categories'),
            'system.product_websites'   => Mage::helper('catalog')->__('Websites')
        );

        if (Mage::helper('udropship')->isUdmultiActive()) {
            $editFields['udmulti']['label'] = Mage::helper('udmulti')->__('Vendor Specific Fields');
            $editFields['udmulti']['values']  = $this->getVendorEditFieldsConfig();
        } else {
            $sdValues['stock_data.qty'] = Mage::helper('cataloginventory')->__('Stock Qty').' [stock_item.qty]';
            $sdValues['stock_data.is_in_stock'] = Mage::helper('cataloginventory')->__('Stock Status').' [stock_item.is_in_stock]';
            $sdValues['stock_data.manage_stock'] = Mage::helper('cataloginventory')->__('Manage Stock').' [stock_item.manage_stock]';
            $sdValues['stock_data.backorders'] = Mage::helper('cataloginventory')->__('Backorders').' [stock_item.backorders]';
            $sdValues['stock_data.min_qty'] = Mage::helper('cataloginventory')->__('Qty for Item\'s Status to Become Out of Stock').' [stock_item.min_qty]';
            $sdValues['stock_data.min_sale_qty'] = Mage::helper('cataloginventory')->__('Minimum Qty Allowed in Shopping Cart').' [stock_item.min_sale_qty]';
            $sdValues['stock_data.max_sale_qty'] = Mage::helper('cataloginventory')->__('Maximum Qty Allowed in Shopping Cart').' [stock_item.max_sale_qty]';
            $editFields['stock_data']['label'] = Mage::helper('udropship')->__('Stock Item Fields');
            $editFields['stock_data']['values']  = $sdValues;
        }
        return $editFields;
    }

    public function getEditFieldsConfigSelect2Json()
    {
        $fConfig = $this->getEditFieldsConfig();

        $fRes = array(array('id'=>'','text'=>$this->__('* Please select')));
        foreach ($fConfig as $efc) {
            if (!is_array($efc['values'])) continue;
            $_fRes = array(
                'text' => $efc['label']
            );
            foreach ($efc['values'] as $fId=>$fLbl) {
                $_fRes['children'][] = array(
                    'id' => $fId,
                    'text' => $fLbl,
                );
            }
            $fRes[] = $_fRes;
        }
        return Mage::helper('core')->jsonEncode($fRes);
    }

    public function serialize($value)
    {
        return Zend_Json::encode($value);
    }
    public function unserialize($value)
    {
        if (empty($value)) {
            $value = empty($value) ? array() : $value;
        } elseif (!is_array($value)) {
            if (strpos($value, 'a:')===0) {
                $value = @unserialize($value);
                if (!is_array($value)) {
                    $value = array();
                }
            } elseif (strpos($value, '{')===0 || strpos($value, '[{')===0) {
                try {
                    $value = Zend_Json::decode($value);
                } catch (Exception $e) {
                    $value = array();
                }
            }
        }
        return $value;
    }
}