<?php
require_once Mage::getModuleDir('controllers', "ZolagoOs_OmniChannel") . DS . "Adminhtml". DS . "VendorController.php";
/**
 * new functions for admin_vendor controller
 */
class Zolago_Dropship_Adminhtml_VendorController extends ZolagoOs_OmniChannel_Adminhtml_VendorController {	
    
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
    public function kindEditAction() {
        $_hlp = Mage::helper('ghregulation');
        $this->_title($_hlp->__('Regulations'))
             ->_title($_hlp->__('Dropship'))
             ->_title($_hlp->__('Edit vendor documents'));
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('ghregulation/adminhtml_kind_edit_vendor'));
        $this->renderLayout();
    }

    /**
     * Send link to accept regulations
     */
    public function sendConfirmationEmailAction(){
        $request = $this->getRequest();
        $vendorId = $request->getParam('id');

        try {
            /* @var $vendor ZolagoOs_OmniChannel_Model_Vendor */
            $vendor = Mage::getModel('udropship/vendor')->load($vendorId);
            $docs = Mage::helper("ghregulation")->getDocumentsToAccept($vendor);
            if (empty($docs)) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper("zolagodropship")->__("Vendor does not have assigned documents"));
                return $this->_redirect('zolagoosadmin/adminhtml_vendor/edit/', array('id' => $vendorId));
            }

            $vendor->setConfirmation(md5(uniqid()));
            $vendor->setConfirmationSent(1);
            $localeTime = Mage::getModel('core/date')->timestamp(time());
            $localeTimeF = date("Y-m-d H:i:s", $localeTime);

            $vendor->setData("regulation_confirm_request_sent_date", $localeTimeF);
            Mage::getResourceSingleton('udropship/helper')
                ->updateModelFields(
                    $vendor,
                    array('confirmation', 'confirmation_sent', 'regulation_confirm_request_sent_date')
                );
            Mage::helper('udmspro')->sendVendorConfirmationEmail($vendor);

            Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('zolagodropship')->__('Regulation Accept Request sent.'));

        } catch (Exception $xt) {
            Mage::getSingleton('adminhtml/session')->addError($xt->getMessage());
            Mage::logException($xt);
        }
        $this->_redirect('zolagoosadmin/adminhtml_vendor/edit/',array('id'=>$vendorId));
    }
    /**
     * resetPassword
     */
    public function resetPasswordAction(){
        $request = $this->getRequest();
        $vendorId = $request->getParam('id');

        try {
            /* @var $vendor ZolagoOs_OmniChannel_Model_Vendor */
            $vendor = Mage::getModel('udropship/vendor')->load($vendorId);
            $vendor->setConfirmation(null);
            $password = Mage::helper('udmspro')->processRandomPattern('[AN*6]');
            $vendor->setPassword($password);
            $vendor->setPasswordEnc(Mage::helper('core')->encrypt($password));
            $vendor->setPasswordHash(Mage::helper('core')->getHash($password, 2));
            Mage::getResourceSingleton('udropship/helper')->updateModelFields($vendor, array('confirmation','password_hash','password_enc'));
            Mage::helper("umicrosite")->sendVendorWelcomeEmail($vendor);
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('zolagodropship')->__('Password reseted and sent to vendor via email.'));

        } catch (Exception $xt) {
            Mage::getSingleton('adminhtml/session')->addError($xt->getMessage());
            Mage::logException($xt);
        }
        $this->_redirect('zolagoosadmin/adminhtml_vendor/edit/',array('id'=>$vendorId));

    }
    public function kindSaveAction() {
        
        $request = $this->getRequest();
        $vendorId = $request->getParam('vendor_id');
        $type = $request->getParam('regulation_type_id');
        $date = $request->getParam('date');
        $model = Mage::getModel('ghregulation/regulation_document_vendor');
        $model->setData('vendor_id',$vendorId);
        $model->setData('date',$date);
        $model->setData('regulation_type_id',$type);
        try {
            $model->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ghregulation')->__('Document saved.'));
        } catch (Exception $xt) {
            Mage::getSingleton('adminhtml/session')->addError($xt->getMessage());            
            Mage::logException($xt);
        }
        $this->_redirect('zolagoosadmin/adminhtml_vendor/edit/',array('id'=>$vendorId,'active_tab' => 'regulation_type'));
        
    }
    
    /**
     * delete document type from vendor
     */
    public function typeDeleteAction() {
        $request = $this->getRequest();
        $vendorId = $request->getParam('vendor_id');
        $documentVendorId = $request->getParam('document_vendor_id');
        $model = Mage::getModel('ghregulation/regulation_document_vendor')->load($documentVendorId);
        try {
            $model->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ghregulation')->__('Document removed.'));
        } catch (Exception $xt) {
            Mage::getSingleton('adminhtml/session')->addError($xt->getMessage());            
            Mage::logException($xt);
        }
        $this->_redirect('zolagoosadmin/adminhtml_vendor/edit/',array('id'=>$vendorId,'active_tab' => 'regulation_type'));
                
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
        $model->setIndexByGoogle($request->getParam('index_by_google'));
        try {
            $model->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('zolagodropship')->__('Settings has been saved.'));
        } catch (Exception $xt) {
            Mage::getSingleton('adminhtml/session')->addError($xt->getMessage());            
        }
        $this->_redirect('zolagoosadmin/adminhtml_vendor/edit/',array('id'=>$vendorId,'active_tab' => 'brandshop_section'));
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

                //TODO
                if ($r->getParam('dhl_vendor')) {
                    $this->_saveDhlVendor(Zend_Json::decode($r->getParam('dhl_vendor')),$id);
                }

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($r->getParam('reg_id')) {
                    $this->_redirect('micrositeadmin/adminhtml_registration/edit', array('reg_id'=>$r->getParam('reg_id')));
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
     * saving vendor permissions to attribute sets
     * @param array $dhl
     * @param int $vendor_id
     */
    protected function _saveDhlVendor($dhl, $vendor_id)
    {
        $this->_saveParams($dhl, $vendor_id, 'ghdhl/dhl_vendor', 'dhl_id');
    }

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return
            Mage::getSingleton('admin/session')->isAllowed('sales/udropship/vendor') ||
            Mage::getSingleton('admin/session')->isAllowed('admin/vendors/vendor');
    }
}