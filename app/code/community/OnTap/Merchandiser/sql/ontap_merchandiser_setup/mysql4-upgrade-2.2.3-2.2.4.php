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
$categoryCollection = Mage::getModel('catalog/category')->getCollection();
$coreResource = Mage::getSingleton('core/resource');
$categoryValuesTable = $coreResource->getTableName('merchandiser_category_values');
$categoryCollection->getSelect()
    ->join(array('cat_values'=>$categoryValuesTable), 'e.entity_id = cat_values.category_id', 'automatic_sort')
    ->where('cat_values.automatic_sort <> "" OR cat_values.automatic_sort <> "none"');
foreach ($categoryCollection as $category) {
    Mage::getModel('merchandiser/merchandiser')->affectCategoryBySmartRule($category->getId());
}
