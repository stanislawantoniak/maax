<?php
require_once Mage::getModuleDir('controllers', "Unirgy_Dropship") . DS . "Adminhtml". DS . "VendorController.php";
/**
 * new functions for admin_vendor controller
 */
class Zolago_Dropship_Adminhtml_VendorController extends Unirgy_Dropship_Adminhtml_VendorController {	
    
    /**
     * Brandshop settings grid
     */

    public function brandshopSettingsAction() {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('zolagodropship/adminhtml_vendor_brandshop')
            ->setUseAjax(true)
            ->toHtml()
        );        
    }
    public function brandshopEditAction()
    {
        $_hlp = Mage::helper('adminhtml');
        $this->_title($_hlp->__('Sales'))
             ->_title($_hlp->__('Dropship'))
             ->_title($_hlp->__('Edit brandshop settings'));

        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('zolagodropship/adminhtml_vendor_brandshop_edit'));
        $this->renderLayout();
    }
    public function brandshopSaveAction() {
        $request = $this->getRequest();
        $vendorId = $request->getParam('vendor_id');
        $brandshopId = $request->getParam('brandshop_id');
        
        $model = Mage::getModel('zolagodropship/vendor_brandshop');
        $model->loadByVendorBrandshop($vendorId,$brandshopId);
        $model->setDescription($request->getParam('description'));
        $model->setCanAsk($request->getParam('can_ask'));
        $model->setCanAddProduct($request->getParam('can_add_product'));
        try {
            $model->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('zolagodropship')->__('Settings has been saved.'));
        } catch (Exception $xt) {
            Mage::getSingleton('adminhtml/session')->addError($xt->getMessage());            
        }
        $this->_redirect('udropshipadmin/adminhtml_vendor/edit/',array('id'=>$vendorId,'active_tab' => 'brandshop_section'));
    }

    /**
     * add sizetable params to save process
     */
    public function saveAction() {
        if ( $this->getRequest()->getPost() ) {
            $r = $this->getRequest();
            $hlp = Mage::helper('zolagosizetable');
            try {
                $id = $r->getParam('id');
                if ($r->getParam('vendor_brand')) {
                    $this->_saveBrands(Zend_Json::decode($r->getParam('vendor_brand')),$id);
                }
                if ($r->getParam('vendor_attributeset')) {
                    $this->_saveAttributeSet(Zend_Json::decode($r->getParam('vendor_attributeset')),$id);                    
                }
                if ($r->getParam('vendor_kind')) {
                    $this->_saveKinds(Zend_Json::decode($r->getParam('vendor_kind')),$id);
                }

                //TODO
                if ($r->getParam('dhl_vendor')) {
                    $this->_saveDhlVendor(Zend_Json::decode($r->getParam('dhl_vendor')),$id);
                }

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($r->getParam('reg_id')) {
                    $this->_redirect('umicrositeadmin/adminhtml_registration/edit', array('reg_id'=>$r->getParam('reg_id')));
                    return;
                }
                $this->_redirect('*/*/edit', array('id' => $r->getParam('id')));
                return;
            }

        }
        return parent::saveAction();
    }
    
    
    /**
     * save parameters
     * @param array $params
     * @param int $vendor_id
     * @param string $model_name
     * @param string $key_name
     * @return 
     */
     protected function _saveParams($params,$vendor_id,$model_name,$key_name) {
        $model = Mage::getModel($model_name);
        $collection = $model->getCollection();
        $collection->getSelect()
            ->where('vendor_id = '.$vendor_id);
        $items = array();
        foreach ($collection as $item) {
            $items[$item->getData($key_name)] = $item;
        }
        foreach ($params as $key=>$param) {
            if (!is_numeric($key)) {
                continue;
            }
            if (isset($param['on'])) {
                if ($param['on']) { //add
                    if (!isset($items[$key])) {
                        $model->setData(array(
                            'vendor_id' => $vendor_id,
                            $key_name	=> $key,
                        ));
                        $model->save();                            
                    }
                } else { // remove
                    if (isset($items[$key])) {
                        $items[$key]->delete();
                        unset($items[$key]);
                    }
                }
            }
        }
         
     }
    /**
     * saving vendor permissions to brand
     * @param array $brands 
     * @param int $vendor_id
     */
     protected function _saveBrands($brands,$vendor_id) {
        $this->_saveParams($brands,$vendor_id,'zolagosizetable/vendor_brand','brand_id');
     }
    /**
     * saving vendor permissions to attribute sets
     * @param array $attributeset
     * @param int $vendor_id
     */
     protected function _saveAttributeSet($attributeset,$vendor_id) {
        $this->_saveParams($attributeset,$vendor_id,'zolagosizetable/vendor_attribute_set','attribute_set_id');
     }

    /**
     * saving vendor permissions to regulation kind
     * @param array $kinds
     * @param int $vendor_id
     */
    protected function _saveKinds($kinds,$vendor_id) {
        $this->_saveParams($kinds,$vendor_id,'ghregulation/regulation_vendor_kind','regulation_kind_id');
    }

    /**
     * saving vendor permissions to attribute sets
     * @param array $dhl
     * @param int $vendor_id
     */
    protected function _saveDhlVendor($dhl, $vendor_id)
    {
        $this->_saveParams($dhl, $vendor_id, 'ghdhl/dhl_vendor', 'dhl_id');
    }

}