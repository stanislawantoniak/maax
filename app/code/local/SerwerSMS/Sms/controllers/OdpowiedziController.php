<?php
/**
 *
 *	@copyright  Copyright (c) 2012-2013 SerwerSMS.pl
 *	http://www.serwersms.pl
 */

class SerwerSMS_Sms_OdpowiedziController extends Mage_Core_Controller_Front_Action{
    
    public function indexAction(){
        
    }
    
    public function odbierzAction(){

        $numer = Mage::app()->getRequest()->getParam('numer');
        $wiadomosc = Mage::app()->getRequest()->getParam('wiadomosc');
        $data = Mage::app()->getRequest()->getParam('data');
        
        if($numer and $wiadomosc and $data){
            
            
            $data = array('data'=>$data, 'numer'=>$numer,'wiadomosc'=>$wiadomosc);
            $model = Mage::getModel('serwersms_model/odpowiedziModel')->addData($data);
            try {
                    $model->save();
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