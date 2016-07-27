<?php
/**
 * Altima Lookbook Free Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Altima
 * @package    Altima_LookbookFree
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Altima_Lookbook_Model_Layout_Generate_Observer {

	public function addHeadItems($observer) {
            if (Mage::helper('lookbook')->getEnabled()) {
            	$data = $observer->getData(); 
                $page = $data['page'];
                if ($page) {
                    $pagecontent = 	$page->getContent();       
                    $search_string = '{{block type="lookbook/lookbook" template="lookbook/lookbook.phtml"}}';
                    if (preg_match($search_string, $pagecontent)) {
                        $updates = $page->getLayoutUpdateXml();
                    	$newupdates = '<reference name="head">
                            <action method="addCss"><stylesheet>lookbook/css/hotspots.css</stylesheet></action>
                            <action method="addJs"><script>jquery/jquery-1.8.2.min.js</script></action>
                            <action method="addJs"><script>lookbook/jquery.mobile.customized.min.js</script></action>
                            <action method="addJs"><script>jquery/jquery.noconflict.js</script></action>
                            <action method="addJs"><script>jquery/jquery.actual.min.js</script></action>

                            <action method="addItem"><type>skin_js</type><name>lookbook/js/jquery-migrate-1.2.1.min.js</name></action>
                            <action method="addItem"><type>skin_js</type><name>lookbook/js/jquery.easing.1.3.js</name></action>
                            <action method="addItem"><type>skin_js</type><name>lookbook/js/camera.min.js</name></action>
                            <action method="addItem"><type>skin_js</type><name>lookbook/js/hotspots.js</name></action>
                        </reference>';
                        $page->setLayoutUpdateXml($updates.$newupdates);
                    } 
                       
                } 
            }                               
	}

}