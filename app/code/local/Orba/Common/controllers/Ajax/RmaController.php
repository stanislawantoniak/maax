<?php

class Orba_Common_Ajax_RmaController extends Orba_Common_Controller_Ajax {

    /**
     * list of possible pickup data
     * @return json
     */
    public function getDateListAction(){


        $zip = $this->getRequest()->getParam('zip');

        $dateList = Mage::helper('zolagorma')->getDateList($zip);

        $arrayDateList = (array) $dateList;//only for easy counting

        $status = count($arrayDateList);

        $result = $this->_formatSuccessContentForResponse($dateList, $status);
        $this->_setSuccessResponse($result);
    }

}