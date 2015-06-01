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

class Mage_AmazonPayments_Model_Payment_Cba extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Payment module of Checkout by Amazon
     * CBA - Checkout By Amazon
     */

    protected $_code  = 'amazonpayments_cba';
    protected $_formBlockType = 'amazonpayments/cba_form';
    protected $_api;

    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = false;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = true;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = false;
    protected $_canUseForMultishipping  = false;


    protected $_skipProccessDocument = false;

    protected $notificationIdList;

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }
    
    //- Wrapper over the counry configuration parameter 
    public function getIsNeedToSignCart()
    {
         return Mage::getStoreConfig('payment/amazonpayments_cba/sign_xml_cart'); 
    }

    /**
     * Get AmazonPayments API Model
     *
     * @return Mage_AmazonPayments_Model_Api_Cba
     */
    public function getApi()
    {
        if (!$this->_api) {
            $this->_api = Mage::getSingleton('amazonpayments/api_cba');
            $this->_api->setPaymentCode($this->getCode());
        }
        return $this->_api;
    }

    /**
     * Get AmazonPayments session namespace
     *
     * @return Mage_AmazonPayments_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('amazonpayments/session');
    }

    /**
     * Retrieve redirect url
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return Mage::getUrl('amazonpayments/cba/redirect');
    }

    /**
     * Retrieve redirect to Amazon CBA url
     *
     * @return string
     */
    public function getAmazonRedirectUrl()
    {
        return $this->getApi()->getAmazonRedirectUrl();
    }

    /**
     * Authorize
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        parent::authorize($payment, $amount);
        return $this;
    }

    /**
     * Capture payment
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        parent::capture($payment, $amount);
        return $this;
    }

    public function processInvoice($invoice, $payment)
    {
        parent::processInvoice($invoice, $payment);
        $invoice->addComment(
            Mage::helper('amazonpayments')->__('Invoice was created with Checkout by Amazon.')
        );
        return $this;
    }

    public function cancel(Varien_Object $payment)
    {
        if ($this->_skipProccessDocument) {
            return $this;
        }
        $this->getApi()->cancel($payment->getOrder());
        $payment->getOrder()->addStatusToHistory(
            $payment->getOrder()->getStatus(),
            Mage::helper('amazonpayments')->__('Order was canceled with Checkout by Amazon.')
        );
        return $this;
    }

    /**
     * Refund order
     *
     * @param   Varien_Object $payment
     * @return  Mage_AmazonPayments_Model_Payment_Cba
     */
    public function refund(Varien_Object $payment, $amount)
    {
        if ($this->_skipProccessDocument) {
            return $this;
        }
        $this->getApi()->refund($payment, $amount);
        $payment->getCreditmemo()->addComment(
            Mage::helper('amazonpayments')->__('Refund was created with Checkout by Amazon.')
        );
        return $this;
    }

    /**
     * Handle Callback from CBA and calculate Shipping, Taxes in case XML-based shopping cart
     *
     */
    public function handleCallback($_request)
    {
	$response = '';

        if (!empty($_request['order-calculations-request'])) {
            $xmlRequest = urldecode($_request['order-calculations-request']);

            $session = $this->getCheckout();
            $xml = $this->getApi()->handleXmlCallback($xmlRequest, $session);

            if ($this->getDebug()) {
                $debug = Mage::getModel('amazonpayments/api_debug')
                    ->setRequestBody(print_r($_request, 1))
                    ->setResponseBody(time().' - request callback')
                    ->save();
            }

            if ($xml) {
                $xmlText = $xml->asXML();
                $response .= 'order-calculations-response='.urlencode($xmlText);
		
		if($this->_api->getCountry() == "US")
                	$secretKeyID = Mage::getStoreConfig('payment/amazonpayments_cba/secretkey_id');
		else
			$secretKeyID = Mage::getStoreConfig('payment/amazonpayments_cba/mws_secretkey_id');

                $_signature = $this->getApi()->calculateSignature($xmlText, $secretKeyID);

                if ($_signature) {
                    $response .= '&Signature='.urlencode($_signature);
                }
		if($this->_api->getCountry() == "US")
        	        $response .= '&aws-access-key-id='.urlencode(Mage::getStoreConfig('payment/amazonpayments_cba/accesskey_id'));
		else
			$response .= '&aws-access-key-id='.urlencode(Mage::getStoreConfig('payment/amazonpayments_cba/mws_accesskey_id'));

                if ($this->getDebug()) {
                    $debug = Mage::getModel('amazonpayments/api_debug')
                        ->setResponseBody($response)
                        ->setRequestBody(time() .' - response calllback')
                        ->save();
                }
            }
        } else {
            if ($this->getDebug()) {
                $debug = Mage::getModel('amazonpayments/api_debug')
                    ->setRequestBody(print_r($_request, 1))
                    ->setResponseBody(time().' - error request callback')
                    ->save();
            }
        }
        return $response;
    }

// filterNotification function will help in filtering the duplicate IOPN notifications by taking the data from amazon_api_debug table

   public function filterNotification($_request)
   {
                $resource = Mage::getSingleton('core/resource');
                $readConnection = $resource->getConnection('core_read');
                $date=date(('Y-m-d H:i:s'), strtotime('-14 days')); // Getting the 14 days old date to get Notification details as the notifications will be resend for a period of 14 days 
                $query = "Select request_body from amazonpayments_api_debug where debug_at > '$date'";     
                $resultset = $readConnection->fetchAll($query);
                $readConnection = null; //Closing the connection by making the connection string as null        
                $notificationReferenceId_StartTag = '<NotificationReferenceId>';
                $notificationReferenceId_EndTag = '</NotificationReferenceId>';
                $notificationReferenceId_StartTag_length= strlen($notificationReferenceId_StartTag);
                $notifId_array = array();
                foreach($resultset as $row)
                {
                        if(strpos($row['request_body'], $notificationReferenceId_StartTag))
                        {
                                $str_temp = substr($row['request_body'],strpos($row['request_body'],$notificationReferenceId_StartTag)+$notificationReferenceId_StartTag_length,strpos($row['request_body'],$notificationReferenceId_EndTag)-(strpos($row['request_body'],$notificationReferenceId_StartTag)+$notificationReferenceId_StartTag_length));
                                array_push($notifId_array,$str_temp);
                        }
                }

		$notif_ref_id = substr($_request['NotificationData'],strpos($_request['NotificationData'],$notificationReferenceId_StartTag)+$notificationReferenceId_StartTag_length,strpos($_request['NotificationData'],$notificationReferenceId_EndTag)-(strpos($_request['NotificationData'],$notificationReferenceId_StartTag)+$notificationReferenceId_StartTag_length));

                if(in_array($notif_ref_id, $notifId_array))
                {
                        Mage::log("Duplicate Notification Id:".$notif_ref_id);
			$notif_ref_id = null;
                }

		return $notif_ref_id;
   }

    public function handleNotification($_request)
    {
        if (!empty($_request) && !empty($_request['NotificationData']) && !empty($_request['NotificationType'])) {

	     $notifId = $this->filterNotification($_request);
	     
		if($notifId == null)
		{
			return null;
		}
	     /**
             * Debug
             */
            if ($this->getDebug()) {
                $debug = Mage::getModel('amazonpayments/api_debug')
                    ->setRequestBody(print_r($_request, 1))
                    ->setResponseBody(time().' - Notification: '. $_request['NotificationType'])
                    ->save();
            }
            switch ($_request['NotificationType']) {
                case 'NewOrderNotification':
                    $newOrderDetails = $this->getApi()->parseOrder($_request['NotificationData']);
                    Mage::log("NewOrder");
                    $this->_createNewOrder($newOrderDetails);
                    break;
                case 'OrderReadyToShipNotification':
                    $orderReadyToShipDetails = $this->getApi()->parseOrder($_request['NotificationData']);
		    Mage::log("OrderReadyToShip");
                    $this->_proccessOrder($orderReadyToShipDetails);
		    break;
                case 'OrderCancelledNotification':
                    $cancelDetails = $this->getApi()->parseCancelNotification($_request['NotificationData']);
                    $this->_skipProccessDocument = true;
                    $this->_cancelOrder($cancelDetails);
                    $this->_skipProccessDocument = false;
		    Mage::log("CancelOrder");
                    break;
                default:
                    // Unknown notification type
            }
        } else {
            if ($this->getDebug()) {
                $debug = Mage::getModel('amazonpayments/api_debug')
                    ->setRequestBody(print_r($_request, 1))
                    ->setResponseBody(time().' - error request callback')
                    ->save();
            }
        }
        return $this;
    }

    /**
     * Create new order by data from Amazon NewOrderNotification
     *
     * @param array $newOrderDetails
     */
    protected function _createNewOrder(array $newOrderDetails)
    {
        /* @var $order Mage_Sales_Model_Order */
        if (array_key_exists('amazonOrderID', $newOrderDetails)) {
            $_order = Mage::getModel('sales/order')
                ->loadByAttribute('ext_order_id', $newOrderDetails['amazonOrderID']);
            if ($_order->getId()) {
                /**
                * associate real order id with Amazon order
                */
                $this->getApi()->syncOrder($_order);
                $_order = null;
                return $this;
            }
            $_order = null;
        }
	
	$session = $this->getCheckout();

        $quoteId = $newOrderDetails['ClientRequestId'];
        $quote = Mage::getModel('sales/quote')->load($quoteId);
        $baseCurrency = $session->getQuote()->getBaseCurrencyCode();
        $currency = Mage::app()->getStore($session->getQuote()->getStoreId())->getBaseCurrency();

        $shipping = $quote->getShippingAddress();
        $billing = $quote->getBillingAddress();

        $_address = $newOrderDetails['shippingAddress'];
        $this->_address = $_address;

        $regionModel = Mage::getModel('directory/region')->loadByCode($_address['regionCode'], $_address['countryCode']);
        $_regionId = $regionModel->getId();

	$sFirstname = $newOrderDetails['shippingAddress']['name'];
	$sLastname = "";

	$bFirstname = $newOrderDetails['buyerName'];
	$bLastname = "";

        $shipping->setCountryId($_address['countryCode'])
            ->setRegion($_address['regionCode'])
            ->setRegionId($_regionId)
            ->setCity($_address['city'])
            ->setStreet($_address['street'])
            ->setPostcode($_address['postCode'])
            ->setTaxAmount($newOrderDetails['tax'])
            ->setBaseTaxAmount($newOrderDetails['tax'])
            ->setShippingAmount($newOrderDetails['shippingAmount'])
            ->setBaseShippingAmount($newOrderDetails['shippingAmount'])
            ->setShippingTaxAmount($newOrderDetails['shippingTax'])
            ->setBaseShippingTaxAmount($newOrderDetails['shippingTax'])
            ->setShippingInclTax($newOrderDetails['shippingAmount']+$newOrderDetails['shippingTax'])
            ->setBaseShippingInclTax($newOrderDetails['shippingAmount']+$newOrderDetails['shippingTax'])
            ->setDiscountAmount($newOrderDetails['discount'])
            ->setBaseDiscountAmount($newOrderDetails['discount'])
            ->setSubtotal($newOrderDetails['subtotal'])
            ->setBaseSubtotal($newOrderDetails['subtotal'])
            ->setGrandTotal($newOrderDetails['total'])
            ->setBaseGrandTotal($newOrderDetails['total'])
            ->setFirstname($sFirstname)
            ->setLastname($sLastname);

        $_shippingDesc = '';
        $_shippingServices = unserialize($quote->getExtShippingInfo());
        if (is_array($_shippingServices) && array_key_exists('amazon_service_level', $_shippingServices)) {
            foreach ($_shippingServices['amazon_service_level'] as $_level) {
                if ($_level['service_level'] == $newOrderDetails['ShippingLevel']) {
                    $shipping->setShippingMethod($_level['code']);
                    $_shippingDesc = $_level['description'];
                }
            }
        }

        $billing->setCountryId($_address['countryCode'])
            ->setRegion($_address['regionCode'])
            ->setRegionId($_regionId)
            ->setCity($_address['city'])
            ->setStreet($_address['street'])
            ->setPostcode($_address['postCode'])
            ->setTaxAmount($newOrderDetails['tax'])
            ->setBaseTaxAmount($newOrderDetails['tax'])
            ->setShippingAmount($newOrderDetails['shippingAmount'])
            ->setBaseShippingAmount($newOrderDetails['shippingAmount'])
            ->setShippingTaxAmount($newOrderDetails['shippingTax'])
            ->setBaseShippingTaxAmount($newOrderDetails['shippingTax'])
            ->setShippingInclTax($newOrderDetails['shippingAmount']+$newOrderDetails['shippingTax'])
            ->setBaseShippingInclTax($newOrderDetails['shippingAmount']+$newOrderDetails['shippingTax'])
            ->setDiscountAmount($newOrderDetails['discount'])
            ->setBaseDiscountAmount($newOrderDetails['discount'])
            ->setSubtotal($newOrderDetails['subtotal'])
            ->setBaseSubtotal($newOrderDetails['subtotal'])
            ->setGrandTotal($newOrderDetails['total'])
            ->setBaseGrandTotal($newOrderDetails['total'])
            ->setFirstname($bFirstname)
            ->setLastname($bLastname);

        $quote->setBillingAddress($billing);
        $quote->setShippingAddress($shipping);

        $billing = $quote->getBillingAddress();
        $shipping = $quote->getShippingAddress();

        $convertQuote = Mage::getModel('sales/convert_quote');
        /* @var $convertQuote Mage_Sales_Model_Convert_Quote */
        $order = Mage::getModel('sales/order');
        /* @var $order Mage_Sales_Model_Order */

        $order = $convertQuote->addressToOrder($billing);

        // add payment information to order
        $order->setBillingAddress($convertQuote->addressToOrderAddress($billing))
            ->setShippingAddress($convertQuote->addressToOrderAddress($shipping));

        $order->setShippingMethod($shipping->getShippingMethod())
            ->setShippingDescription($_shippingDesc)
            ->setForcedDoShipmentWithInvoice(true);

        $order->setPayment($convertQuote->paymentToOrderPayment($quote->getPayment()));

        /**
         * Amazon Order Id
         */
        $order->setExtOrderId($newOrderDetails['amazonOrderID']);
	Mage::log("NewOrderDetails");
        // add items to order
        foreach ($quote->getAllItems() as $item) {
            /* @var $item Mage_Sales_Model_Quote_Item */
            $order->addItem($convertQuote->itemToOrderItem($item));
            /* @var $orderItem Mage_Sales_Model_Order_Item */
            $orderItem = $order->getItemByQuoteItemId($item->getId());
            $orderItem->setExtOrderItemId($newOrderDetails['items'][$item->getId()]['AmazonOrderItemCode']);
            $orderItemOptions = $orderItem->getProductOptions();
            $orderItemOptions['amazon_amounts'] = serialize(array(
                'shipping' => $newOrderDetails['items'][$item->getId()]['shipping'],
                'tax' => $newOrderDetails['items'][$item->getId()]['tax'],
                'shipping_tax' => $newOrderDetails['items'][$item->getId()]['shipping_tax'],
                'principal_promo' => $newOrderDetails['items'][$item->getId()]['principal_promo'],
                'shipping_promo' => $newOrderDetails['items'][$item->getId()]['shipping_promo']
            ));
            $orderItem->setProductOptions($orderItemOptions);
            $orderItem->setLockedDoInvoice(true)
                ->setLockedDoShip(true);
        }
        $order->place();

        $order->addStatusToHistory(
            $order->getStatus(),
	    Mage::helper('amazonpayments')->__('New Order Notification received from Checkout by Amazon service.')
        );

	$customer = $quote->getCustomer();
        if ($customer && $customer->getId()) {
            $order->setCustomerId($customer->getId())
                ->setCustomerEmail($customer->getEmail())
                ->setCustomerPrefix($customer->getPrefix())
                ->setCustomerFirstname($customer->getFirstname())
                ->setCustomerMiddlename($customer->getMiddlename())
                ->setCustomerLastname($customer->getLastname())
                ->setCustomerSuffix($customer->getSuffix())
                ->setCustomerGroupId($customer->getGroupId())
                ->setCustomerTaxClassId($customer->getTaxClassId());
        } else {
            $order->setCustomerEmail($newOrderDetails['buyerEmailAddress'])
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        }

        $order->save();

        $quote->setIsActive(false);
        $quote->save();

        $orderId = $order->getIncrementId();
        $this->getCheckout()->setLastQuoteId($quote->getId());
        $this->getCheckout()->setLastSuccessQuoteId($quote->getId());
        $this->getCheckout()->setLastOrderId($order->getId());
        $this->getCheckout()->setLastRealOrderId($order->getIncrementId());

        $order->sendNewOrderEmail();

        /**
         * associate real order id with Amazon order
         */
        $this->getApi()->syncOrder($order);
        return $this;
    }

    /**
     * Proccess existing order
     *
     * @param array $amazonOrderDetails
     * @return boolean
     */
    protected function _proccessOrder($amazonOrderDetails)
    {
        if (array_key_exists('amazonOrderID', $amazonOrderDetails)) {
            $order = Mage::getModel('sales/order')
                ->loadByAttribute('ext_order_id', $amazonOrderDetails['amazonOrderID']);
            /* @var $order Mage_Sales_Model_Order */
            if ($order->getId() && $order->getStatus()=="pending") {
		$amazonOrderArray=array_keys($amazonOrderDetails['items']);
		$amazonOrderDetailsArray=$amazonOrderDetails['items'][$amazonOrderArray[0]];
                /* @var $item Mage_Sales_Model_Order_Item */
                foreach ($order->getAllVisibleItems() as $item) {
                     if ($item->getExtOrderItemId() === $amazonOrderDetailsArray['AmazonOrderItemCode']) {
                        $item->setLockedDoInvoice(false)
                            ->setLockedDoShip(false)
                            ->save();
                    }
                }
                $order->addStatusToHistory(
		    Mage_Sales_Model_Order::STATE_PROCESSING,
                    Mage::helper('amazonpayments')->__('Order Ready To Ship Notification received form Checkout by Amazon service.')
                )->save();
           } 
        }
        return true;
    }

    /**
     * Cancel the order
     *
     * @param array $amazonOrderDetails
     * @return boolean
     */
    protected function _cancelOrder($cancelDetails)
    {
        if (array_key_exists('amazon_order_id', $cancelDetails)) {
            $order = Mage::getModel('sales/order')
                ->loadByAttribute('ext_order_id', $cancelDetails['amazon_order_id']);
	Mage::log("In cancel");
            /* @var $order Mage_Sales_Model_Order */
            if ($order->getId()) {
                try {
                    $order->cancel()
                        ->addStatusToHistory(
                            $order->getStatus(),
                            Mage::helper('amazonpayments')->__('Cancel Order Notification received from Checkout by Amazon service.')
                        )->save();
                } catch (Exception $e) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Return xml with error
     *
     * @param Exception $e
     * @return string
     */

    public function callbackXmlError(Exception $e)
    {
	if($this->_api->getCountry() == "US")
        {
                        $secretKeyId= Mage::getStoreConfig('payment/amazonpayments_cba/secretkey_id');
                        $accessKeyId= Mage::getStoreConfig('payment/amazonpayments_cba/accesskey_id');
        }
        else
        {
                        $secretKeyId= Mage::getStoreConfig('payment/amazonpayments_cba/mws_secretkey_id');
                        $accessKeyId= Mage::getStoreConfig('payment/amazonpayments_cba/mws_accesskey_id');
        }

        $_xml = $this->getApi()->callbackXmlError($e);
        $_signature = $this->getApi()->calculateSignature($_xml->asXml(), $secretKeyId);

        $response = 'order-calculations-response='.urlencode($_xml->asXML())
                .'&Signature='.urlencode($_signature)
                .'&aws-access-key-id='.urlencode($accessKeyId);
        return $response;
    }

    /**
     * Prepare fields for XML-based signed cart form for CBA
     *
     * @return array
     */
    public function getCheckoutXmlFormFields()
    {
	if($this->_api->getCountry() == "US")
	{
                        $secretKeyId= Mage::getStoreConfig('payment/amazonpayments_cba/secretkey_id');
			$accessKeyId= Mage::getStoreConfig('payment/amazonpayments_cba/accesskey_id');
	}
        else
	{
			$secretKeyId= Mage::getStoreConfig('payment/amazonpayments_cba/mws_secretkey_id');
			$accessKeyId= Mage::getStoreConfig('payment/amazonpayments_cba/mws_accesskey_id');
	}
        $quoteId = $this->getCheckout()->getQuoteId();
        $_quote = Mage::getModel('sales/quote')->load($quoteId);
        
        $xml = $this->getApi()->getXmlCart($_quote);

        //- log the xml cart sent , before encryption.
        
        //- Sign the cart only if configured.
        if($this->getIsNeedToSignCart())
        { 
          $xmlCart = array('order-input' =>
              "type:merchant-signed-order/aws-accesskey/1;"
              ."order:".base64_encode($xml).";"
              ."signature:{$this->getApi()->calculateSignature($xml, $secretKeyId)};"
              ."aws-access-key-id:" .$accessKeyId

             );
        }
        else
        {
          $xmlCart = array('order-input' =>
              "type:unsigned-order;"
              ."order:".base64_encode($xml).";"
             );
        }
        if ($this->getDebug()) {
            $debug = Mage::getModel('amazonpayments/api_debug')
                ->setResponseBody(print_r($xmlCart, 1)."\norder:".$xml)
                ->setRequestBody(time() .' - xml cart')
                ->save();
        }
        return $xmlCart;
    }

    /**
     * Return CBA order details in case Html-based shopping cart commited to Amazon
     *
     */
    public function returnAmazon()
    {
        $_request = Mage::app()->getRequest()->getParams();

        if ($this->getDebug()) {
            $debug = Mage::getModel('amazonpayments/api_debug')
                ->setRequestBody(print_r($_request, 1))
                ->setResponseBody(time().' - success')
                ->save();
        }
    }

    /**
     * Rewrite standard logic
     *
     * @return bool
     */
    public function isInitializeNeeded()
    {
        return false;
    }

    /**
     * Get debug flag
     *
     * @return string
     */
    public function getDebug()
    {
        return Mage::getStoreConfig('payment/' . $this->getCode() . '/debug_flag');
    }
}
