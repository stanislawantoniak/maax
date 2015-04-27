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


}