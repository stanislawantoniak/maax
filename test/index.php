<?php
define('TEST_DIR',getcwd().'/');
define('TEST_FILE_SUFFIX','Test');
define('TEST_FUNCTION_PREFIX','test');
define('TEST_WWW',TEST_DIR.'www/');
define('TEST_PHPUNIT',TEST_DIR.'phpunit.phar');
include(TEST_DIR.'init.php');
include(TEST_WWW.'controller.php');
class TestMain {
    public function main() {
        $controller = new TestController();
        $controller->display();        
    }
}

$obj = new TestMain();
$obj->main();
