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
class Altima_Lookbook_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {	 
        if (Mage::helper('lookbook')->getEnabled()) {
 		     $this->loadLayout();     
		  $this->renderLayout();              
        }
        else
        {
           $this->_forward('*/*/noRouteAction');
        }

    }
    
    public function noRouteAction($coreRoute = null)
    {
        //modify this method as you need	
        $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
        $this->getResponse()->setHeader('Status','404 File not found');
        $pageId = Mage::getStoreConfig('web/default/cms_no_route');
        if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
            $this->_forward('defaultNoRoute');
       }
    }
    
}