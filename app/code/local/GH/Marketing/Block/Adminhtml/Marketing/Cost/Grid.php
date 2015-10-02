<?php

class GH_Marketing_Block_Adminhtml_Marketing_Cost_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ghmarketing_cost_grid');
        $this->setDefaultSort('date');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $resourceModel = Mage::getResourceModel("ghmarketing/marketing_cost");

        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addAttributeToSelect("name");
        $collection->getSelect()
            ->join(
                array("marketing_cost" => $resourceModel->getTable("ghmarketing/marketing_cost")),
                "e.entity_id = marketing_cost.product_id"
            );
        $collection->getSelect()
            ->join(
                array("marketing_cost_type" => $resourceModel->getTable("ghmarketing/marketing_cost_type")),
                "marketing_cost_type.marketing_cost_type_id = marketing_cost.type_id"
            );
        $collection->getSelect()
            ->join(
                array("vendors" => $resourceModel->getTable("udropship/vendor")),
                "marketing_cost.vendor_id=vendors.vendor_id",
                array("vendor_name" => "vendors.vendor_name")
            );
        $collection->getSelect()
            ->joinLeft(
                array("ghstatements" => $resourceModel->getTable("ghstatements/statement")),
                "marketing_cost.statement_id=ghstatements.id",
                array("link_param" => "ghstatements.id","link_name" => "ghstatements.name")
            );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn("marketing_cost_id", array(
            "index" => "marketing_cost_id",
            "header" => Mage::helper("ghmarketing")->__("ID"),
            "align" => "right",
            "type" => "number",
            "width" => "10px"
        ));

        $this->addColumn("sku", array(
            "index" => "sku",
            "header" => Mage::helper("ghmarketing")->__("Product SKU"),
            "align" => "right",
            "type" => "text",
            "width" => "100px"
        ));
        $this->addColumn("name", array(
            "index" => "name",
            "header" => Mage::helper("ghmarketing")->__("Product"),
            "align" => "right",
            "type" => "text",
            "width" => "100px"
        ));
        $this->addColumn("vendor_name", array(
            "index" => "vendor_name",
            "header" => Mage::helper("ghmarketing")->__("Vendor"),
            "align" => "right",
            "type" => "text",
            "width" => "100px",
            "filter_condition_callback" => array($this, "_marketingCostVendor")
        ));

        $this->addColumn("date", array(
            "index" => "date",
            "header" => Mage::helper("ghmarketing")->__("Date"),
            "align" => "right",
            "type" => "date",
            "width" => "100px",
            "filter_condition_callback" => array($this, "_marketingCostDate")
        ));
        $this->addColumn("type_id", array(
            "index" => "type_id",
            "header" => Mage::helper("ghmarketing")->__("Cost type"),
            "align" => "right",
            "type" => "options",
            "options" => Mage::getSingleton('ghmarketing/source')->setPath('cost_type')->toOptionHash(),
            "filter_condition_callback" => array($this, "_marketingCostType")
        ));
        $this->addColumn("cost", array(
            "index" => "cost",
            "header" => Mage::helper("ghmarketing")->__("Cost"),
            "align" => "right",
            "type" => "price",
            "width" => "100px",
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
            "filter_condition_callback" => array($this, "_marketingCost")
        ));
        $this->addColumn("click_count", array(
            "index" => "click_count",
            "header" => Mage::helper("ghmarketing")->__("Click count"),
            "align" => "right",
            "type" => "number",
            "width" => "100px",
            "filter_condition_callback" => array($this, "_statementClickCount")
        ));
        $this->addColumn("billing_cost", array(
            "index" => "billing_cost",
            "header" => Mage::helper("ghmarketing")->__("Billing cost"),
            "align" => "right",
            "type" => "price",
            "width" => "100px",
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
            "filter_condition_callback" => array($this, "_marketingBillingCost")
        ));

        $this->addColumn("statement_link", array(
            "index" => "statement_id",
            "header" => Mage::helper("ghmarketing")->__("Statement"),
            "align" => "right",
            "width" => "200px",
            "renderer" => "Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_LinkRefer",
            "link_action" => "*/vendor_statements/edit",
            "link_param" => "id",
            "is_admin_link" => true,
            "filter_condition_callback" => array($this, "_statementLinkRefer")

        ));

        return parent::_prepareColumns();
    }



    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    protected function _marketingCostDate($collection, $column)
    {
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $value = $column->getFilter()->getValue();
        if (!$value)
            return $this;

        $from = isset($value["orig_from"]) ? date("Y-m-d H:i:s", strtotime($value["orig_from"])) : false;
        $to = isset($value["orig_to"]) ? date("Y-m-d H:i:s", strtotime($value["orig_to"])) : false;

        if ($from && $to) {
            $collection->getSelect()->where("marketing_cost.date BETWEEN '{$from}' AND '{$to}' ");
            return $this;
        } elseif ($from) {
            $collection->getSelect()->where("marketing_cost.date >= '{$from}' ");
            return $this;
        } elseif ($to) {
            $collection->getSelect()->where("marketing_cost.date <= '{$to}' ");
            return $this;
        }
        return $this;
    }

    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    protected function _marketingCostType($collection, $column)
    {
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $value = $column->getFilter()->getValue();
        if (!$value)
            return $this;

        $collection->getSelect()->where("marketing_cost.type_id=? ", $value);

        return $this;
    }

    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    protected function _marketingBillingCost($collection, $column)
    {
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $value = $column->getFilter()->getValue();
        if (!$value)
            return $this;

        $from = isset($value["from"]) ? $value["from"] : false;
        $to = isset($value["to"]) ? $value["to"] : false;

        if ($from && $to) {
            $collection->getSelect()->where("marketing_cost.billing_cost BETWEEN {$from} AND {$to}");
            return $this;
        } elseif ($from) {
            $collection->getSelect()->where("marketing_cost.billing_cost >= {$from}");
            return $this;
        } elseif ($to) {
            $collection->getSelect()->where("marketing_cost.billing_cost <= {$to}");
            return $this;
        }
        return $this;
    }
    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    protected function _marketingCost($collection, $column)
    {
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $value = $column->getFilter()->getValue();
        if (!$value)
            return $this;

        $from = isset($value["from"]) ? $value["from"] : false;
        $to = isset($value["to"]) ? $value["to"] : false;

        if ($from && $to) {
            $collection->getSelect()->where("marketing_cost.cost BETWEEN {$from} AND {$to}");
            return $this;
        } elseif ($from) {
            $collection->getSelect()->where("marketing_cost.cost >= {$from}");
            return $this;
        } elseif ($to) {
            $collection->getSelect()->where("marketing_cost.cost <= {$to}");
            return $this;
        }
        return $this;
    }


    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    protected function _statementClickCount($collection, $column)
    {
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $value = $column->getFilter()->getValue();
        if (!$value)
            return $this;

        $from = isset($value["from"]) ? $value["from"] : false;
        $to = isset($value["to"]) ? $value["to"] : false;

        if ($from && $to) {
            $collection->getSelect()->where("click_count BETWEEN {$from} AND {$to}");
            return $this;
        } elseif ($from) {
            $collection->getSelect()->where("click_count >= {$from}");
            return $this;
        } elseif ($to) {
            $collection->getSelect()->where("click_count <= {$to}");
            return $this;
        }
        return $this;
    }


    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    protected function _marketingCostVendor($collection, $column)
    {
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $value = $column->getFilter()->getValue();
        if (!$value)
            return $this;

        $collection->getSelect()->where("vendors.vendor_name LIKE ? ", "%{$value}%");
        return $this;
    }


    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    protected function _statementLinkRefer($collection, $column)
    {
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $value = $column->getFilter()->getValue();

        if (!$value)
            return $this;

        $likeName = new Zend_Db_Expr("ghstatements.name LIKE '%{$value}%' ");
        $equalId = new Zend_Db_Expr("ghstatements.id='{$value}' ");

        $collection->getSelect()->where("{$likeName} OR {$equalId}");
        return $this;
    }

}