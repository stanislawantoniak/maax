<?php
/**
 * saving promo image
 */
require_once Mage::getModuleDir('controllers', "Mage_Adminhtml") . DS . "Promo/QuoteController.php";

class Zolago_Adminhtml_Promo_QuoteController extends Mage_Adminhtml_Promo_QuoteController {
    /**
     * overriding save action (promotion image)
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            try {
	            $id = $this->getRequest()->getParam('rule_id');
//                if(isset($_FILES['promo_image']['name']) and (file_exists($_FILES['promo_image']['tmp_name']))) {
//                    $uploader = new Varien_File_Uploader('promo_image');
//                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png')); // or pdf or anything
//                    $uploader->setAllowRenameFiles(false);
//                    $uploader->setFilesDispersion(false);
//                    $path = Mage::helper('zolagosalesrule')->getPromotionImagePath();
//                    if (!is_dir($path)) {
//                        mkdir($path);
//                    }
//	                $pre = $id."_".time()."_";
//                    $filename = $pre.$uploader->getCorrectFileName($_FILES['promo_image']['name']);
//                    $uploader->save($path,$filename);
//                    $data['promo_image'] = $filename;
//                } else {
//                    if(isset($data['promo_image']['delete']) && $data['promo_image']['delete'] == 1) {
//                        $data['promo_image'] = '';
//                    } else {
//                        unset($data['promo_image']);
//                    }
//                }
                /** @var $model Mage_SalesRule_Model_Rule */
                $model = Mage::getModel('salesrule/rule');
                Mage::dispatchEvent(
                    'adminhtml_controller_salesrule_prepare_save',
                    array('request' => $this->getRequest()));
                $data = $this->_filterDates($data, array('from_date', 'to_date'));
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        Mage::throwException(Mage::helper('salesrule')->__('Wrong rule specified.'));
                    }
                }

                $session = Mage::getSingleton('adminhtml/session');

                $validateResult = $model->validateData(new Varien_Object($data));
                if ($validateResult !== true) {
                    foreach($validateResult as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                    $session->setPageData($data);
                    $this->_redirect('*/*/edit', array('id'=>$model->getId()));
                    return;
                }

                if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent'
                && isset($data['discount_amount'])) {
                    $data['discount_amount'] = min(100,$data['discount_amount']);
                }
                if (isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                }
                if (isset($data['rule']['actions'])) {
                    $data['actions'] = $data['rule']['actions'];
                }
                unset($data['rule']);
                $model->loadPost($data);

                $useAutoGeneration = (int)!empty($data['use_auto_generation']);
                $model->setUseAutoGeneration($useAutoGeneration);

                $session->setPageData($model->getData());

                $model->save();
                $session->addSuccess(Mage::helper('salesrule')->__('The rule has been saved.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('rule_id');
                if (!empty($id)) {
                    $this->_redirect('*/*/edit', array('id' => $id));
                } else {
                    $this->_redirect('*/*/new');
                }
                return;

            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('catalogrule')->__('An error occurred while saving the rule data. Please review the log and try again.'));
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('rule_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }


}