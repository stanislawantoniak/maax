<?php

/**
 * Block with budget and marketing costs
 *
 * Class GH_Marketing_Block_Dropship_Marketing
 */
class GH_Marketing_Block_Dropship_Marketing extends Mage_Core_Block_Template {

	/**
	 * Retrieve html of <select> with budget dates (yyyy-mm)
	 *
	 * @return string
	 */
	public function getDateSelect() {	    
		/** @var GH_Marketing_Helper_Data $helper */
		$helper = Mage::helper('ghmarketing');
		$dates = $this->getDates();
		$html  = "<select id='budget-date' class='form-control' data-base='".$this->getUrl('*/*/*')."'>";
		foreach ($dates as $date) {
			$html .= sprintf("<option value='%s'%s>%s</option>",$date['date'],($date['selected']? ' selected':''),$date['date']) ;
		}
		$html .= "<select>";
		return $html;
	}

	/**
	 * TODO description
	 *
	 * @return array
	 */
	public function getDates() {
	    $selectedMonth = $this->getRequest()->getParam('month',date('Y-m'));
	    $vendor = $this->getVendor();
	    $date = $vendor->getRegulationAcceptDocumentDate();
	    if ($date) {
	        $begin = date('Y-m-01',strtotime($date));
        } else {
            $begin = date('Y-m-01');
        }
        $list = array();
        while (strtotime($begin) <= strtotime(date('Y-m-01'))) {
            $shortDate = date('Y-m',strtotime($begin));
            $list[] = array(
                'date' => $shortDate, 
                'selected' => ($shortDate == $selectedMonth)? true:false,
            );
            $begin = date('Y-m-d',strtotime("$begin +1 month"));
        }
        return array_reverse($list);
	}

	/**
	 * Retrieve html of <thead> with marketing cost types
	 *
	 * @return string
	 */
	public function getThead() {
		$html = '<thead><tr role="row">';
		$marketingCostTypes = $this->getMarketingCostTypes();
		$html .= "<th class='type-code-empty' role='columnheader' rowspan='1' colspan='1'></th>";
		foreach ($marketingCostTypes as $type) {
			/** @var GH_Marketing_Model_Marketing_Cost_Type $type */
			$html .= "<th class='type-code-{$type->getCode()}' data-id='{$type->getMarketingCostTypeId()}' data-code='{$type->getCode()}' role='columnheader' rowspan='1' colspan='1'>{$type->getName()}</th>";
		}
		$html .= '</thead></tr>';
		return $html;
	}

	/**
	 * Retrieve collection of marketing cost types
	 *
	 * @return GH_Marketing_Model_Resource_Marketing_Cost_Type_Collection
	 */
	public function getMarketingCostTypes() {
		/** @var GH_Marketing_Model_Resource_Marketing_Cost_Type_Collection $collection */
		$collection = Mage::getResourceModel("ghmarketing/marketing_cost_type_collection");
		return $collection;
	}

	/**
	 * Retrieve attributes sets for current vendor
	 *
	 * @return array()
	 */
	public function getCategories() {
		/** @var Zolago_Catalog_Model_Resource_Vendor_Mass $model */
		$model = Mage::getResourceSingleton('zolagocatalog/vendor_mass');
		$categories = $model->getAttributeSetsForVendor($this->getVendor());
		return $categories;
	}

	public function getTbody() {
		$html = "<tbody>";
		// Rows with current costs in categories for marketing costs types + Total row + Budget row
		$rows = $this->getRows();
		foreach ($rows as $row) {
			$html .= $this->makeSingleElement('tr', $row['data'], null, false);
			foreach ($row['cells'] as $cell) {
				$html .= $cell;
			}
			$html .= "</tr>";
		}
		$html .= "</tbody>";
		return $html;
	}

	/**
	 * TODO description
	 *
	 * @return array
	 */
	public function getRows() {

		/** @var GH_Marketing_Helper_Data $helper */
		$helper = Mage::helper('ghmarketing');

		$categories = $this->getCategories();
		$costTypes  = $this->getMarketingCostTypes();
		$store = Mage::app()->getStore();
		$rows = array();
		$totals = array();
		// init totals
		foreach ($costTypes as $type) {
			/** @var GH_Marketing_Model_Marketing_Cost_Type $type */
			$totals[$type->getMarketingCostTypeId()] = 0;
		}

		// Rows with costs corresponding to category and marketing cost type
		foreach ($categories as $value => $label) {
			$row = array('data' => array(), 'cells' => array());
			$row['cells'][] = $this->makeSingleElement('td', array(), $label);
			foreach ($costTypes as $type) {
				/** @var GH_Marketing_Model_Marketing_Cost_Type $type */
				$value = rand(10, 100);
				$totals[$type->getMarketingCostTypeId()] += $value;
				$row['cells'][] = $this->makeSingleElement('td', array(), $store->formatPrice($value));
			}
			$rows[] = $row;
		}

		// Empty row for better look
		$row = array('data' => array(), 'cells' => array());
		$row['cells'][] = $this->makeSingleElement('td', array('class' => 'empty-row'), '');
		foreach ($costTypes as $type) {
			/** @var GH_Marketing_Model_Marketing_Cost_Type $type */
			$row['cells'][] = $this->makeSingleElement('td', array(), '');
		}
		$rows[] = $row;

		// Total row
		$row = array('data' => array(), 'cells' => array());
		$row['cells'][] = $this->makeSingleElement('td', array('class' => 'total-row'), $helper->__("Total"));
		foreach ($costTypes as $type) {
			/** @var GH_Marketing_Model_Marketing_Cost_Type $type */
			$value = $totals[$type->getMarketingCostTypeId()];
			$row['cells'][] = $this->makeSingleElement('td', array(), $store->formatPrice($value));
		}
		$rows[] = $row;

		// Budget row
		$row = array('data' => array(), 'cells' => array());
		$row['cells'][] = $this->makeSingleElement('td', array('class' => 'budget-row'), $helper->__("Budget"));
		foreach ($costTypes as $type) {
			/** @var GH_Marketing_Model_Marketing_Cost_Type $type */
			$value = rand(10, 1000);
			$input = $this->makeSingleElement('input', array('name' => "budget[{$type->getMarketingCostTypeId()}]",'class' => 'form-control', 'type' => 'text', 'placeholder' =>  $store->formatPrice($value, false)));
			$row['cells'][] = $this->makeSingleElement('td', array(), $input);
		}
		$rows[] = $row;
		return $rows;
	}

	public function makeSingleElement($element, $data = array(), $value = null, $close = true) {
		$html = "<{$element} ";
		foreach ($data as $attr => $v) {
			$html .= "{$attr}='{$v}'";
		}
		$html .= ">{$value}";
		if ($close) {
			$html .= "</{$element}>";
		}
		return $html;
	}

	/**
	 * Retrieve current vendor
	 *
	 * @return Zolago_Dropship_Model_Vendor
	 */
	public function getVendor() {
		/** @var Zolago_Dropship_Model_Vendor $vendor */
		$vendor = Mage::getSingleton('udropship/session')->getVendor();
		return $vendor;
	}

	public function getCurrentMonth() {
		// todo
		return Mage::getModel('core/date')->date('m-Y');
	}
}