<?php

class Snowdog_Freshmail_Model_System_Config_Backend_Customfields
    extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{
    protected function _beforeSave()
    {
        $value = $this->getValue();

        if (is_array($value)) {
            $errors = array();
            $sourceFields = array();
            foreach ($value as $key => $row) {
                if ($key == '__empty') {
                    continue;
                }
                if (!preg_match('/^[a-z][a-z_0-9]{1,254}$/', $row['target_field'])) {
                    $errors[] = Mage::helper('snowfreshmail')
                        ->__('Invalid tag format <em>%s</em> for the attribute <em>%s</em> in custom fields to sync'
                            , $row['target_field'], $row['source_field']);
                }
                if (in_array($row['source_field'], $sourceFields)) {
                    $errors[] = Mage::helper('snowfreshmail')->__('Attribute <em>%s</em> is duplicated in custom fields to sync', $row['source_field']);
                } else {
                    $sourceFields[] = $row['source_field'];
                }
            }
            if ($errors) {
                Mage::throwException(implode('<br />', $errors));
            }
        }

        parent::_beforeSave();
    }
}
