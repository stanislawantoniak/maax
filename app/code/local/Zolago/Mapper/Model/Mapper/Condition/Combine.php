<?php
class Zolago_Mapper_Model_Mapper_Condition_Combine extends Mage_Rule_Model_Condition_Combine {
    
	protected $_rule;
	/**
	 * Rewrite shitty contructor
	 */
    public function __construct($ruleModel) {
		if(! $ruleModel instanceof Mage_Rule_Model_Rule){
			throw new Exception("Speficty rule model");
		}
		$this->_rule = $ruleModel;
		parent::__construct();
		$this->setType('zolagomapper/mapper_condition_combine');
	}
	
	protected function _getNewConditionModelInstance($modelClass) {
		if (empty($modelClass)) {
            return false;
        }

        if (!array_key_exists($modelClass, self::$_conditionModels)) {
            $model = Mage::getModel($modelClass , $this->_rule);
            self::$_conditionModels[$modelClass] = $model;
        } else {
            $model = self::$_conditionModels[$modelClass];
        }

        if (!$model) {
            return false;
        }

        $newModel = clone $model;
        return $newModel;
	}

    public function getNewChildSelectOptions() {
        $productCondition = Mage::getModel('zolagomapper/mapper_condition_product', $this->_rule);
		
        $productAttributes = $productCondition->
				loadAttributeOptions()->
				getAttributeOption();
        $attributes = array();
        foreach ($productAttributes as $code=>$label) {
            $attributes[] = array('value'=>'zolagomapper/mapper_condition_product|'.$code, 'label'=>$label);
        }
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value'=>'zolagomapper/mapper_condition_combine', 'label'=>Mage::helper('catalogrule')->__('Conditions Combination')),
            array('label'=>Mage::helper('catalogrule')->__('Product Attribute'), 'value'=>$attributes),
        ));
        return $conditions;
    }

	/**
	 * @param $productCollection
	 * @return $this
	 */
	public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
			/** @var Zolago_Mapper_Model_Mapper_Condition_Combine $condition */
			/* OR */
			/** @var Zolago_Mapper_Model_Mapper_Condition_Product $condition */
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
	
}
