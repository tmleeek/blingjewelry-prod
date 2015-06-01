<?php
/**
 * Magento
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
 * @category    OnTap
 * @package     OnTap_Merchandiser
 * @copyright   Copyright (c) 2014 On Tap Networks Ltd. (http://www.ontapgroup.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer = $this;
$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('merchandiser_category_values')};
CREATE TABLE {$this->getTable('merchandiser_category_values')} (
  `category_id` int(11) NOT NULL,
  `heroproducts` text NOT NULL default '',
  `attribute_codes` varchar(255) NOT NULL default '',
  `smart_attributes` text NOT NULL default '',
  `ruled_only` smallint(4) NOT NULL default 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('merchandiser_vmbuild')};
CREATE TABLE {$this->getTable('merchandiser_vmbuild')} (
  `attribute_code` varchar(255) NOT NULL default ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup();
