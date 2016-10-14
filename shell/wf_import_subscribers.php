<?php

require_once 'shell/abstract.php';

class Wf_Import_Subscribers extends Mage_Shell_Abstract
{

    public function run()
    {

        $fileName = Mage::getBaseDir() . '/var/import/GetResponse.csv';


        //1. Reading file
        if (($fileContent = fopen($fileName, "r")) == FALSE) {
            echo "Error reading file {$fileName}" . "\n";
            return;
        }
        echo "Start reading file {$fileName} ...." . "\n";

        $oldStoreCustomers = [];
        $row = 0;
        while (($data = fgetcsv($fileContent)) !== FALSE) {
            $row++;

            if ($row == 1) {
                //skip header
                continue;
            }

            $email = $data[0];
            $mailing = $data[1];
            $account = $data[2];

            $oldStoreCustomers[] = "('{$email}',{$mailing},{$account})";
        }

        if (empty($oldStoreCustomers)) {
            echo "Valid data in file {$fileName} not found" . "\n";
            return;
        }
        echo "Start updating old customers DB table ...." . "\n";


        $oldStoreCustomersBatches = array_chunk($oldStoreCustomers, 200);

        //2. Put old customers to DB table `wfoldstorecustomer_customer`
        $resource = Mage::getSingleton('core/resource');
        $setup = Mage::getSingleton('core/resource_setup');
        $writeConnection = $resource->getConnection('core_write');

        $tableName = $setup->getTable('wfoldstorecustomer/customer');

        foreach ($oldStoreCustomersBatches as $oldStoreCustomersBatch) {
            $insertQuery = sprintf("INSERT IGNORE INTO  %s (email,is_subscribed,has_account_in_old_store) VALUES %s", $tableName, implode(",", $oldStoreCustomersBatch));
            $writeConnection->query($insertQuery);
        }

        echo "Start creating subscribers ...." . "\n";

        //3. Create subscribers
        $oldStoreCustomersCollection = Mage::getModel('wfoldstorecustomer/customer')
            ->getCollection();
        $oldStoreCustomersCollection->getSelect()->joinLeft(
            array('newsletter_subscriber' => $oldStoreCustomersCollection->getTable('newsletter/subscriber')),
            "newsletter_subscriber.subscriber_email=main_table.email",
            array()
        );
        $oldStoreCustomersCollection->addFieldToFilter("is_subscribed", 1);
        $oldStoreCustomersCollection->addFieldToFilter("newsletter_subscriber.subscriber_email", array("null" => TRUE));


        $storeId = 1;
        $subscriberStatus = Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED;  // Subscribed

        $subscriberData = [];
        foreach ($oldStoreCustomersCollection as $oldStoreCustomer) {
            $subscriberData[] = "({$storeId}, '" . $oldStoreCustomer->getEmail() . "', {$subscriberStatus})";
        }


        if (empty($subscriberData)) {
            echo "Customers for subscribing not found" . "\n";
            return;
        }


        //3.1 Select already existing magento store subscribers

        $tableSubscribersName = $setup->getTable('newsletter/subscriber');

        $insertQuery = sprintf("INSERT IGNORE INTO  %s (store_id,subscriber_email,subscriber_status) VALUES %s", $tableSubscribersName, implode(",", $subscriberData));
        $writeConnection->query($insertQuery);

    }
}


$obj = new Wf_Import_Subscribers();
$obj->run();