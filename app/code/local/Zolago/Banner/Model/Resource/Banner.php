<?php

class Zolago_Banner_Model_Resource_Banner extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagobanner/banner', "banner_id");
    }

    public function saveBannerContent($content)
    {
        $content = $this->_prepareBannerContentToSave($content);
        $table = $this->getTable("zolagobanner/banner_content");
        $where = $this->getReadConnection()
            ->quoteInto("banner_id=?", $content['banner_id']);
        $this->_getWriteAdapter()->delete($table, $where);
        try {
            $this->_getWriteAdapter()->insert($table, $content);
        } catch(Mage_Core_Exception $e){
            Mage::logException($e);
        }
    }

    private function _prepareBannerContentToSave($content)
    {
        $serialize = array('image', 'caption');
        foreach ($content as $element => &$_) {
            if (in_array($element, $serialize)) {
                $_ = serialize($_);
            }
        }

        return $content;
    }

    public function getBannerContent($bannerId){
        $table = $this->getTable("zolagobanner/banner_content");
        $where = $this->getReadConnection()
            ->quoteInto("banner_id=?", $bannerId);
        $select = $this->_getReadAdapter()->select()
             ->from($table)
             ->where($where);

        $data = $this->_getReadAdapter()->fetchRow($select);

        return $data;
    }
}

