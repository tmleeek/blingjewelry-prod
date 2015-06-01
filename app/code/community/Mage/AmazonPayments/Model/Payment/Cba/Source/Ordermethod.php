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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_AmazonPayments
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * AmazonPayments ASP Payment Action Dropdown source
 *
 * @category   Mage
 * @package    Mage_AmazonPayments
 * @author     Amazon Payments Developer
 */
class Mage_AmazonPayments_Model_Payment_Cba_Source_OrderMethod
{
    public function toOptionArray()
    {
	// Orders currently supported by CBA are AWS and MWS
        return array(
            array(
                'value' => 'AWS',
                'label' => Mage::helper('amazonpayments')->__('AWS')
            ),
            array(
                'value' => 'MWS',
                'label' => Mage::helper('amazonpayments')->__('MWS')
            ),
        );
    }
}

