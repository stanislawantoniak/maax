<?php
/**
 * controller for vendors and brandshops ajax
 */
 class Orba_Common_Ajax_VendorController extends Orba_Common_Controller_Ajax {
     
    /**
     * vendors assigned to brandshop by can_ask questions
     */
     public function can_askAction() {
         $brandshopId = $this->getRequest()->getParam('brandshop_id');
         if (!$brandshopId) {
             return null;
         }
         $model = Mage::getModel('zolagodropship/vendor_brandshop');
         $collection = $model->getCollection();
         $select = $collection->getSelect();
         $adapter = $select->getAdapter();
         $name = Mage::getSingleton('core/resource')->getTableName('udropship/vendor');
         $select->join(
             array(
                 'vendor' => $name,
             ),
             'main_table.vendor_id = vendor.vendor_id',
             array (
                 'vendor_name' => 'vendor_name',
             )           
         )->where(
             'main_table.brandshop_id=?',$brandshopId
         )->where(
             'main_table.can_ask = TRUE'
         )->where(
             'vendor.status = \'A\''
         );
         $out = array();
         foreach ($collection as $vendor) {
             $out[$vendor->getData('vendor_id')] = $vendor->getData('vendor_name');
         }
         if (!$out) {
             $response = sprintf('<input type="hidden" name="question[vendor_id]" value="%s" class="ajax_content"/>',$brandshopId);
         } else {
             $response = '<li class="wide ajax_content" id="vendor_select">
						<div class="form-group select-box-it-select-container">
                            <select name="question[vendor_id]" id="vendor_id" class="form-control required-entry select-box-it-select" required>';
            if (count($out)>1) {                            
                $response .= '<option value="">'.Mage::helper('zolagodropship')->__('Select vendor').'</option>';
            }
            foreach ($out as $id=>$name) {
                $response .= sprintf('<option value="%s">%s</option>',$id,$name);
            }
            $response .= '</select>
						</div>
					</li>';
         }
         echo $response;
     }
 }
