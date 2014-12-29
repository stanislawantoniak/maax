<?php

/**
 * Licentia Fidelitas - Advanced Email and SMS Marketing Automation for E-Goi
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * @title      Advanced Email and SMS Marketing Automation
 * @category   Marketing
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) 2012 Licentia - http://licentia.pt
 * @license    Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 */


class Licentia_Fidelitas_Model_Source_Run {

    public function toOptionArray() {

        $return = array();

        for ($i = 0; $i <= 23; $i++)
        {
            $return[] = array('value' => str_pad($i, 2, '0', STR_PAD_LEFT), 'label' => str_pad($i, 2, '0', STR_PAD_LEFT) . ':00');
        }

        return $return;
    }

}