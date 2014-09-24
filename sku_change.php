<?php

include_once 'app/Mage.php';

Mage::app();

 

$updates_file="/var/www/modago/var/import/sku2sku.csv";

 

$sku_entry=array();

 

$updates_handle=fopen($updates_file, 'r');

if($updates_handle) {

    while($sku_entry=fgetcsv($updates_handle, 1000, ";")) {

        $old_sku=$sku_entry[0];

        $new_sku=$sku_entry[1];

        echo "\n<br ?-->Updating ".$old_sku." to ".$new_sku." - ";

        try {

            $get_item = Mage::getModel('catalog/product')->loadByAttribute('sku', $old_sku);

            if ($get_item) {

                $get_item->setSku($new_sku)->save();

                echo "successful";

            } else {

                echo "item not found";

            }

        } catch (Exception $e) {

            echo "Cannot retrieve products from Magento:".$e->getMessage()."
";

            return;

        }

    }

}

 

fclose($updates_handle);

 