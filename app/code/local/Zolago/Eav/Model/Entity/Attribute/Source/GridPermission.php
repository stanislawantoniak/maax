<?php

class Zolago_Eav_Model_Entity_Attribute_Source_GridPermission extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	const DO_NOT_USE		= 0;
	const USE_IN_FILTER		= 10;
	const DISPLAY			= 20;
	const EDITION			= 30;
	const INLINE_EDITION	= 40;
	
    /**
     * Get Attribute Grid Permission Options
     * 
     * @param booloean $withEmpty Add empty Value
     * 
     * @return array Array with Attribute Grid Permission Options
     */
    public function getAllOptions($withEmpty = true)
    {
		if (is_null($this->_options)) {
            $this->_options = array (		
                array (
                    'value' => self::DO_NOT_USE,
                    'label' => Mage::helper('zolagoeav')->__('Do not use'),
                ),
                array (
                    'value' => self::USE_IN_FILTER,
                    'label' => Mage::helper('zolagoeav')->__('Use in additional filter'),
                ),
                array (
                    'value' => self::DISPLAY,
                    'label' => Mage::helper('zolagoeav')->__('Display in Grid'),
                ),
                array (
                    'value' => self::EDITION,
                    'label' => Mage::helper('zolagoeav')->__('Edition'),
                ),
                array (
                    'value' => self::INLINE_EDITION,
                    'label' => Mage::helper('zolagoeav')->__('Inline Edition'),
                )
            );			
		}
		
        $options = $this->_options;

        if ($withEmpty) {
            array_unshift($options, array('label' => '', 'value' => ''));
        }
        return $options;
    }

}