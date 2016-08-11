<?php
/**
 * tar.gz compression 
 */
class Modago_Integrator_Model_Compress_Tar
    extends Modago_Integrator_Model_Compress {

    // which function is used to exec
    protected $_exec = 'exec';
    
    public function updatePath($filename) {
        return $filename;
    }
    
    public function compress($data) {
        return $data; 
    }
    public function postprocessFile($filename) {
        $function = sprintf('tar -czfv %s%s %s',$filename,'.tar.gz',$tmpFile);
        $this->_exec($function);
        return $filename.'.tar.gz';
    }
    public function setExecMethod($method) {
        $this->_exec = $method;
    }
}