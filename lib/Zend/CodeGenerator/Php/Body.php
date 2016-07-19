<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_CodeGenerator
 * @subpackage PHP
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_CodeGenerator_Abstract
 */
#require_once 'Zend/CodeGenerator/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_CodeGenerator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_CodeGenerator_Php_Body extends Zend_CodeGenerator_Abstract
{

    /**
     * @var string
     */
    protected $_content = null;

    /**
     * setContent()
     *
     * @param string $content
     * @return Zend_CodeGenerator_Php_Body
     */
    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }

    /**
     * getContent()
     *
     * @return string
     */
    public function getContent()
    {
        return (string) $this->_content;
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        return $this->getContent();
    }
}
