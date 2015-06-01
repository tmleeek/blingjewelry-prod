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
 * @category  OnTap
 * @package   OnTap_Merchandiser
 * @copyright Copyright (c) 2014 On Tap Networks Ltd. (http://www.ontapgroup.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OnTap_Merchandiser_Block_Adminhtml_System_Config_Fieldset_Rebuild
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * _construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('merchandiser/config/button.phtml');
    }
    
    /**
     * _getElementHtml
     * 
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }
    
    /**
     * getButtonHtml
     * 
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'id'        => 'merchandiser_rebuild_button',
            'label'     => $this->helper('merchandiser')->__('Rebuild Category Products for Smart Categories'),
            'onclick'   => 'javascript:check(); return false;'
        ));
 
        return $button->toHtml();
    }
}