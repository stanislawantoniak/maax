<?php
/**
 * override for email info
 */
class Zolago_Common_Model_Core_Email_Info extends Mage_Core_Model_Email_Info {
    
    /**
     * reply to email value
     */

    protected $_replyTo;
    
    
    /**
     * reply_to setter 
     *
     * @param string $email
     */
     public function setReplyTo($email) {
         $this->_replyTo = $email;
     }
     
    /**
     * reply_to getter
     * 
     * @return string
     */
     public function getReplyTo() {
         return $this->_replyTo;
     }


}