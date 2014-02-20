<?php

class Zolago_Operator_Model_Resource_Operator extends Mage_Core_Model_Resource_Db_Abstract {

	protected function _construct() {
		$this->_init('zolagooperator/operator', "operator_id");
	}

	/**
	 * @param Mage_Core_Model_Abstract $object
	 * @param int $poId
	 * @return bool
	 */
	public function isAllowedToPo(Mage_Core_Model_Abstract $object, $poId) {
		$select = $this->getReadConnection()->select();
		$select->from(
				array("operator_pos"=>$this->getTable("zolagooperator/operator_pos")), 
				array(new Zend_Db_Expr("COUNT(*)"))
		);
		$cond = $this->getReadConnection()->quoteInto(
				"po.default_po_id=operator_pos.pos_id AND po.entity_id=?", 
				$poId
		);
		$select->join(
				array("po"=>$this->getTable("udpo/po")),
				$cond,
				array()
		);
		$select->where("operator_pos.operator_id=?", $object->getId());
		
		return (int)$this->getReadConnection()->fetchOne($select) > 0;
		
	}
	
	/**
	 * @param Mage_Core_Model_Abstract $object
	 * @return type
	 */
	public function getAllowedPos(Mage_Core_Model_Abstract $object) {
		if(!$object->getId()){
			return array();
		}
		$select = $this->getReadConnection()->select();
		$select->from(array("operator_pos"=>$this->getTable("zolagooperator/operator_pos")), array("pos_id"));
		$select->where("operator_pos.operator_id=?", $object->getId());
		return $this->getReadConnection()->fetchCol($select);
	}
	
    /**
     * fill times
     */	
     protected function _prepareDataForSave(Mage_Core_Model_Abstract $object) {
 		// Times
		$currentTime = Varien_Date::now();
		if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {

			$object->setCreatedAt($currentTime);
		}
		$object->setUpdatedAt($currentTime);
		
		// Do not modify passwd hash - return orig passwd
		if($object->getId()){
			$object->setPassword($object->getOrigData('password'));
		}
		// Modify passwd hash by special param
		if($object->getPostPassword()){
			$helper = Mage::helper('core');
			/* @var $helper Mage_Core_Helper_Data */
			$hash = $helper->getHash($object->getPostPassword());
			$object->setPassword($hash);
			$object->setPostPassword(null);
		}
		
		// ACl roles
		 $acl = $object->getAcl();
		 $roles = $object->getRoles();
		 $rolesToSave = array();
		 if(is_array($roles)){
			 foreach($roles as $role){
				 if($acl->hasRole($role)){
					 $rolesToSave[] = $role;
				 }
			 }
		 }
		
		$object->setRoles(implode(",", $rolesToSave));
	
		
		return parent::_prepareDataForSave($object);     	
     }
	 
	 /**
	  * @param Mage_Core_Model_Abstract $object
	  * @return Zolago_Operator_Model_Resource_Operator
	  */
	 protected function _afterLoad(Mage_Core_Model_Abstract $object) {
		 $object->setRoles(explode(",", $object->getData("roles")));
		 return parent::_afterLoad($object);
	 }
	 
	 /**
	  * @param Mage_Core_Model_Abstract $param
	  * @return Zolago_Operator_Model_Resource_Operator
	  */
	 protected function _afterSave(Mage_Core_Model_Abstract $object) {
		// POS Assigment
		if ($object->hasData("allowed_pos")) {
			$this->_setAllowedPos($object, $object->getData("allowed_pos"));
		}
		return parent::_afterSave($object);
	}
	 
	 /**
	  * @param Mage_Core_Model_Abstract $object
	  * @param array $allowedPos
	  * @return Zolago_Operator_Model_Resource_Operator
	  */
	 protected function _setAllowedPos(Mage_Core_Model_Abstract $object, array $allowedPos) {
		 $table = $this->getTable("zolagooperator/operator_pos");
		 $where = $this->getReadConnection()
				 ->quoteInto("operator_id=?", $object->getId());
		 $this->_getWriteAdapter()->delete($table, $where);
		 
		 $toInsert = array();
		 foreach($allowedPos as $posId){
			 $toInsert[] = array("pos_id"=>$posId, "operator_id"=>$object->getId());
		 }
		 if(count($toInsert)){
			 $this->_getWriteAdapter()->insertMultiple($table, $toInsert);
		 }
		 return $this;
	 }
	 
	 public function addOperatorFilterToPoCollection(Mage_Core_Model_Resource_Db_Collection_Abstract $collection, $operator) {
		 if($operator instanceof Zolago_Operator_Model_Operator){
			 $operator = $operator->getId();
		 }
		 $cond = $this->getReadConnection()->quoteInto(
				 "main_table.default_pos_id=operator_pos.pos_id AND operator_pos.operator_id=?", 
				 $operator
		 );
		 $collection->getSelect()->
			join(
				 array("operator_pos"=>$this->getTable("zolagooperator/operator_pos")), 
			     $cond, 
				 array()
			)->
			group("main_table.entity_id");
	 }
}

