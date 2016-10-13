<?php

class Zolago_Pos_Dropship_PosController extends Zolago_Dropship_Controller_Vendor_Abstract {

	/**
	 * Pos listing action
	 */
	public function indexAction() {
		$this->_renderPage(null, 'zolagopos');
	}

	/**
	 * Pos Edit
	 */
	public function editAction() {
		$pos = $this->_registerModel();
		$vendor = $this->_getSession()->getVendor();

		// Existing pos - has vendor rights?
		if ($pos->getId() && !$pos->isAssignedToVendor($vendor)) {
			$this->_getSession()->addError(Mage::helper('zolagopos')->__("You can not edit this POS"));
			return $this->_redirect("*/*");
			// POS id specified, but post dons't exists
		} elseif (!$pos->getId() && $this->getRequest()->getParam("pos_id", null) !== null) {
			$this->_getSession()->addError(Mage::helper('zolagopos')->__("POS doesn't exists"));
			return $this->_redirect("*/*");
		}

		// Process request & session data 
		$sessionData = $this->_getSession()->getFormData();
		if (!empty($sessionData)) {
			$pos->addData($sessionData);
			$this->_getSession()->setFormData(null);
		}

		$this->_renderPage(null,'zolagopos');
	}

	/**
	 * New Pos
	 */
	public function newAction() {
		$this->_forward("edit");
	}
	
    /**
     * save vendor pos settings
     */
    public function settingsSaveAction() {
		$helper = Mage::helper('zolagopos');
		if (!$this->getRequest()->isPost()) {
			return $this->_redirectReferer();
		}
		// Form key valid?
		$formKey = Mage::getSingleton('core/session')->getFormKey();
		$formKeyPost = $this->getRequest()->getParam('form_key');
		if ($formKey != $formKeyPost) {
			return $this->_redirectReferer();
		}
		$vendor = $this->_getSession()->getVendor();
		$posId = $this->getRequest()->getParam('problem_pos_id');		
		try {
		    $pos = Mage::getModel('zolagopos/pos')->load($posId);
		    if (!$pos->getId()) {
		        Mage::throwException($helper->__('Pos does not exists'));
		    }
		    if (!$pos->getIsActive()) {
		        Mage::throwException($helper->__('Pos "%s" inactive',$pos->getName()));		        
		    }
		    if (!$pos->isAssignedToVendor($vendor)) {
		        Mage::throwException($helper->__('Pos not assigned to vendor'));
		    }
		    $vendor->setData('problem_pos_id',$posId);
	    	$vendor->save();
	    	$this->_getSession()->addSuccess($helper->__('Settings saved'));
        } catch (Mage_Core_Exception $xt) {
            $this->_getSession()->addError($xt->getMessage());            
        } catch (Exception $xt) {
            Mage::logException($xt);
            $this->_getSession()->addError($xt->getMessage());
        }
		$this->_redirect("*/pos",array("_fragment"=>"tab_1_2"));
    }

    public function check_dhlAction() {
        $posId = $this->getRequest()->getParam('pos_id');
        $settings = Mage::helper('udpo')->getDhlSettings(null,$posId);
        $mess = Mage::helper('orbashipping/carrier_dhl')->checkDhlSettings($settings);
        echo json_encode($mess);
    }

	/**
	 * Save Pos
	 */
	public function saveAction() {
		$helper = Mage::helper('zolagopos');
		$pos = $this->_registerModel();
		$vendor = $this->_getSession()->getVendor();

		// Has permission?
		if ($pos->getId() && !$pos->isAssignedToVendor($vendor)) {
			$this->_getSession()->addError($helper->__("You cannot edit this POS"));
			return $this->_redirectReferer();
		}

		// Try save
		$this->_getSession()->setFormData(null);
		$data = $this->getRequest()->getParams();
		$data["show_on_map"] = $this->getRequest()->getParam("show_on_map", 0);
		$data["is_available_as_pickup_point"] = $this->getRequest()->getParam("is_available_as_pickup_point", 0);
		$data["map_notes"] = $this->getRequest()->getParam("map_notes","");
		$data["map_time_opened"] = htmlentities($this->getRequest()->getParam("map_time_opened",""));

		$modelId = $this->getRequest()->getParam("pos_id");

		try {
			// Edit ?
			if (!empty($modelId) && !$pos->getId()) {
				throw new Mage_Core_Exception($helper->__("POS not found"));
			}
			$pos->addData($data);
			$validErrors = $pos->validate();
			if ($validErrors === true) {
				// Fix empty value
				if($pos->getId()==""){
					$pos->setId(null);
				}
				// Add stuff for new POS
				if(!$pos->getId()){
					// Set Vendor Owner
					$pos->setVendorOwnerId($vendor->getId());
					// Add relation
					$pos->setPostVendorIds(array($vendor->getId()));
				}
				$pos->save();
			} else {
				$this->_getSession()->setFormData($data);
				foreach ($validErrors as $error) {
					$this->_getSession()->addError($error);
				}
				return $this->_redirectReferer();
			}
			$this->_getSession()->addSuccess($helper->__("POS Saved"));
		} catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
			$this->_getSession()->setFormData($data);
			return $this->_redirectReferer();
		} catch (Exception $e) {
			$this->_getSession()->addError($helper->__("Some error occure"));
			$this->_getSession()->setFormData($data);
			Mage::logException($e);
			return $this->_redirectReferer();
		}
		return $this->_redirect("udropship/pos/edit/", array('pos_id' => $pos->getId()));
		
	}

	/**
	 * Register current model to use by blocks
	 * @return Zolago_Pos_Model_Pos
	 */
	protected function _registerModel() {
		$posId = $this->getRequest()->getParam("pos_id");
		$pos = Mage::getModel("zolagopos/pos");
		if ($posId) {
			$pos->load($posId);
		}
		// Default values for new model
		if (!$pos->getId()) {
			$pos->setDefaults();
		}
		Mage::register("current_pos", $pos);
		return $pos;
	}

}


