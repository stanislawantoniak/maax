<?php

/**
 * Class GH_AttributeRules_IndexController
 * @method Zolago_Dropship_Model_Session _getSession()
 */
class GH_AttributeRules_IndexController extends Zolago_Dropship_Controller_Vendor_Abstract
{
    /**
     * Save Attribute rule action
     */
    public function saveAction()
    {
        if (!$this->getRequest()->isPost()) {
            return false;
        }

        $_helper = Mage::helper("gh_attributerules");
        $id = $this->getRequest()->getPost('attribute_rule_id');

        /* @var $attributeRule GH_AttributeRules_Model_Rule */
        $attributeRule = $this->_initModel($id);
        $vendor = $this->_getSession()->getVendor();

        // Edit
        if (!empty($id) && !$attributeRule->getId()) {
            throw new Mage_Core_Exception($_helper->__("Attribute Rule does not exists"));
        }

        if ($attributeRule->getVendorId() != $vendor->getId()) {
            throw new Mage_Core_Exception($_helper->__("Attribute Rule does not exists"));
        }

        // Set Vendor Owner
        $attributeRule->setVendorId($vendor->getId());

        $data = $this->getRequest()->getParams();
        $attributeRule->addData($data);
        $attributeRule->save();
    }


    /**
     * @param $modelId
     * @return GH_AttributeRules_Model_Rule
     */
    protected function _initModel($modelId)
    {
        if (Mage::registry('current_attribute_rule') instanceof GH_AttributeRules_Model_Rule) {
            return Mage::registry('current_attribute_rule');
        }

        $model = Mage::getModel("gh_attributerules/attribute_rules");
        /* @var $model GH_AttributeRules_Model_Rule */
        if ($modelId) {
            $model->load($modelId);
        }

        Mage::register('current_attribute_rule', $model);
        return $model;
    }

}