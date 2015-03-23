<?php

require_once 'abstract.php';

class Gh_Api_Shell extends Mage_Shell_Abstract {

    /**
     * Run script
     *
     * @return void
     */
    public function run() {
        require_once( $this->_getRootPath() . '/test/GH/Api/Model/SoapTest.php');

        $action = $this->getArg('action');
        if (empty($action)) {
            echo $this->usageHelp();
        } else {
            $actionMethodName = $action.'Action';
            if (method_exists($this, $actionMethodName)) {
                $this->$actionMethodName();
            } else {
                echo "Action $action not found!\n";
                echo $this->usageHelp();
                exit(1);
            }
        }
    }


    /**
     * Retrieve Usage Help Message
     *
     * @return string
     */
    public function usageHelp() {
        $help = 'Available actions: ' . "\n";
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (substr($method, -6) == 'Action') {
                $help .= '    -action ' . substr($method, 0, -6);
                $helpMethod = $method.'Help';
                if (method_exists($this, $helpMethod)) {
                    $help .= $this->$helpMethod();
                }
                $help .= "\n";
            }
        }
        return $help;
    }

    /**
     * doLogin($vendorId,$password,$webAPIKey)
     */
    public function doLoginAction() {
        $api = new GH_Api_Model_SoapTest();

        $vId = $this->getArg('vid');
        $passwd = $this->getArg('passwd');
        $key = $this->getArg('key');

        $api->doLogin($vId,$passwd,$key);
    }
    public function doLoginActionHelp() {
        return "use ex: php shell/ghapi.php -action doLogin -vid 5 -passwd testtest123 -key klucz";
    }



    /**
     * createUser($vendorId,$password)
     */
    public function addUserAction() {
        $vId = $this->getArg('vid');
        $passwd = $this->getArg('passwd');

        /** @var GH_Api_Model_User $user */
        $user = Mage::getModel('ghapi/user')->createUser($vId,$passwd);
        var_dump($user->getData());
    }
    public function addUserActionHelp() {
        return "use ex: php shell/ghapi.php -action addUser -vid 5 -passwd testtest123";
    }

    public function addMessageAction() {
        /** @var GH_Api_Model_Message $modelMSG */
        $modelMSG = Mage::getModel('ghapi/message');

        $poId = $this->getArg('poid');
        $msgType = $this->getArg('type');
        /** @var Zolago_Po_Model_Po $po */
        $po = Mage::getModel('zolagopo/po')->load($poId);

        if (empty($msgType)) {
            $modelMSG->addMessage($po, GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_NEW_ORDER);
            $modelMSG->addMessage($po, GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_CANCELLED_ORDER);
            $modelMSG->addMessage($po, GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_PAYMENT_DATA_CHANGED);
            $modelMSG->addMessage($po, GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_ITEMS_CHANGED);
            $modelMSG->addMessage($po, GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_DELIVERY_DATA_CHANGED);
            $modelMSG->addMessage($po, GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_INVOICE_ADDRESS_CHANGED);
            $modelMSG->addMessage($po, GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_STATUS_CHANGED);
        } else {
            var_dump($modelMSG->addMessage($po, $msgType));
        }
    }
    public function addMessageActionHelp() {
        return "use ex: php shell/ghapi.php -action addMessage -poid 30 [ -type newOrder|cancelledOrder|paymentDataChanged|itemsChanged|deliveryDataChanged|invoiceAddressChanged|statusChanged ]";
    }

    /**
     * getChangeOrderMessage($sessionToken,$messageBatchSize,$messageType)
     */
    public function getChangeOrderMessageAction() {
        $api = new GH_Api_Model_SoapTest();

        $token = $this->getArg('token');
        $size = $this->getArg('size') ? $this->getArg('size') : 10;
        $type = $this->getArg('type');

        if (empty($type)) {
            $api->getChangeOrderMessage($token, $size, null);
        } else {
            $api->getChangeOrderMessage($token, $size, $type);
        }
    }
    public function getChangeOrderMessageActionHelp() {
        return "use ex: php shell/ghapi.php -action getChangeOrderMessage -token xoxo -size 10 [ -type *|newOrder|cancelledOrder|paymentDataChanged|itemsChanged|deliveryDataChanged|invoiceAddressChanged|statusChanged ]";
    }


    /**
     * setChangeOrderMessageConfirmation($sessionToken,array $messageIDs)
     */
    public function setChangeOrderMessageConfirmationAction() {
        $api = new GH_Api_Model_SoapTest();
        $token = $this->getArg('token');
        $ids = $this->getArg('ids');
        $ids = explode(',', $ids);


        foreach ($ids as $id => $value) {
            $ids[$id] = (int)$value;
        }

        $api->setChangeOrderMessageConfirmation($token, $ids);
    }
    public function setChangeOrderMessageConfirmationActionHelp() {
        return "use ex: php shell/ghapi.php -action setChangeOrderMessageConfirmation -token xoxo -ids 12,13,15";
    }


}

$shell = new Gh_Api_Shell();
$shell->run();