<?php

/**
 * Class ZolagoOs_LoyaltyCard_Block_Vendor_Card_Grid
 *
 * @method ZolagoOs_LoyaltyCard_Model_Resource_Card_Collection getCollection()
 */
class ZolagoOs_LoyaltyCard_Block_Vendor_Card_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('zosloyaltycard_campaign_grid');
		$this->setDefaultSort('created_at');
		$this->setDefaultDir('desc');
		// Need
		$this->setGridClass('z-grid');
		$this->setTemplate("zolagoadminhtml/widget/grid.phtml");
	}

	protected function _prepareCollection() {
		/** @var Zolago_Dropship_Model_Vendor $vendor */
		$vendor = Mage::getSingleton('udropship/session')->getVendor();
		/** @var ZolagoOs_LoyaltyCard_Model_Resource_Card_Collection $collection */
		$collection = Mage::getResourceModel("zosloyaltycard/card_collection");
		$collection->addVendorFilter($vendor);

		$this->setCollection($collection);

		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {
		$helper = Mage::helper("zosloyaltycard");

		$this->addColumn("card_number", array(
			"type" => "text",
			"index" => "card_number",
			"class" => "form-control",
			"header" => $helper->__("Card number"),
		));

		// No select, just text filter
		$this->addColumn("card_type", array(
			"type" => "text",
			"index" => "card_type",
			"class" => "form-control",
			"header" => $helper->__("Card type"),
			"renderer" => Mage::getConfig()->getBlockClassName("zosloyaltycard/adminhtml_grid_column_renderer_card_type"),
		));

		$this->addColumn("first_name", array(
			"type" => "text",
			"index" => "first_name",
			"class" => "form-control",
			"header" => $helper->__("First name"),
		));

		$this->addColumn("surname", array(
			"type" => "text",
			"index" => "surname",
			"class" => "form-control",
			"header" => $helper->__("Surname"),
		));

		$this->addColumn("telephone_number", array(
			"type" => "text",
			"index" => "telephone_number",
			"class" => "form-control",
			"header" => $helper->__("Telephone number"),
		));
		
		$this->addColumn("email", array(
			"type" => "text",
			"index" => "email",
			"class" => "form-control",
			"header" => $helper->__("Email"),
		));

//		$this->addColumn("shop_code", array(
//			"type" => "text",
//			"index" => "shop_code",
//			"class" => "form-control",
//			"header" => $helper->__("Shop code"),
//		));

		$this->addColumn("created_at", array(
			"type" => "text",
			"index" => "created_at",
			"class" => "form-control",
			"header" => $helper->__("Created at"),
			"filter" => false,
		));

		$this->addColumn("expire_date", array(
			"type" => "text",
			"index" => "expire_date",
			"class" => "form-control",
			"header" => $helper->__("Expire date"),
			"filter" => false,
		));


		$this->addColumn("actions", array(
			'header' => $helper->__('Action'),
			'renderer' => Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_link"),
			'width' => '50px',
			'type' => 'action',
			'index' => 'card_id',
			'link_action' => "*/*/edit",
			'link_param' => 'id',
			'link_label' => $helper->__('Edit'),
			'link_target' => '_self',
			'filter' => false,
			'sortable' => false
		));
		
		return parent::_prepareColumns();
	}
	
	public function getRowUrl($item) {
		return null;
	}
	
	protected function _addColumnFilterToCollection($column) {
		$id = $column->getId();
		$value = $column->getFilter()->getValue();

		switch ($id) {
			case 'card_type':
				/** @var ZolagoOs_LoyaltyCard_Helper_Data $helper */
				$helper = Mage::helper("zosloyaltycard");
				$config = $helper->getLoyaltyCardConfig();
				$ids = array();
				foreach ($config as $id => $type) {
					if (stripos($type['name'], $value) !== FALSE) {
						$ids[] = $id;
					}
				}
				$this->getCollection()->addFieldToFilter('card_type', array('in' => $ids));
				return $this;
			default:
				parent::_addColumnFilterToCollection($column);
				return $this;
		}
	}
}