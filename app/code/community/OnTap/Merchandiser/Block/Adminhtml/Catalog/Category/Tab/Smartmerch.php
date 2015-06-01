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
class OnTap_Merchandiser_Block_Adminhtml_Catalog_Category_Tab_Smartmerch extends Mage_Core_Block_Template
{
    /**
     * _construct
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('catalog_category_smartmerch');
        $this->setTemplate('merchandiser/smartmerch/tab.phtml'); 
    }
    
    /**
     * getCategoryValues
     * 
     * @return array
     */
    public function getCurrentCategoryValues()
    {
        $categoryValues = Mage::getResourceModel('merchandiser/merchandiser')
            ->fetchCategoriesValuesByCategoryId($this->getCategory()->getId());
        return array_shift($categoryValues);
    }
    
    /**
     * getCategory
     * 
     * @return object
     */
    public function getCategory()
    {
        return Mage::registry('category');
    }
    
    /**
     * getSmartCategoryAttributes
     * 
     * @param array $categoryValues
     * @return array
     */
    public function getSmartCategoryAttributes($categoryValues)
    {
        try {
            $smartCategoryAttributes = unserialize($categoryValues['smart_attributes']);
        } catch(Exception $e) {
            $smartCategoryAttributes = array();
        }
        
        if (is_array($smartCategoryAttributes)) {
            foreach ($smartCategoryAttributes as $key => $row) {
                if (!is_array($row)) {
                    unset($smartCategoryAttributes[$key]);
                }
            }
        }
        
        return $smartCategoryAttributes;
    }
}
