<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Mysql4_Comment_Notification extends Magpleasure_Common_Model_Resource_Abstract
{

    public function _construct()
    {    
        $this->_init('mpblog/comment_notification', 'notification_id');
        $this->setUseUpdateDatetimeHelper(true);
    }

}