<?php

require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Catalog' . DS . 'Product' . DS . 'SetController.php';
class Zolago_Adminhtml_Catalog_Product_SetController extends Mage_Adminhtml_Catalog_Product_SetController
{
    /**
     * Save attribute set action
	 * Override to get Save and Edit Return URL feature
     *
     * [POST] Create attribute set from another set and redirect to edit page
     * [AJAX] Save attribute set data
     *
     */
    public function saveAction()
    {
        $entityTypeId   = $this->_getEntityTypeId();
        $hasError       = false;
        $attributeSetId = $this->getRequest()->getParam('id', false);
        $isNewSet       = $this->getRequest()->getParam('gotoEdit', false) == '1';
		$saveAndEdit	= $this->getRequest()->getParam('saveAndEdit', false);

        /* @var $model Mage_Eav_Model_Entity_Attribute_Set */
        $model  = Mage::getModel('eav/entity_attribute_set')
            ->setEntityTypeId($entityTypeId);

        /** @var $helper Mage_Adminhtml_Helper_Data */
        $helper = Mage::helper('adminhtml');
        try {
            if ($isNewSet) {
                //filter html tags
                $name = $helper->stripTags($this->getRequest()->getParam('attribute_set_name'));
                $model->setAttributeSetName(trim($name));

                $useToCreateProduct = $this->getRequest()->getParam('attribute_set_use_to_create_product');
                $model->setData('use_to_create_product',$useToCreateProduct);

                $useSizeboxList = $this->getRequest()->getParam('attribute_set_use_sizebox_list');
                $model->setData('use_sizebox_list',$useSizeboxList);

                Mage::log('New ');
            } else {
                if ($attributeSetId) {
                    $model->load($attributeSetId);
                }
                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('catalog')->__('This attribute set no longer exists.'));
                }
                $data = Mage::helper('core')->jsonDecode($this->getRequest()->getPost('data'));
                //filter html tags
                $data['attribute_set_name'] = $helper->stripTags($data['attribute_set_name']);

                $model->organizeData($data);

                $useToCreateProduct = isset($data['attribute_set_use_to_create_product'])
                    ? (int)$data['attribute_set_use_to_create_product'] : 0;
                $model->setData('use_to_create_product', $useToCreateProduct);

                $useSizeboxList = isset($data['attribute_set_use_sizebox_list'])
                    ? (int)$data['attribute_set_use_sizebox_list'] : 0;
                $model->setData('use_sizebox_list', $useSizeboxList);
            }

            $model->validate();
            if ($isNewSet) {
                $model->save();
                $model->initFromSkeleton($this->getRequest()->getParam('skeleton_set'));
            }
            $model->save();
            $this->_getSession()->addSuccess(Mage::helper('catalog')->__('The attribute set has been saved.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $hasError = true;
        } catch (Exception $e) {
            $this->_getSession()->addException($e,
                Mage::helper('catalog')->__('An error occurred while saving the attribute set.'));
            $hasError = true;
        }

        if ($isNewSet) {
            if ($hasError) {
                $this->_redirect('*/*/add');
            } else {
				$this->_getSession()->addSuccess(Mage::helper('catalog')->__('The attribute set has been saved.'));
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
            }
		} elseif ($saveAndEdit) {
			$response = array();
			$this->_initLayoutMessages('adminhtml/session');
			if (!$hasError) {
				$this->_getSession()->addSuccess(Mage::helper('catalog')->__('The attribute set has been saved.'));
				$response['error']   = 0;
				$response['url']     = $this->getUrl('*/*/edit', array('id' => $model->getId()));
			} else {
				$this->getSession()->addException(Mage::helper('catalog')->__('An error occurred while saving the attribute set.'));
                $response['error']   = 0;
                $response['url']     = $this->getUrl('*/*/');				
			}
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        } else {
            $response = array();
            if ($hasError) {
                $this->_initLayoutMessages('adminhtml/session');
                $response['error']   = 1;
                $response['message'] = $this->getLayout()->getMessagesBlock()->getGroupedHtml();
            } else {
                $response['error']   = 0;
                $response['url']     = $this->getUrl('*/*/');
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        }
    }	
}