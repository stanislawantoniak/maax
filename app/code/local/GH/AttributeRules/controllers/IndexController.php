<?php

/**
 * Class GH_AttributeRules_IndexController
 */
class GH_AttributeRules_IndexController extends Mage_Core_Controller_Front_Action
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

        $data = $this->getRequest()->getParams();

        // If Edit
        if (!empty($modelId) && !$attributeRule->getId()) {
            throw new Mage_Core_Exception($_helper->__("Attribute Rule not found"));
        }

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