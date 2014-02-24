<?php
/**
 * new acl test
 */
class Zolago_Operator_Model_AclTest extends Zolago_TestCase {
    
    /**
     * simply test
     */
     public function testCreate() {
         $obj = Mage::getModel('zolagooperator/acl');
         $this->assertNotEmpty($obj);
         $roles = $obj::getAllRoles();
         $this->assertArrayHasKey('order_operator',$roles,print_R($roles,1));
         $options = $obj::getAllRolesOptions();
         $this->assertNotEmpty($options);
     }
     
}