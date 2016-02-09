<?php

class Zolago_Sizetable_Dropship_SizetableController extends Zolago_Dropship_Controller_Vendor_Abstract
{

    protected $erroroccurred = false;

    /**
     * Simple function to get udropship session
     * @return Mage_Core_Model_Abstract
     */
    protected function _getSession()
    {
        return Mage::getSingleton('udropship/session');
    }

    /**
     * Sizetables listing display
     */
    public function indexAction()
    {
        $this->render();
    }

    /**
     * Sizetables edit/new form display
     */
    public function editAction()
    {
        $this->_prepareSizetable(true);
        $this->render();
    }

    /**git
     * Create new sizetable or edit existing one action
     * @return Mage_Core_Controller_Varien_Action
     */
    public function saveAction()
    {
        $session = $this->_getSession();
        $data = $this->getRequest()->getParams();

        /** @var Zolago_Sizetable_Helper_Data $helper */
        $helper = Mage::helper('zolagosizetable');
        try {
            if ($this->getRequest()->getPost()) {
                $formKey = Mage::getSingleton('core/session')->getFormKey();
                $formKeyPost = $this->getRequest()->getParam('form_key');
                if ($formKey != $formKeyPost) {
                    return $this->redirectSizetable();
                } else {
                    /** @var Zolago_Sizetable_Model_Sizetable $model */
                    $model = Mage::getModel("zolagosizetable/sizetable");
                    $modelId = $this->getRequest()->getParam("sizetable_id");
                    if ($modelId !== null) {
                        $model->load($modelId);
                        if (!$model->getId()) {
                            throw new Mage_Core_Exception($helper->__("Size table not found"));
                        } elseif ($model->getVendorId() != $this->getVendorId()) {
                            return $this->redirectSizetable();
                        }
                    }
                    $model->updateModelData($data);
                    $model->setPostData($data['sizetable']);

                    $model->save();

                    $session->addSuccess($helper->__("Size table saved"));
                }
            }
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
            $session->setFormData($data);
            return $this->redirectSizetable();
        } catch (Exception $e) {
            $session->addError($helper->__("Some error occurred"));
            $session->setFormData($data);
            Mage::logException($e);
            return $this->redirectSizetable();
        }
        return $this->redirectSizetable();
    }


    /**
     * Action to add new/overwrite existing sizetables assignments
     * @return Mage_Core_Controller_Varien_Action
     */
    public function assignAction()
    {
        $session = $this->_getSession();
        try {
            if ($this->getRequest()->getPost()) {
                $vid = $this->getVendorId();
                $data = $this->getRequest()->getParams();
                $formKey = Mage::getSingleton('core/session')->getFormKey();
                $formKeyPost = $this->getRequest()->getParam('form_key');
                if ($formKey != $formKeyPost) {
                    return $this->redirectSizetable();
                } else {
                    /** @var Zolago_Sizetable_Helper_Data $helper */
                    $helper = Mage::helper('zolagosizetable');
                    /** @var Zolago_Sizetable_Model_Sizetable_Rule $model */
                    $model = Mage::getModel("zolagosizetable/sizetable_rule");
                    if (isset($data["rule_id"]) && !empty($data["rule_id"])) {
                        $model->load($data["rule_id"]);
                        if (!$model->getId()) {
                            throw new Mage_Core_Exception($helper->__("Size table assignment not found"));
                        } elseif ($model->getVendorId() != $vid) {
                            throw new Mage_Core_Exception($helper->__("You don't have permission to edit this size table assignment"));
                        }
                    }

                    $data['vendor_id'] = $vid;
                    if (!isset($data['attribute_set_id']) || empty($data['attribute_set_id']))
                        $data['attribute_set_id'] = null;

                    if (!isset($data['brand_id']) || empty($data['brand_id']))
                        $data['brand_id'] = null;

                    $model->updateModelData($data);
                    $model->save();
                    $session->addSuccess($helper->__("Size table assignment saved"));
                }
            }
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
            return $this->redirectSizetable();
        } catch (Exception $e) {
            $session->addError($helper->__("Some error occurred"));
            Mage::logException($e);
            return $this->redirectSizetable();
        }
        return $this->redirectSizetable();
    }

    /**
     * Unified delete action for both sizetables and their assignments
     * I think that logic is self-explanatory
     */
    public function deleteAction()
    {
        $ruleId = $this->getRequest()->getParam("rule_id");
        $sizetableId = $this->getRequest()->getParam("sizetable_id");
        if (!empty($ruleId)) {
            $this->deleteRule($ruleId);
        } elseif (!empty($sizetableId)) {
            $this->deleteSizetable($sizetableId);
        }
        $this->redirectSizetable();
    }


    /**
     * Function that deletes sizetables assignments
     * @param $ruleId
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function deleteRule($ruleId)
    {
        $helper = Mage::helper('zolagosizetable');
        $successMsg = $helper->__("Size table assignment was deleted");
        $errorMsg = $helper->__("There was an error while deleting selected assignment");
        $model = Mage::getModel("zolagosizetable/sizetable_rule");
        return $this->delete($model, $ruleId, $successMsg, $errorMsg);
    }

    /**
     * Function that deletes sizetables
     * @param $sizetableId
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function deleteSizetable($sizetableId)
    {
        $helper = Mage::helper('zolagosizetable');
        $successMsg = $helper->__("Size table was deleted");
        $errorMsg = $helper->__("There was an error while deleting selected size table");
        $model = Mage::getModel("zolagosizetable/sizetable");
        return $this->delete($model, $sizetableId, $successMsg, $errorMsg);
    }

    /**
     * Unified delete function
     * @param $model
     * @param $modelId
     * @param $successMsg
     * @param $errorMsg
     * @return Mage_Core_Controller_Varien_Action
     * @throws Mage_Core_Exception | Exception
     */
    protected function delete($model, $modelId, $successMsg, $errorMsg)
    {
        $session = $this->_getSession();
        $helper = Mage::helper('zolagosizetable');
        try {
            if ($modelId) {
                $model = $model->load($modelId);
                if ($model && $model->getVendorId() == $this->getVendorId()) {
                    $model->delete();
                    $session->addSuccess($successMsg);
                } else {
                    throw new Mage_Core_Exception($errorMsg);
                }
            }
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $session->addError($helper->__("Some error occurred"));
            Mage::logException($e);
        }
        return $this->redirectSizetable();
    }

    protected function render()
    {
        $this->_renderPage(null, 'zolagosizetable');
    }

    /**
     * Setting $addScopes to true results in loading sizetable
     * per store translations and including it in return data
     * By default only default sizetable value is loaded
     * @param bool $addScopes
     * @return bool|false|Mage_Core_Model_Abstract
     */
    protected function _registerModel($addScopes = false)
    {
        $sizetableId = $this->getRequest()->getParam("sizetable_id");
        $sizetable = Mage::getModel("zolagosizetable/sizetable");
        if ($sizetableId) {
            $sizetable->load($sizetableId);
            if ($addScopes)
                $sizetable->getScopes();
        } else {
            $sizetable = false;
        }
        Mage::register("sizetable", $sizetable);
        return $sizetable;
    }

    protected function _prepareSizetable($addScopes = false)
    {
        $sizetable = $this->_registerModel($addScopes);
        if ($sizetable !== false) {
            // Existing sizetable - has venor rights?
            if ($sizetable->getSizetableId() && $sizetable->getVendorId() != $this->getVendorId()) {
                $this->_getSession()->addError(Mage::helper('zolagosizetable')->__("You cannot edit this size table"));
                return $this->redirectSizetable();
                // Sizetable id specified, but dons't exists
            } elseif (!$sizetable->getSizetableId() && $this->getRequest()->getParam("sizetable_id", null) !== null) {
                Mage::log(4);
                $this->_getSession()->addError(Mage::helper('zolagosizetable')->__("Size table doesn't exists"));
                return $this->redirectSizetable();
            }
        }
    }

    /**
     * Gets vendor id
     * @return mixed
     */
    public function getVendorId()
    {
        return $this->_getSession()->getVendor()->getVendorId();
    }

    /**
     * Simple redirect function, couldn't use _redirectReferer,
     * because it does not work with Magento admin grid
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function redirectSizetable()
    {
        return $this->_redirect("udropship/sizetable");
    }

    /**
     * Tiny mce image upload handler,
     * echoes json response on finish (needed by tinymce imageupload plugin)
     * @return void
     */
    public function imageAction()
    {
        if (isset($_FILES['image'])) {

            $images = $_FILES['image'];

            $tmpName = $images['tmp_name'];

            $imageName = $images['name'];

            $error_filetype = json_encode(array("error" => "filetype"));

            if (!empty($imageName)) {
                try {
                    $is_image = getimagesize($tmpName);
                    if (!$is_image) {
                        echo $error_filetype;
                        return;
                    }
                } catch (Exception $e) {
                    Mage::logException($e);
                    echo $error_filetype;
                    return;
                }
                $uniqueName = uniqid() . "_" . $imageName;

                $folder = $this->getVendorId() . "/";
                $folder_path = Mage::getBaseDir() . "/media/sizetables/" . $folder;
                if (!is_dir($folder_path)) {
                    mkdir($folder_path, 0777, true);
                }
                $return_path = Mage::getUrl("media/sizetables") . $folder . $uniqueName;
                $path = $folder_path . $uniqueName;
                try {
                    move_uploaded_file($tmpName, $path);
                    echo json_encode(array("error" => false, "path" => $return_path));
                } catch (Exception $e) {
                    Mage::logException($e);
                    echo json_encode(array("error" => "unknown"));
                }
            }
        }
    }
}