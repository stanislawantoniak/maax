<?php
/**
 * bzip2 compression
 */
class Modago_Integrator_Model_Compress_Bzip 
    extends Modago_Integrator_Model_Compress {

    
    public function updatePath($filename) {
        return $filename.'.bz2';
    }
    
    public function compress($data) {
        return bzcompress($data,9); // max compression
    }
    public function postprocessFile($filename) {
        return $filename.'.bz2';
    }
}