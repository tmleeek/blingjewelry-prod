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
class OnTap_Merchandiser_Block_Adminhtml_Merchandiser extends Mage_Adminhtml_Block_Catalog
{
    /**
     * _construct
     */
    public function _construct()
    {
        $session = Mage::getSingleton('core/session', array('name' => 'adminhtml'));
        $user = Mage::helper('adminhtml')->getCurrentUserId();
        $this->setUser($user)->setSession($session);
        parent::_construct();
    }
    
    /**
     * getCategoryId
     * 
     * @return int
     */
    public function getCategoryId()
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        return is_numeric($categoryId) ? (int)$categoryId : null;
    }
    
    /**
     * getCategory
     * 
     * @return object
     */
    public function getCategory()
    {
        if ($categoryId = $this->getCategoryId()) {
            return Mage::getModel('catalog/category')->load($categoryId);
        } else {
            return null;
        }
    }

    /**
     * getColumnCount
     * 
     * @return int
     */
    public function getColumnCount()
    {
        $columnCount = (int)$this->getRequest()->getParam('column_count');
        return (int)Mage::helper('merchandiser')->getColumnCount($columnCount);
    }
}