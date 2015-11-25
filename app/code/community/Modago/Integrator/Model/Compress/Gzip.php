<?php
/**
 * gzip compression
 */
class Modago_Integrator_Model_Compress_Gzip 
    extends Modago_Integrator_Model_Compress {

    
    public function updatePath($filename) {
        return $filename.'.tar.gz';
    }
    
    public function compress($data) {
        return gzcompress($data,9); // max compression
    }
    public function postprocessFile($filename) {
        return $filename.'.tar.gz';
    }
}