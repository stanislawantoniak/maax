<?php
class Zolago_Customer_ConfirmController extends Mage_Core_Controller_Front_Action
{
    public function confirmAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
}
