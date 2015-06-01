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

$installer->run("ALTER TABLE  `".$this->getTable('merchandiser_category_values').
    "` ADD  `automatic_sort` VARCHAR(100) NOT NULL  default '';");

$categories = Mage::getModel('catalog/category')->getCollection();
$categories->addAttributeToSelect(array(
    'smart_attributes',
    'merchandiser_sorting_options',
    'merchandiser_heroproducts',
    'merchandise_option'));
$insertData = array();

foreach ($categories as $category) {
    $attributeCodesArray = array();
    $smartAttributes = $category->getSmartAttributes()!=''?$category->getSmartAttributes():'';
    $heroProducts = $category->getMerchandiserHeroproducts()!=''?$category->getMerchandiserHeroproducts():'';
    $sortingOptions = $category->getMerchandiserSortingOptions()!=''?$category->getMerchandiserSortingOptions():'';
    $merchandise = $category->getMerchandiseOption()!=''?$category->getMerchandiseOption():'';
    if ($smartAttributes != '') {
        foreach (unserialize($smartAttributes) as $smartRules) {
            $attributeCodesArray[] = $smartRules['attribute'];
        }
    }
    if ($smartAttributes != '' || $heroProducts != '' || $sortingOptions != '') {
        $insertData[] = array(
            'category_id'=>$category->getId(),
            'heroproducts'=>$heroProducts,
            'smart_attributes'=>$smartAttributes,
            'ruled_only'=>$merchandise,
            'attribute_codes'=>implode(",", array_unique($attributeCodesArray)),
            'automatic_sort'=>$sortingOptions);
    }
}

if (sizeof($insertData) > 0) {
    $coreResource = Mage::getSingleton('core/resource');
    $writeAdapter = $coreResource->getConnection('core_write');
    $categoryValuesTable = $coreResource->getTableName('merchandiser_category_values');
    $writeAdapter->insertMultiple($categoryValuesTable, $insertData);
}

$installer->endSetup(); 