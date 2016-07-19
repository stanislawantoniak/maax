<?php
/**
 * controller for attributes preview
 */
class Zolago_Catalog_Vendor_AttributesController
    extends Zolago_Catalog_Controller_Vendor_Product_Abstract {

    /**
     * @return Zolago_Dropship_Model_Session
     */
    protected function _getUdropSession() {
        return Mage::getSingleton('udropship/session');
    }

    /**
     * @return null|Zolago_Operator_Model_Operator
     */
    protected function _getOperator() {
        $session = $this->_getUdropSession();
        if($session->isOperatorMode()) {
            return $session->getOperator();
        } else {
            return null;
        }
    }

    /**
     * Vendor from udropship session
     * @return Zolago_Dropship_Model_Vendor
     */
    protected function _getVendor() {
        return $this->_getUdropSession()->getVendor();
    }

    /**
     * store assigned to vendor
     * @return string
     */
    protected function _getStore() {
        $vendor = $this->_getVendor();
        return $vendor->getLabelStore();
    }
    /**
     * Index
     */
    public function indexAction() {
        $this->_renderPage(null, 'udprod_attributes');
    }


    /**
     * attributes list by attribute set
     */
    public function get_attributesAction() {
        $attributeSetId = $this->getRequest()->getParam('attribute_set');


        $groups = Mage::getModel('eav/entity_attribute_group')
                  ->getResourceCollection()
                  ->setAttributeSetFilter($attributeSetId)
                  ->setSortOrder()
                  ->load();
        $_helper = Mage::helper('zolagocatalog');
        $list = array();
        $storeId = $this->_getStore();
        foreach ($groups as $group) {
            $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
                          ->addFieldToFilter("grid_permission", array("in"=>array(
                                                 Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::EDITION,
                                                 Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::INLINE_EDITION,
                                             )))
                          ->setAttributeGroupFilter($group->getId())
                          ->getItems();
            foreach ($attributes as $item) {
                if (!in_array($item->getAttributeCode(), array('description','short_description','brandshop','manufacturer'))) {
                    $list[] = array (
                                  'id' => $item->getId(),
                                  'label' => $item->getStoreLabel($storeId),
                                  'type' => $item->getFrontendInput(),
                                  'type_translated' => $_helper->__($item->getFrontendInput()),
                                  'required' => $item->getIsRequired() ? 'required':'not required',
                                  'required_translated' => $_helper->__($item->getIsRequired() ? 'required':'not required'),
                              );
                }
            }
        }
        echo json_encode($list);
        die();
    }

    /**
     * attribute values list
     */
    public function get_valuesAction() {
        $storeId = $this->_getStore();
        $attributeId = $this->getRequest()->getParam('attribute');
//        $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
        $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                                 ->setPositionOrder('asc')
                                 ->setAttributeFilter($attributeId)
                                 ->setStoreFilter($storeId)
                                 ->load();



        $list = array();
        foreach ($collection as $item) {
            $list[] = $item->getValue();
        }
        $out = '';
        foreach ($list as $item) {
            $out .= $item.'<br/>';
        }
        if (!$out) {
            $out = Mage::helper('zolagocatalog')->__('-- none --');
        }
        echo $out;
        die();
    }

    /**
     * suggestion new attribute value
     */
    public function ask_valueAction() {

        /* @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = Mage::helper('core');

        $attributeId = $this->getRequest()->getParam('attrId');
        $value     = trim($coreHelper->escapeHtml($this->getRequest()->getParam('value')));
        if (empty($value)) {
            $this->getResponse()->setHttpResponseCode(500);
            $this->getResponse()->setBody(Mage::helper('zolagocatalog')->__('No suggested value'));
            return;
        }
        $setId = $this->getRequest()->getParam('setId');
        $attributeSet = Mage::getModel('eav/entity_attribute_set')->load($setId);
        $storeId   = $this->_getStore();
        $store     = Mage::app()->getStore($storeId);
        $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
        $label     = $attribute->getStoreLabel($storeId);

        $operator = $this->_getOperator();
        $vendor   = $this->_getVendor();
        if ($operator) {
            $userEmail = $operator->getEmail();
            $userName  = $operator->getFullname();
        } else {
            $userEmail = $vendor->getEmail();
            $userName  = $vendor->getVendorName();
        }

        $storeEmail  = Mage::getStoreConfig('zolagoos/vendor/ask_attribute_email_cc_store', $store);
        $storeName   = $store->getFrontendName();
        $template    = Mage::getStoreConfig('zolagoos/vendor/ask_attribute_email_template', $store);
        $data['attributeSetName'] = $attributeSet->getAttributeSetName();
        $data['attributeCode'] = $attribute->getAttributeCode();
        $data['attributeName']  = $label;
        $data['attributeValue'] = $value;
        $data['vendorName'] = $vendor->getVendorName();
        $data['userEmail'] = $userEmail;
        $data['userName'] = $userName;
        $this->sendEmailTemplate(
            $userEmail,
            $userName,
            $storeEmail,
            $storeName,
            $template,
            $data,
            $storeId,
            null
        );

        $this->getResponse()->setBody(Mage::helper('zolagocatalog')->__('For attribute %s value %s was suggested', $label, $value));
    }

    /**
     * Send email to current vendor(operator) and to store
     *
     * @param $userEmail
     * @param $userName
     * @param $storeEmail
     * @param $replyEmail
     * @param null $storeName
     * @param $template
     * @param array $templateParams
     * @param bool $storeId
     * @param null $sender
     * @return Mage_Core_Model_Email_Template_Mailer
     */
    private function sendEmailTemplate($userEmail, $userName, $storeEmail, $storeName = null, $template, array $templateParams = array(), $storeId = true, $sender = null) {

        $store = Mage::app()->getStore($storeId);
        $storeId = $store->getId();
        $hlp = Mage::helper('udropship');
        $hlp->setDesignStore($store);
        $templateParams['use_attachments'] = true;// Logo

        if(is_null($sender)) {
            $sender = $store->getConfig('udropship/vendor/vendor_email_identity');
        }

        /* @var $mailer Zolago_Common_Model_Core_Email_Template_Mailer */
        $mailer = Mage::getModel('zolagocommon/core_email_template_mailer');

        /** @var Mage_Core_Model_Email_Info $emailInfoVendor */
//        $emailInfoVendor = Mage::getModel('core/email_info');
//        $emailInfoVendor->addTo($userEmail, $userName);
//        $mailer->addEmailInfo($emailInfoVendor);

        /** @var Mage_Core_Model_Email_Info $emailInfoStore */
        $emailInfoStore = Mage::getModel('core/email_info');
        if ($storeEmail) {
            $emailInfoStore->addTo($storeEmail, $storeName);
            $emailInfoStore->setReplyTo($userEmail,$userName);
            $emailInfoStore->addBcc($userEmail,$userName);
            $mailer->addEmailInfo($emailInfoStore);
        }
        
        // Set all required params and send emails
        $mailer->setSender($sender);
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($template);
        $mailer->setTemplateParams($templateParams);

        $r = $mailer->send();
        $hlp->setDesignStore();
        return $r;
    }
}
