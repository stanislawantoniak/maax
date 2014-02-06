<?php
class Zolago_Customer_Helper_Data extends Mage_Core_Helper_Abstract {
    public function generateToken() {
        return hash("sha256", uniqid(microtime()));
    }
}