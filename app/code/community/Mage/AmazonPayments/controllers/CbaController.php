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
class Mage_AmazonPayments_CbaController extends Mage_Core_Controller_Front_Action
{

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get singleton with Checkout by Amazon order transaction information
     *
     * @return Mage_AmazonPayments_Model_Payment_CBA
     */
    public function getCba()
    {
        return Mage::getSingleton('amazonpayments/payment_cba');
    }

    /**
     * When a customer chooses Checkout by Amazon on Shopping Cart page
     *
     */
    public function shortcutAction()
    {
        if (!$this->getCba()->isAvailable()) {
            $this->_redirect('checkout/cart/');
        }
        $session = $this->getCheckout();
        if ($quoteId = $this->getCheckout()->getQuoteId()) {
            $quote = Mage::getModel('sales/quote')->load($quoteId);

            /** @var $quote Mage_Sales_Model_Quote */
            if ($quote->hasItems()) {
                $session->setAmazonQuoteId($quoteId);

                $quote->getPayment()->setMethod($this->getCba()->getCode());
                $quote->setIsActive(true);
                $quote->save();
		$this->_redirect('checkout/cart/');
            } else {
                $this->_redirect('checkout/cart/');
            }
        } else {
            $this->_redirect('checkout/cart/');
        }
    }

    /**
     * When a customer has checkout on Amazon and return with Successful payment
     *
     */
    public function successAction()
    {
        $session = $this->getCheckout();
        if ($session->hasData('quote_id_'.Mage::app()->getStore()->getWebsiteId())) {
             //Clear the session only at the success event. 
             $session->unsetData('quote_id_'.Mage::app()->getStore()->getWebsiteId());
        }
        $this->getCba()->returnAmazon();

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * When Amazon return callback request for calculation shipping, taxes and etc.
     *
     */
    public function callbackAction()
    {
        $response = '';
        $session = $this->getCheckout();

        $_request = Mage::app()->getRequest()->getParams();
        try {
            if ($_request) {
                $response = $this->getCba()->handleCallback($_request);
            } else {
                $e = new Exception('Inavlid Shipping Address');
            }
        }
        catch (Exception $e) {
            // Return Xml with Error
            $response = $this->getCba()->callbackXmlError($e);
        }
        echo $response;
        exit(0);
    }

    public function notificationAction()
    {
        $response = '';
        $session = $this->getCheckout();

        $_request = Mage::app()->getRequest()->getParams();
        try {
            $this->getCba()
                ->handleNotification($_request);
        }
        catch (Exception $e) {
            // Return Xml with Error
            $response = $this->getCba()->callbackXmlError($e);
        }
        $this->getResponse()
            ->setHttpResponseCode(200);
    }

    /**
     * When a customer has checkout on Amazon and return with Cancel
     *
     */
    public function cancelAction()
    {
        $session = $this->getCheckout();
        if ($quoteId = $session->getAmazonQuoteId()) {
            $quote = Mage::getModel('sales/quote')->load($quoteId);
            $quote->setIsActive(true);
            $quote->save();
            $session->setQuoteId($quoteId);
        }
        if ($this->getCba()->getDebug()) {
            $_request = Mage::app()->getRequest()->getParams();
            $debug = Mage::getModel('amazonpayments/api_debug')
                ->setResponseBody(print_r($_request, 1))
                ->setRequestBody(time() .' - cancel')
                ->save();
        }
        $this->_redirect('checkout/cart/');
    }

}
