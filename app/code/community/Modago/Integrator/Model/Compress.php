<?php
/**
 * abstract function for compression
 */
abstract class Modago_Integrator_Model_Compress {
    
    /**
     * change filename
     *
     * @param string $filename
     * @return string
     */
     abstract public function updatePath($filename);
     
    /**
     * compression data
     *
     * @param string $data
     * @return string
     */
     abstract public function compress($data);
     
    /**
     * postprocess file
     *
     * @param string $filename
     * @return string
     */
    abstract public function postprocessFile($filename);     
}