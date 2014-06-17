<?php
class Zolago_Rma_Model_Observer{
	
	public function udropship_adminhtml_vendor_tabs_after($observer)
    {
        $block = $observer->getBlock();
        $block->addTab('return_reasons', array(
            'label'     => Mage::helper('zolagorma')->__('Return Reasons'),
            'content'   => Mage::app()->getLayout()->createBlock('zolagorma/adminhtml_dropship_edit_tab_returnreasons', 'vendor.returnreasons.form')
                ->toHtml()
        ));
    }
	
	/**
     * Save return reason for specific vendor
     *
     * @param $observer Varien_Event_Observer
	 * 
     * @return Zolago_Rma_Model_Observer
     */
	public function udropship_vendor_save_after($observer){
		
		$params = Mage::app()->getFrontController()->getRequest()->getParams();
		$vendor_return_reasons = $params['return_reasons'];
		$vendor = $observer->getVendor();
		
		if(sizeof($vendor_return_reasons) > 0){
			
			foreach($vendor_return_reasons as $vendor_return_reason_id => $data){
				
				$vendor_return_reason = Mage::getModel('zolagorma/vendorreturnreason')->load($vendor_return_reason_id);
				
				if($vendor_return_reason){
					try{
						$vendor_return_reason->updateModelData($data);
						$vendor_return_reason->save();
					}catch(Mage_Core_Exception $e){
			            Mage::logException($e);
			        }catch(Exception $e){
			            Mage::logException($e);
			        }
				}
			}
		}
		
		return $this;
	}
	
	/**
     * Add Return Reason to all vendors
     *
     * @param $observer Varien_Event_Observer
	 * 
     * @return Zolago_Rma_Model_Observer
     */	
	public function zolagorma_global_return_reson_save_after(Varien_Event_Observer $observer)
	{
		$helper = Mage::helper('zolagorma');
		
		$return_reason = $observer->getModel();
		
		$all_vendors = Mage::getModel('udropship/vendor')->getCollection();
		
		$vendors_count = $all_vendors->count();
		$ok_saved = 0;
		
		if($vendors_count > 0){
			
			// Add records for each vendor
			foreach($all_vendors as $vendor){
				
				try{
					$vendor_return_reason = Mage::getModel('zolagorma/vendorreturnreason');
					
					$data = array(
						'return_reason_id' => $return_reason->getReturnReasonId(),
	                	'vendor_id' => $vendor->getVendorId(),
	                	'auto_days' => $return_reason->getAutoDays(),
	              		'allowed_days' => $return_reason->getAllowedDays(),
	                	'message' => $return_reason->getMessage()
					);
					
					$vendor_return_reason->updateModelData($data);
					$vendor_return_reason->save();
					
					$ok_saved++;
				}catch(Mage_Core_Exception $e){
		            Mage::logException($e);
		        }catch(Exception $e){
		            Mage::logException($e);
		        }
				
			}
			
			$error_saved = $vendors_count - $ok_saved;
			
			if($ok_saved > 0){
				Mage::getSingleton('core/session')->addSuccess($helper->__("Records saved successfuly for {$ok_saved} vendors."));
			}
			if($error_saved > 0){
				Mage::getSingleton('core/session')->addError($helper->__("Records did not save successfuly for {$error_saved} vendors."));
			}
		}
		
		return $this;
	}
}
