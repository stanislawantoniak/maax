<?php
class Zolago_Adminhtml_Block_Catalog_Product_Attribute_Set_Main extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Set_Main
{
    protected function _construct()
    {
        $this->setTemplate('zolagoadminhtml/catalog/product/attribute/set/main.phtml');
    }
	
    /**
     * Prepare Global Layout
     *
     * @return Zolago_Adminhtml_Block_Catalog_Product_Attribute_Set_Main
     */
    protected function _prepareLayout()
    {
		$this->setChild('save_and_edit_button',
			$this->getLayout()->createBlock('adminhtml/widget_button')
				->setData(array(
					'label'     => Mage::helper('zolagoadminhtml')->__('Save and Continue'),
					'onclick'   => 'editSet.saveAndContinueEdit(\''.$this->getSaveAndContinueUrl().'\')',
					'class' => 'save'
				))
		);
		
		parent::_prepareLayout();
	}
	
    public function getSaveAndEditButtonHtml()
    {
        return $this->getChildHtml('save_and_edit_button');
    }	
	
    public function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save');
    }
	
    /**
     * Retrieve Attribute Set Save URL
     *
     * @return string
     */
    public function getMoveAndContinueUrl()
    {
        return $this->getUrl('*/catalog_product_set/save', array(
			'id'		=> $this->_getSetId(),
			'saveAndEdit'	=> true)
		);
    }	
	
	public function getAllAttributeSets($withEmpty = true)
	{
		return Mage::getModel('zolagoeav/entity_attribute_source_set')->getAllOptions($withEmpty);
	}
	
	protected function _getReloadTreeId()
	{
		return $this->getRequest()->getParam('reloadTree', false);
	}


	/**
     * Retrieve Unused in Attribute Set Attribute Tree as JSON
     *
     * @return string
     */
    public function getExtendedAttributeTreeJson($selectedSetId = false)
    {
		if (!$selectedSetId) {
			return $this->getAttributeTreeJson();
		}
		
        $items = array();
        $setId = $this->_getSetId();

        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
			->setAttributeSetFilter($setId)
            ->load();

        $attributesIds = array('0');
        /* @var $item Mage_Eav_Model_Entity_Attribute */
        foreach ($collection->getItems() as $item) {
            $attributesIds[] = $item->getAttributeId();
        }

        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
			->setAttributesExcludeFilter($attributesIds)
            ->addFieldToFilter('set_id', array('eq' => $selectedSetId))
            ->addVisibleFilter()
            ->load();

        foreach ($attributes as $child) {
            $attr = array(
                'text'              => $child->getAttributeCode(),
                'id'                => $child->getAttributeId(),
                'cls'               => 'leaf',
                'allowDrop'         => false,
                'allowDrag'         => true,
                'leaf'              => true,
                'is_user_defined'   => $child->getIsUserDefined(),
                'is_configurable'   => false,
                'entity_id'         => $child->getEntityId()
            );

            $items[] = $attr;
        }

        if (count($items) == 0) {
            $items[] = array(
                'text'      => Mage::helper('catalog')->__('Empty'),
                'id'        => 'empty',
                'cls'       => 'folder',
                'allowDrop' => false,
                'allowDrag' => false,
            );
        }

        return Mage::helper('core')->jsonEncode($items);
    }
}