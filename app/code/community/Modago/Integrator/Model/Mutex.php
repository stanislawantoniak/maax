<?php
/**
 * semaphore for concurrent process
 */
class Modago_Integrator_Model_Mutex {
    protected $_file;
    /**
     * init mutex
     *
     * @param string $name
     * @return 
     */
     public function init($name) {
         $file = Mage::getBaseDir('var').DS.$name;
         if (!file_exists($name)) {
             if (!$tmp = fopen($file,'w')) {
                 Mage::throwException(sprintf('Cant create mutex file: %s',$file));
             }
             fclose($tmp);
         }
         if (!$this->_file = fopen($file,'r')) {
             Mage::throwException(sprintf('Cant access mutex file %s',$file));
         }
         return $this;
     }
     
    /**
     * lock mutex
     * @param bool $process_lock
     * @return bool
     */
     public function lock($process_lock = false) {
         $flag = $process_lock? LOCK_EX : (LOCK_EX | LOCK_NB);
         if (!$this->_file) {
             Mage::throwException('Mutex not init');
         } 
         return flock($this->_file,$flag);
     }
     
     
    /**
     * unlock mutex
     */
     public function unlock() {
         if (!$this->_file) {
             Mage::throwException('Mutex not init');
         }
         return flock($this->_file,LOCK_UN);
     }
    /**
     * destructor
     */
     public function __destruct() {
         if ($this->_file) {
             fclose($this->_file);
         }
     }
}