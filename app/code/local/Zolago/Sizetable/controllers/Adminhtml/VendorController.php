<?php
/**
 * sizetable save params
 */

require_once Mage::getModuleDir('controllers', "Unirgy_Dropship") . DS . "Adminhtml" . DS . "VendorController.php";

class Zolago_Sizetable_Adminhtml_VendorController extends Unirgy_Dropship_Adminhtml_VendorController {

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
     * @return 
     */
     protected function _saveBrands($brands,$vendor_id) {
        $this->_saveParams($brands,$vendor_id,'zolagosizetable/vendor_brand','brand_id');
     }
    /**
     * saving vendor permissions to attribute sets
     * @param array $attributesets
     * @param int $vendor_id
     * @return 
     */
     protected function _saveAttributeSet($attributeset,$vendor_id) {
        $this->_saveParams($attributeset,$vendor_id,'zolagosizetable/vendor_attribute_set','attribute_set_id');
     }

}