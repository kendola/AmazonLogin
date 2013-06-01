<?php
/**
 * Kendola Login With Amazon for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Kendola
 * @package    Kendola_AmazonLogin
 * @author     Oliver Treck, kendola - easy eCommerce <o.treck@kendola.de>
 * @copyright  Copyright (c) 2013 kendola - easy eCommerce (http://www.kendola.de/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Kendola_AmazonLogin_Model_Resource_Amazonlogin extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('amazonlogin/amazonlogin', 'id');
    }
}
