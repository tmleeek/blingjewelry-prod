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

/*
The following code is Copyright 2011 Amazon Technologies, Inc. All Rights Reserved.

Licensed under the Open Software License ("OSL"), version 3.0 (the “License”). 
You may not use this file except in compliance with the License. A copy of the License is located at

      http://opensource.org/licenses/osl-3.0.php  

*/


/**
 * Paypal shortcut link
 *
 * @category   Mage
 * @package    Mage_Paypal
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_AmazonPayments_Block_Link_Shortcut extends Mage_Core_Block_Template
{
    public function getCheckoutUrl()
    {
        return $this->getUrl('amazonpayments/cba/shortcut');
    }

    public function _toHtml()
    {
        if (Mage::getStoreConfigFlag('payment/amazonpayments_cba/active')
            && Mage::getSingleton('checkout/session')->getQuote()->validateMinimumAmount()) {
            return parent::_toHtml();
        }

        return '';
    }

    public function getCbaJsUrl()
    {
	$cba_api = Mage::getModel('amazonpayments/api_cba');
	$cba_api->getApi();
	Mage::log($cba_api->getCountry());
	$country = $cba_api->getCountry();
	if (Mage::getStoreConfigFlag('payment/amazonpayments_cba/sandbox_flag'))
	{
		$mode = 'sandbox';
	}
	else
	{
		$mode = 'prod';
	}

	$url = Mage::getStoreConfig('payment/amazonpayments_cba/'.$mode.'_javascript_url_'.$country);
        return $url;
    }

	public function getButtonHtml($widgetName)
	{
		$cba = Mage::getModel('amazonpayments/payment_cba');
	        $cba->getApi();
        	$formFields = $cba->getCheckoutXmlFormFields();
	        $html='<script type="text/javascript" src="';
	        $html.= $this->getCbaJsUrl();
	        $html.='"></script>';
	        $html.= '<div id="'.$widgetName.'"></div>';
	        $html.= '<script type="text/javascript">';
	        $html.= 'new CBA.Widgets.StandardCheckoutWidget({ merchantId: \'';
	        $html.= Mage::getStoreConfig('payment/amazonpayments_cba/merchant_id');
	        $html.= '\', buttonSettings: { size: \'medium\', color: \'orange\', background: \'light\' }, orderInput: { format: \'XML\', value:\'';
	        $html.= $formFields['order-input'];
	        $html.= '\'} }).render(\''.$widgetName.'\');';
	        $html.= '</script>';
	        return $html;
	}
}
