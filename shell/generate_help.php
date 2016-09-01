<?php
/**
 * HOWTO: 
 * php shell/generate_help.php > foo.php
 * move foo.php on new serwer to shell/foo.php
 * php shell/foo.php
 * rm shell/foo.php
 *
 */

require_once 'shell/abstract.php';

class Modago_Test_Shell2 extends Mage_Shell_Abstract {

    public function run() {

        $resource = Mage::getSingleton('core/resource');

        $readConnection = $resource->getConnection('core_read');

        $tableName = $resource->getTableName('cms/block');

        $query = 'SELECT title,identifier,content FROM ' . $tableName .' WHERE identifier like "zolagoos-help%"';
        $results = $readConnection->fetchAll($query);
        echo '<?php'.PHP_EOL;
        echo "require_once 'shell/abstract.php';".PHP_EOL;
        echo 'class Modago_Helper_Installer extends Mage_Shell_Abstract {'.PHP_EOL;
        echo ' public function run() {'.PHP_EOL;
        echo '$blocks = array();'.PHP_EOL;
        foreach ($results as $item) {
            echo '$blocks[] = array ('.PHP_EOL;
            echo '	"title" => "'.$item['title'].'",'.PHP_EOL;
            echo '	"identifier" => "'.$item['identifier'].'",'.PHP_EOL;
            echo '  "content" => '.PHP_EOL;
            echo '<<<EOT'.PHP_EOL;
            echo $item['content'].PHP_EOL;
            echo 'EOT'.PHP_EOL;
            echo ','.PHP_EOL;
            echo '	"is_active" => 1,'.PHP_EOL;
            echo '	"stores" => 0,'.PHP_EOL;
            echo ');'.PHP_EOL;
        }

        echo '
        foreach ($blocks as $blockData) {
            $collection = Mage::getModel("cms/block")->getCollection();
            $collection->addFieldToFilter("identifier",$blockData["identifier"]);
            $currentBlock = $collection->getFirstItem();
            if ($currentBlock->getBlockId()) {
                $oldBlock = $currentBlock->getData();
                $blockData = array_merge($oldBlock, $blockData);
            }
            $currentBlock->setData($blockData)->save();
        }

        '.PHP_EOL;
        echo '	}
}'.PHP_EOL;
        echo '$obj = new Modago_Helper_Installer();'.PHP_EOL;
        echo '$obj->run();'.PHP_EOL;


    }
}


$obj = new Modago_Test_Shell2();
$obj->run();