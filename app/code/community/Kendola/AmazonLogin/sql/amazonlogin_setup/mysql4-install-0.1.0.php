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
 * @copyright  Copyright (c) 2017 kendola - easy eCommerce (http://www.kendola.de/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('kendola_amazonlogin_customer')};
CREATE TABLE IF NOT EXISTS {$this->getTable('kendola_amazonlogin_customer')} (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `amazon_id` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_customer` (`customer_id`,`amazon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `{$this->getTable('kendola_amazonlogin_customer')}`
ADD FOREIGN KEY ( `customer_id` ) REFERENCES `{$this->getTable('customer_entity')}` (
`entity_id`
) ON DELETE CASCADE ;
");
$installer->endSetup();
