<?php
class Zolago_Customer_Helper_Data extends Mage_Core_Helper_Abstract {
    public function generateToken() {
        return hash("sha256", uniqid(microtime()));
    }

    public function getPasswordMinLength() {
        return 6; //it's hardcoded in app/code/core/Mage/Customer/Model/Customer/Attribute/Backend/Password.php
    }
}