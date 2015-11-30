<?php
/**
 *
 *	@copyright  Copyright (c) 2012-2013 SerwerSMS.pl
 *	http://www.serwersms.pl
 */

class SerwerSMS_Sms_RaportController extends Mage_Core_Controller_Front_Action{
    
    public function indexAction(){
        
    }
    
    public function odbierzAction(){

        $smsid = Mage::app()->getRequest()->getParam('smsid');
        $stan = Mage::app()->getRequest()->getParam('stan');
        $data = Mage::app()->getRequest()->getParam('data');
        
        if($smsid and $stan and $data){
            
            $collection = Mage::getModel('serwersms_model/SmsModel')->getCollection();
            $collection->addFieldToFilter('smsid', $smsid);
            
            foreach($collection as $item){
                $id = $item->getId();
            }
            
            $data = array('raport'=>$stan.' '.$data);
            $model2 = Mage::getModel('serwersms_model/smsModel')->load($id)->addData($data);
            try {
                    $model2->setId($id)->save();
                    echo "OK";

                } catch (Exception $e){
                    echo $e->getMessage();
            }
            
        } else {
            echo "ERROR";
        }
        
    }
}
?>