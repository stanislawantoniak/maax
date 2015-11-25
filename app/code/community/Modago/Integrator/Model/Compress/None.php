<?php
/**
 * no compression
 */
class Modago_Integrator_Model_Compress_None 
    extends Modago_Integrator_Model_Compress {

    
    public function updatePath($filename) {
        return $filename;
    }
    
    public function compress($data) {
        return $data;
    }
    public function postprocessFile($filename) {
        return $filename;
    }
}