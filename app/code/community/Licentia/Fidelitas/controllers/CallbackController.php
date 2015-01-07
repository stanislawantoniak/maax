<?php

/**
 * Licentia Fidelitas - Advanced Email and SMS Marketing Automation for E-Goi
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * @title      Advanced Email and SMS Marketing Automation
 * @category   Marketing
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) 2012 Licentia - http://licentia.pt
 * @license    Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 */
class Licentia_Fidelitas_CallbackController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {

        $remove = $this->getRequest()->getPost('removeSubscriber');
        $add = $this->getRequest()->getPost('addSubscriber');
        $data = isset($remove) ? $remove : $add;
        $data = $this->_object2array(simplexml_load_string($data));
        $data = array_change_key_case($data);

        foreach ($data as $key => $value) {
            if (is_array($value) && count($value) == 0) {
                unset($data[$key]);
            }
        }

        $data['inCron'] = true;
        $data['inCallback'] = true;
        try {
            if ($add) {
                Mage::getModel('fidelitas/subscribers')->setData($data)->save();
            }

            if ($remove) {
                $result = Mage::getModel('fidelitas/subscribers')->getCollection()
                        ->addFieldToFilter('email', $data['email'])
                        ->addFieldToFilter('list', $data['list']);

                if ($result->count() > 0) {
                    foreach ($result as $item) {
                        $item->delete();
                    }
                }
            }
        } catch (Exception $e) {

        }

        Mage::log($data, 3, 'egoi-callback.log');
    }

    protected function _object2array($data) {
        if (!is_object($data) && !is_array($data)) {
            return $data;
        }

        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        return array_map(array($this, '_object2array'), $data);
    }

}
