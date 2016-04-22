<?php
/**
  
 */

abstract class ZolagoOs_OmniChannel_Model_Source_Abstract extends Varien_Object
{
    abstract public function toOptionHash($selector=false);

    public function toOptionArray($selector=false)
    {
        $arr = array();
        foreach ($this->toOptionHash($selector) as $v=>$l) {
            if (!is_array($l)) {
                $arr[] = array('label'=>$l, 'value'=>$v);
            } else {
                $options = array();
                foreach ($l as $v1=>$l1) {
                    $options[] = array('value'=>$v1, 'label'=>$l1);
                }
                $arr[] = array('label'=>$v, 'value'=>$options);
            }
        }
        return $arr;
    }

    public function getOptionLabel($value)
    {
        $options = $this->toOptionHash();
        if (is_array($value)) {
            $result = array();
            foreach ($value as $v) {
                $result[$v] = isset($options[$v]) ? $options[$v] : $v;
            }
        } else {
            $result = isset($options[$value]) ? $options[$value] : $value;
        }
        return $result;
    }
}
