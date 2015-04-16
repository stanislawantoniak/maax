<?php
class TestController {
    // get test methods from class
    protected function getMethods($className) {
        if (!class_exists($className)) {
            return array();
        }
        $methods  = get_class_methods($className);
        $pattern = '/^'.TEST_FUNCTION_PREFIX.'([_0-9A-Za-z]+)/';
        $testMethods = array();
        foreach ($methods as $method) {
            $matches = array();
            if (preg_match($pattern,$method,$matches)) {
                $testMethods[] = $matches[0];
            }
        }
        return $testMethods;        
    }
    // get class from file
    protected function getClass($file) {
            $text = file_get_contents($file);
            $matches = array();
            $pattern = '/class ([_a-zA-Z0-9]+) extends ([_a-zA-Z0-9]+)_TestCase([^_A-Za-z0-9])/';
            if (preg_match($pattern,$text,$matches)) {	
                return $matches[1];
            }
        return false;        
    }
    // scan files and get test classess
    protected function getClasses($files) {
        $class = array();
        foreach ($files as $file) {
            if ($className = $this->getClass($file)) {	
                require_once($file);
                $methods = $this->getMethods($className);
                $class[$className] = array (
                    'methods' => $methods,
                    'file' => $file,
                );
            }
        }    
        return $class;    
    }
    // read directory
    protected function scandir($directory,&$files) {
        $list = array_diff(scandir($directory), array('..', '.'));
        foreach ($list as $file) {
            if (is_dir($directory.$file)) {
                $this->scandir($directory.$file.'/',$files);
            } else {
                $matches = array();
                if (preg_match('/'.TEST_FILE_SUFFIX.'\.php$/',$file,$matches)) {
                    $files[] = $directory.$file;
                }
            }
        }
    }
    // default action - tests list
    protected function getTestList() {
        $dir = TEST_DIR;
        $files = array();
        $this->scandir($dir,$files);
        $classes = $this->getClasses($files);
        ob_start();
        include(TEST_WWW.'testList.phtml');
        $body = ob_get_clean();
        return $body;
    }
    // execute phpunit test
    protected function executeTest() {
        $class = $_POST['classname'];
        $method = $_POST['method'];
        $file = 'json_'.time().'.out';
        $command = sprintf('php %s --log-json %s --filter %s %s',TEST_PHPUNIT,$file,$method,$class);
        $output = '';
        exec($command,$output);
        $data = trim(file_get_contents($file));
        $data = substr($data,1);
        $data = substr($data,0,-1);
        $data = explode('}{',$data);
        unlink($file);
        $result = json_decode('{'.$data[2].'}');
        if ($result->status == 'pass') {
            $out = '<span class="label label-success">Pass</span>';
        } else {
            $out = '<span class="label label-danger">'.$result->message.'</span>';
        }
        echo $out;
        die();
    }
    protected function getBody() {
        $action = empty($_GET['action'])? '':$_GET['action'];
        $body = '';
        switch ($action) {
            case 'execute':
                $this->executeTest();
                break;
            default:
                $body = $this->getTestList();                
        }
        return $body;
    }
    public function display() {
        $host = $_SERVER['SERVER_NAME'];
        $body = $this->getBody();
        include(TEST_WWW.'body.phtml');
    }
    
}
?>