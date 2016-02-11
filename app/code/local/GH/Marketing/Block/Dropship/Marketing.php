<?php

/**
 * Block with budget and marketing costs
 *
 * Class GH_Marketing_Block_Dropship_Marketing
 */
class GH_Marketing_Block_Dropship_Marketing extends Mage_Core_Block_Template {

	/**
	 * Cache for calculated costs
	 *
	 * @var array
	 */
	protected $_allCosts = null;

	/**
	 * Cache for budgets
	 *
	 * @var null
	 */
	protected $_allBudgets = null;
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
	 * Retrieve dates as months for current vendor
	 * from regulation document accepted date to now
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
            $begin = Mage::getModel('core/date')->date('Y-m-01');
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

	/**
	 * Retrieve html of <tbody> with marketing costs rows + Separator row + Total row + Budget row
	 *
	 * @return string
	 */
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
	 * Get rows array for getTbody
	 * array like:
	 * array(
	 *    // rows for categories
	 *    array('data' => array(), 'cells' => array("<td>Category Y</td>","<td>value</td>",[...])),
	 *    [...]
	 *    // Total row
	 *    array('data' => array('class' => 'total-row'), 'cells' => array("<td>Total</td>","<td>total 1</td>", [...])),
	 *     // Budget row
	 *    array('data' => array('class' => 'budget-row'),'cells' => array("<td>Budget</td>","<td><input class=\"form-control\" type=\"text\" placeholder=\"\"></td>", [...]
	 * ))
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
		foreach ($categories as $catId => $label) {
			$row = array('data' => array(), 'cells' => array());
			$row['cells'][] = $this->makeSingleElement('td', array(), $label);
			foreach ($costTypes as $type) {
				/** @var GH_Marketing_Model_Marketing_Cost_Type $type */
				$value = $this->getCost($type->getId(), $catId);
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
			$value = $this->getBudget($type->getId());
			$input = $this->makeSingleElement('input', array('name' => "budget[{$type->getMarketingCostTypeId()}]",'class' => 'form-control', 'type' => 'text', 'value' =>  number_format($value,2,',','')));
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

	/**
	 * Retrieve current month in format Y-m
	 * Month can by parameter in request
	 * If not current date taken
	 *
	 * @param string $format
	 * @return mixed
	 * @throws Exception
	 */
	public function getCurrentMonth($format = 'Y-m') {
		/** @var Mage_Core_Controller_Request_Http $req */
		$req = $this->getRequest();
		$month = $req->getParam('month');
		return empty($month) ? Mage::getModel('core/date')->date($format) : $month;
	}

	/**
	 * Get cost for marketing type and category
	 *
	 * @param $typeId
	 * @param $catId
	 * @param null $vendor
	 * @param null $month
	 * @return int
	 */
	public function getCost($typeId, $catId, $vendor = null, $month = null) {
		$allCosts = $this->getAllCosts($vendor, $month);
		$key = $this->buildKeyForCache($vendor, $month);
		if (isset($allCosts[$key][$catId][$typeId])) {
			return $allCosts[$key][$catId][$typeId];
		}
		return 0;
	}

	/**
	 * Get budget for marketing type
	 *
	 * @param $typeId
	 * @param null $month
	 * @param null $vendorId
	 * @return int
	 */
	public function getBudget($typeId, $month = null, $vendorId = null) {
		$key = $this->buildKeyForCache($vendorId, $month);
		$budgets = $this->getAllBudgets($month, $vendorId);
		if (isset($budgets[$key][$typeId])) {
			return $budgets[$key][$typeId];
		}
		return 0;
	}

	/**
	 * Collect budgets and cache it
	 *
	 * @param $month
	 * @param $vendorId
	 * @return null
	 */
	public function getAllBudgets($month, $vendorId) {
		if (empty($month)) {
			$month = $this->getCurrentMonth('Y-m');
		}
		if (empty($vendor)) {
			$vendorId = $this->getVendor()->getId();
		}
		$key = $this->buildKeyForCache($vendorId, $month);
		if (!isset($this->_allBudgets[$key])) {
			/** @var GH_Marketing_Model_Resource_Marketing_Budget_Collection $collection */
			$collection = Mage::getResourceModel('ghmarketing/marketing_budget_collection');
			$collection->addVendorFilter($vendorId);
			$collection->addMonthFilter($month);

			$this->_allBudgets[$key] = array();
			foreach ($collection as $budget) {
				/** @var GH_Marketing_Model_Marketing_Budget $budget */
				$this->_allBudgets[$key][$budget->getMarketingCostTypeId()] = $budget->getBudget();
			}
		}
		return $this->_allBudgets;
	}

	/**
	 * Retrieve all costs for specific Vendor and Month
	 * If no vendor selected current taken
	 * If no mount selected current taken
	 * return example:
	 * [<key-vendor-month>][<attribute_set_id>][<type_id>]
	 *
	 * @param null $vendorId
	 * @param null $month
	 * @return array
	 */
	public function getAllCosts($vendorId = null, $month = null) {
		if (empty($month)) {
			$month = $this->getCurrentMonth('Y-m');
		}
		if (empty($vendor)) {
			$vendorId = $this->getVendor()->getId();
		}
		$key = $this->buildKeyForCache($vendorId, $month);
		if (!isset($this->_allCosts[$key])) {
			/** @var GH_Marketing_Model_Resource_Marketing_Cost $modelResource */
			$modelResource = Mage::getResourceModel("ghmarketing/marketing_cost");
			$data = $modelResource->getGroupedCosts($vendorId, $month);
			$this->_allCosts[$key] = array();
			foreach ($data as $row) {
				$this->_allCosts[$key][$row['attribute_set_id']][$row['type_id']] = round($row['sum'], 2);
			}
		}
		return $this->_allCosts;
	}

	/**
	 * Build key for cache with all costs
	 * from vendor and month
	 *
	 * @param $vendorId
	 * @param $month
	 * @return string
	 */
	public function buildKeyForCache($vendorId, $month) {
		if (empty($month)) {
			$month = $this->getCurrentMonth();
		}
		if (empty($vendor)) {
			$vendorId = $this->getVendor()->getId();
		}
		return $vendorId.'-'.$month;
	}
}