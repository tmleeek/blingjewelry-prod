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
 * The following code is Copyright 2011 Amazon Technologies, Inc. All Rights 
 * Reserved.
 */



/**
 * Amazon Order Document Api
 *
 * @category   Mage
 * @package    Mage_AmazonPayments
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Mage_AmazonPayments_Model_Api_Cba_Document extends Varien_Object
{

    public $readresult = null; 
    protected $_merchantInfo = array();
    protected $_result = null;

    protected function _construct()
    {
        parent::_construct();
    }


    /**
     * Function to get the MWS Access key from config
     */
    public function getMwsAccessKey()
    {
	return Mage::getStoreConfig('payment/amazonpayments_cba/mws_accesskey_id');
    }


    /**
     * Function to get the MWS Secret key from config
     */
    public function getMwsSecretKey()
    {
	return Mage::getStoreConfig('payment/amazonpayments_cba/mws_secretkey_id');
    }

    /**
     * Set merchant info
     *
     * @param array $merchantInfo
     * @return Mage_AmazonPayments_Model_Api_Cba_Document
     */
    public function setMerchantInfo(array $merchantInfo = array())
    {
        $this->_merchantInfo = $merchantInfo;
        return $this;
    }

    /**
     * Return merchant info
     *
     * @return array
     */
    public function getMerchantInfo()
    {
        return $this->_merchantInfo;
    }

    /**
     * Return merchant identifier
     *
     * @return string
     */
    public function getMerchantIdentifier()
    {
        if (array_key_exists('merchantIdentifier', $this->_merchantInfo)) {
            return $this->_merchantInfo['merchantIdentifier'];
        }
        return null;
    }


    /*
    /**
     * Funtion to submit the XML feed to Amazon using MWS
     * @param XML document string 
     * @param feed type string
     * added for Order Mangement through MWS
     */
    protected function _processMWSRequest($_document,$feedType)
    {
        $Country = Mage::getStoreConfig('payment/amazonpayments_cba/country');

	$application_name='Magento CBA Plugin';
        $application_version=Mage::getVersion();
	$serviceUrl = Mage::getStoreConfig('payment/amazonpayments_cba/mws_service_url_'.$Country);
	$merchant_id = Mage::getStoreConfig('payment/amazonpayments_cba/merchant_id');

 if (Mage::getStoreConfig('payment/amazonpayments_cba/sandbox_flag')==1)
        {
                $marketplace_id = Mage::getStoreConfig('payment/amazonpayments_cba/sandbox_marketplace_id_'.$Country);
        }
        else
        {
                $marketplace_id = Mage::getStoreConfig('payment/amazonpayments_cba/marketplace_id_'.$Country);
        }

	$config = array (
			  'ServiceURL' => $serviceUrl,
			  'ProxyHost' => null,
			  'ProxyPort' => -1,
			  'MaxErrorRetry' => 3,
			);
	
	/************************************************************************
	 * Instantiate Implementation of MarketplaceWebService
	 * 
	 * AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY constants 
	 * are defined in the .config.inc.php located in the same 
	 * directory as this sample
	 ***********************************************************************/
	 $service = new MarketplaceWebService_Client(
			     $this->getMwsAccessKey(),
			     $this->getMwsSecretKey(),
			     $config,
			     $application_name,
			     $application_version); 

	
	$feedHandle = @fopen('php://temp', 'rw+');
	fwrite($feedHandle, $_document);
	rewind($feedHandle);

	$parameters = array (
		  'Marketplace' => $marketplace_id,
		  'Merchant' => $merchant_id,
		  'FeedType' => $feedType,
		  'FeedContent' => $feedHandle,
		  'PurgeAndReplace' => false,
		  'ContentMd5' => base64_encode(md5(stream_get_contents($feedHandle), true)),
		);

	rewind($feedHandle);

	$request = new MarketplaceWebService_Model_SubmitFeedRequest($parameters);

	$this->_result = $this->invokeSubmitFeed($service, $request);
	@fclose($feedHandle);
    }     


    /**
     * Format amount value (2 digits after the decimal point)
     *
     * @param float $amount
     * @return float
     */
    public function formatAmount($amount)
    {
        return Mage::helper('amazonpayments')->formatAmount($amount);
    }


    /**
     * Associate Magento real order id with Amazon order id
     *
     * @param Mage_Sales_Model_Order $order
     * @return string
     * Modified to use MWS instead of SOAP
     */
    public function sendAcknowledgement($order)
    {
        $_document = '<?xml version="1.0" encoding="UTF-8"?>
        <AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
        <Header>
            <DocumentVersion>1.01</DocumentVersion>
            <MerchantIdentifier>' . $this->getMerchantIdentifier() . '</MerchantIdentifier>
        </Header>
        <MessageType>OrderAcknowledgement</MessageType>
            <Message>
                <MessageID>1</MessageID>
                <OperationType>Update</OperationType>
                <OrderAcknowledgement>
                    <AmazonOrderID>' . $order->getExtOrderId() . '</AmazonOrderID>
                    <MerchantOrderID>' . $order->getRealOrderId() . '</MerchantOrderID>
                    <StatusCode>Success</StatusCode>
                </OrderAcknowledgement>
            </Message>
        </AmazonEnvelope>';
	
	
        $this->_processMWSRequest($_document,"_POST_ORDER_ACKNOWLEDGEMENT_DATA_");
        return $this->_result;
    }


    /**
     * Cancel order
     *
     * @param Mage_Sales_Model_Order $order
     * @return string Amazon Transaction Id
     * Modified to use MWS instead of SOAP 
     */
    public function cancel($order)
    {
        $_document=
	'<?xml version="1.0" encoding="UTF-8"?>
        <AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
        <Header>
            <DocumentVersion>1.01</DocumentVersion>
            <MerchantIdentifier>'.$this->getMerchantIdentifier().'</MerchantIdentifier>
        </Header>
        <MessageType>OrderAcknowledgement</MessageType>
            <Message>
                <MessageID>1</MessageID>
                <OperationType>Update</OperationType>
                <OrderAcknowledgement>
                    <AmazonOrderID>'.$order->getExtOrderId().'</AmazonOrderID>
                    <MerchantOrderID>'.$order->getRealOrderId().'</MerchantOrderID>
                    <StatusCode>Failure</StatusCode>
                </OrderAcknowledgement>
            </Message>
        </AmazonEnvelope>';
	
	
        $this->_processMWSRequest($_document,"_POST_ORDER_ACKNOWLEDGEMENT_DATA_");
	Mage::log("Order cancel request sent with reference ID " . $this->_result . " !");
	//Adding reference id to order comments for polling later
	$comment=$order->addStatusToHistory(
                 Mage_Sales_Model_Order::STATE_PROCESSING,
                 Mage::helper('amazonpayments')->__('Cancelled Reference ID:'.$this->_result.' and Amazon order ID:'.$order->getExtOrderId()))->save();
	
        return $this->_result;
    }

    /**
     * Refund order
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return string Amazon Transaction Id
     * Modified to use MWS instead of SOAP 
     */
    public function refund($payment, $amount)
    {
        $_document = '<?xml version="1.0" encoding="UTF-8"?>
            <AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
            <Header>
                <DocumentVersion>1.01</DocumentVersion>
                <MerchantIdentifier>' . $this->getMerchantIdentifier() . '</MerchantIdentifier>
            </Header>
            <MessageType>OrderAdjustment</MessageType>';

        $_shippingAmount = $payment->getCreditmemo()->getShippingAmount();
        $_messageId = 1;
        foreach ($payment->getCreditmemo()->getAllItems() as $item) {
            /* @var $item Mage_Sales_Model_Order_Creditmemo_Item */
            if ($item->getOrderItem()->getParentItemId()) {
                continue;
            }
	    
	        $shipping = 0;
            $amazon_amounts = unserialize($item->getOrderItem()->getProductOptionByCode('amazon_amounts'));
            if ($amazon_amounts['shipping'] > $_shippingAmount) {
                $shipping = $_shippingAmount;
            } else {
                $shipping = $amazon_amounts['shipping'];
            }
            $_shippingAmount -= $shipping;
	    $currency = Mage::getModel('amazonpayments/api_cba')->getCurrencyFormat();
            $_document .= '<Message>
                            <MessageID>' . $_messageId . '</MessageID>
                            <OrderAdjustment>
                                <AmazonOrderID>' . $payment->getOrder()->getExtOrderId() . '</AmazonOrderID>
                                <AdjustedItem>
                                    <AmazonOrderItemCode>'. $item->getOrderItem()->getExtOrderItemId() . '</AmazonOrderItemCode>
                                    <AdjustmentReason>GeneralAdjustment</AdjustmentReason>
                                    <ItemPriceAdjustments>
                                        <Component>
                                            <Type>Principal</Type>
                                            <Amount currency="'.$currency .'">' . $this->formatAmount($item->getBaseRowTotal()) . '</Amount>
                                        </Component>
                                        <Component>
                                            <Type>Tax</Type>
                                            <Amount currency="'.$currency .'">' . $this->formatAmount($amazon_amounts['tax']+$amazon_amounts['shipping_tax']) . '</Amount>
                                        </Component>'
                                        .'<Component>
                                            <Type>Shipping</Type>
                                            <Amount currency="'.$currency .'">' . $this->formatAmount($shipping) . '</Amount>
                                        </Component>'
                                    .'</ItemPriceAdjustments>';
            $_document .= '</AdjustedItem>
                        </OrderAdjustment>
                    </Message>';
            $_messageId++;
        }

        $_document .= '</AmazonEnvelope>';
        
        $this->_processMWSRequest($_document,"_POST_PAYMENT_ADJUSTMENT_DATA_");
	$comment=$payment->getOrder()->addStatusToHistory(
                    Mage_Sales_Model_Order::STATE_PROCESSING,
                    Mage::helper('amazonpayments')->__('Refund Reference ID:'.$this->_result.' and Amazon order ID:'. $payment->getOrder()->getExtOrderId()))->save();
        return $this->_result;
    }

    /**
     * Confirm creating of shipment
     *
     * @param string $aOrderId
     * @param string $carrierName
     * @param string $shippingMethod
     * @param array $items
     * @param string $trackNumber
     * @return string Amazon Transaction Id
     * Modified to use MWS instead of SOAP 
     */
    public function confirmShipment($aOrderId, $carrierName, $shippingMethod, $items, $trackNumber = '')
    {
        $fulfillmentDate = gmdate('Y-m-d\TH:i:s');
        $_document = '<?xml version="1.0" encoding="UTF-8"?>
            <AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
            <Header>
                <DocumentVersion>1.01</DocumentVersion>
                <MerchantIdentifier>' . $this->getMerchantIdentifier() . '</MerchantIdentifier>
            </Header>
            <MessageType>OrderFulfillment</MessageType>
            <Message>
                <MessageID>1</MessageID>
                <OrderFulfillment>
                    <AmazonOrderID>' . $aOrderId . '</AmazonOrderID>
                    <FulfillmentDate>' . $fulfillmentDate . '</FulfillmentDate>
                    <FulfillmentData>
                        <CarrierName>' . strtoupper($carrierName) . '</CarrierName>
                        <ShippingMethod>' . $shippingMethod . '</ShippingMethod>
                        <ShipperTrackingNumber>' . $trackNumber .'</ShipperTrackingNumber>
                    </FulfillmentData>';
        foreach ($items as $item) {
            $_document .= '<Item>
                            <AmazonOrderItemCode>' . $item['id'] . '</AmazonOrderItemCode>
                            <Quantity>' . $item['qty'] . '</Quantity>
                        </Item>';
        }
        $_document .= '</OrderFulfillment>
                </Message>
        </AmazonEnvelope>';
        
        $this->_processMWSRequest($_document,"_POST_ORDER_FULFILLMENT_DATA_");
	$order=Mage::getModel('sales/order')
                                ->loadByAttribute('ext_order_id',$aOrderId);
	$comment=$order->addStatusToHistory(
                    Mage_Sales_Model_Order::STATE_PROCESSING,
                    Mage::helper('amazonpayments')->__('Shipment reference ID:'.$this->_result.' and Amazon order ID:'.$aOrderId))->save();
        return $this->_result;
    }

    /**
     * Send Tracking Number
     *
     * @param Mage_Sales_Model_Order $order
     * @param string $carrierCode
     * @param string $carrierMethod

     * @return string Amazon Transaction Id
     */
    public function sendTrackNumber($order, $carrierCode, $carrierMethod, $trackNumber)
    {
        $fulfillmentDate = gmdate('Y-m-d\TH:i:s');
        $_document = '<?xml version="1.0" encoding="UTF-8"?>
            <AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
            <Header>
                <DocumentVersion>1.01</DocumentVersion>
                <MerchantIdentifier>' . $this->getMerchantIdentifier() . '</MerchantIdentifier>
            </Header>
            <MessageType>OrderFulfillment</MessageType>';
            $_document .= '<Message>
                    <MessageID>1</MessageID>
                    <OrderFulfillment>
                        <AmazonOrderID>' . $order->getExtOrderId() . '</AmazonOrderID>
                        <FulfillmentDate>' . $fulfillmentDate . '</FulfillmentDate>
                        <FulfillmentData>
                            <CarrierCode>' . $carrierCode . '</CarrierCode>
                            <ShippingMethod>' . $carrierMethod . '</ShippingMethod>
                            <ShipperTrackingNumber>' . $trackNumber .'</ShipperTrackingNumber>
                        </FulfillmentData>
                    </OrderFulfillment>
                </Message>';
        $_document .= '</AmazonEnvelope>';
        
        $this->_processMWSRequest($_document,"_POST_ORDER_FULFILLMENT_DATA_");
	Mage::log("Order tracking number update request sent with reference ID " . $this->_result . " !");
        return $this->_result;
    }

/**
  * Submit Feed Action Sample
  * Uploads a file for processing together with the necessary
  * metadata to process the file, such as which type of feed it is.
  * PurgeAndReplace if true means that your existing e.g. inventory is
  * wiped out and replace with the contents of this feed - use with
  * caution (the default is false).
  *   
  * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
  * @param mixed $request MarketplaceWebService_Model_SubmitFeed or array of parameters
  * Added  to submit feeds through MWS (uses Amazon's MWS PHP SDK in magento/lib)
  */
  function invokeSubmitFeed(MarketplaceWebService_Interface $service, $request)
  {
      try {
              $response = $service->submitFeed($request);
	      $feedSubmissionId= null;

                if ($response->isSetSubmitFeedResult()) { 
                    $submitFeedResult = $response->getSubmitFeedResult();
                    if ($submitFeedResult->isSetFeedSubmissionInfo()) {
                        $feedSubmissionInfo = $submitFeedResult->getFeedSubmissionInfo();
                        if ($feedSubmissionInfo->isSetFeedSubmissionId())
                        {
							$feedSubmissionId =  $feedSubmissionInfo->getFeedSubmissionId();
                        }
                    }
                }
                
     } catch (MarketplaceWebService_Exception $ex) {
         Mage::log("Caught Exception: " . $ex->getMessage() . "\n");
         Mage::log("Response Status Code: " . $ex->getStatusCode() . "\n");
         Mage::log("Error Code: " . $ex->getErrorCode() . "\n");
         Mage::log("Error Type: " . $ex->getErrorType() . "\n");
         Mage::log("Request ID: " . $ex->getRequestId() . "\n");
         Mage::log("XML: " . $ex->getXML() . "\n");
	 return null;
     }

     return $feedSubmissionId;
  }


/**
* Function to get the status of the feed submitted through MWS identified by 
* ReferenceID returned when feed was submitted.
* @param referenceId string
* @returns true: if the feed's processing was successful
*	   false: if the feed's processing was pending/unsuccessful with errors
* Added  for checking the status of the submitted requests (used by polling)
*/
public function getRequestStatus($referenceId)
{
	$country=Mage::getStoreConfig('payment/amazonpayments_cba/country');

	if($country == "US")
	{
		$serviceUrl = Mage::getStoreConfig('payment/amazonpayments_cba/mws_service_url_US');
		$marketplace_id = Mage::getStoreConfig('payment/amazonpayments_cba/marketplace_id_US');
	}
	elseif($country == "UK")
	{
		$serviceUrl = Mage::getStoreConfig('payment/amazonpayments_cba/mws_service_url_UK');
		$marketplace_id = Mage::getStoreConfig('payment/amazonpayments_cba/marketplace_id_UK');
	}
	elseif($country == "DE")
	{
		$serviceUrl = Mage::getStoreConfig('payment/amazonpayments_cba/mws_service_url_DE');
		$marketplace_id = Mage::getStoreConfig('payment/amazonpayments_cba/marketplace_id_DE');
	}

        Mage::log("In getStatusRequest:");
        
	$application_name='Magento CBA Plugin';
        $application_version=Mage::getVersion();
	$merchant_id = Mage::getStoreConfig('payment/amazonpayments_cba/merchant_id');
        
        $config = array (
                          'ServiceURL' => $serviceUrl,
                          'ProxyHost' => null,
                          'ProxyPort' => -1,
                          'MaxErrorRetry' => 3,
                        );
        
        /************************************************************************
         * Instantiate Implementation of MarketplaceWebService
         * 
         * AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY constants 
         * are defined in the .config.inc.php located in the same 
         * directory as this sample
         ***********************************************************************/
        $service = new MarketplaceWebService_Client(
                             $this->getMwsAccessKey(),
                             $this->getMwsSecretKey(),
                             $config,
                             $application_name,
                             $application_version);

        $feedHandle = @fopen('php://memory','rw+');

        $parameters = array (
                  'Marketplace' => $marketplace_id,
                  'Merchant' => $merchant_id,
                  'FeedSubmissionId' => $referenceId,
                  'FeedSubmissionResult' => $feedHandle
                );
	$request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest($parameters);
	
	$processingStatus = false;
	try {

	        $response = $service->getFeedSubmissionResult($request);
	} catch (MarketplaceWebService_Exception $ex) {
         Mage::log("Caught Exception: " . $ex->getMessage() . "\n");
         Mage::log("Response Status Code: " . $ex->getStatusCode() . "\n");
         Mage::log("Error Code: " . $ex->getErrorCode() . "\n");
         Mage::log("Error Type: " . $ex->getErrorType() . "\n");
         Mage::log("Request ID: " . $ex->getRequestId() . "\n");
         Mage::log("XML: " . $ex->getXML() . "\n");

         @fclose($feedHandle);
	 return $processingStatus; //returns false in case of request still in pending status

     	}

        rewind($feedHandle);
        
        $xml = simplexml_load_string(stream_get_contents($feedHandle),'Varien_Simplexml_Element');
	
        if ($xml->descend('Message/ProcessingReport/ProcessingSummary/MessagesSuccessful') == "1")
        {
                Mage::log ("Success");
		$processingStatus = true; //Sets status to true if and only if the feed was successfully processed
        }
	
	if ($processingStatus)
	{
		Mage::log("Returning the state of ".$referenceId." !");
	}
        @fclose($feedHandle);
	return $processingStatus;

}

/**
* Function to implment polling of status of requests
*/
public function statusUpdate()
{
	$maxSize = 57;
	$write = Mage::getSingleton('core/resource')->getConnection('core_write');
	Mage::log("In statusUpdate function");	
	
//For getting the order count
	$cancelledOrderResult=$write->query("SELECT count(entity_id) as count FROM  sales_flat_order_status_history b WHERE  b.status = 'processing' and b.comment LIKE 'Cancelled Reference ID:%'");
	$cancelOrderArr = $cancelledOrderResult->fetch();
        $shippedOrderResult=$write->query("SELECT count(entity_id) as count FROM  sales_flat_order_status_history b WHERE  b.status = 'processing' and b.comment LIKE 'Shipment reference ID:%'");
        $shippedOrderArr = $shippedOrderResult->fetch();
        $refundOrderResult=$write->query("SELECT count(entity_id) as count FROM  sales_flat_order_status_history b WHERE  b.status = 'processing' and b.comment LIKE 'Refund reference ID:%'");
        $refundOrderArr = $refundOrderResult->fetch();

	$split = $maxSize/3;
	$cancelOrderCount = $cancelOrderArr["count"];
	$shippedOrderCount = $shippedOrderArr["count"];
	$refundOrderCount = $refundOrderArr["count"];
	if($cancelOrderCount<$split)
	{
		if($refundOrderCount<$split)
		{
			$shippedOrderCount= $maxSize-($cancelOrderCount+$refundOrderCount);
		
		}
		else if($shippedOrderCount<$split)
		{
			$refundOrderCount=$maxSize-($cancelOrderCount+$shippedOrderCount);
		}
		else
		{
			$refundOrderCount=($maxSize-$cancelOrderCount)/2;
			$shippedOrderCount=($maxSize-$cancelOrderCount)/2;		
		}
		
	}
	else if($refundOrderCount<$split)
	{
		if($cancelOrderCount<$split)
		{
			$shippedOrderCount= $maxSize-($cancelOrderCount+$refundOrderCount);
		}
		else if($shippedOrderCount<$split)
		{
			$cancelOrderCount=$maxSize-($refundOrderCount+$shippedOrderCount);
		}
		else
		{
			$cancelOrderCount=($maxSize-$refundOrderCount)/2;
			$shippedOrderCount=($maxSize-$refundOrderCount)/2;		
		}
		
	}	
	else if($shippedOrderCount<$split)
	{
		if($refundOrderCount<$split)
		{
			$cancelOrderCount= $maxSize-($shippedOrderCount+$refundOrderCount);
		}
		else if($cancelOrderCount<$split)
		{
			$refundOrderCount=$maxSize-($cancelOrderCount+$shippedOrderCount);
		}
		else
		{
			$refundOrderCount=($maxSize-$shippedOrderCount)/2;
			$cancelOrderCount=($maxSize-$shippedOrderCount)/2;		
		}
		
	}	
	else
	{
		$cancelOrderCount=$split;
		$refundOrderCount=$split;
		$shippedOrderCount=$split;

	}

	//For Cancel polling

	$query = "SELECT COMMENT FROM  sales_flat_order_status_history b WHERE  b.status = 'processing' and b.comment LIKE 'Cancelled Reference ID:%'  order by created_at limit ".$cancelOrderCount;
        $readresult=$write->query($query);
	while ($row = $readresult->fetch() ) 
	{
		$rawReferenceId = explode(":", $row['COMMENT']);
                $referenceIdWithSpace= explode("and",$rawReferenceId[1]);
                $referenceId=explode(" ",$referenceIdWithSpace[0]);
                if($this->getRequestStatus($referenceId[0]))
                {
                        $order=Mage::getModel('sales/order')
                                ->loadByAttribute('ext_order_id',$rawReferenceId[2]);
			if(! $order->getStatus()==Mage_Sales_Model_Order::STATE_CANCELED)
			{
				$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED,
                   		 Mage::helper('amazonpayments')->__('Cancelled Notification received from status update of Checkout by Amazon service for OrderID:'.$rawReferenceId[2]))->save();
			}
			$query = "UPDATE sales_flat_order_status_history b set COMMENT = 'Amazon ". $row['COMMENT']."'  WHERE COMMENT= '". $row['COMMENT']."'";
			$write->query($query);
                }
        }

	//For Shipment polling
	$query = "SELECT COMMENT FROM  sales_flat_order_status_history b WHERE  b.status = 'processing' and b.comment LIKE 'Shipment reference ID:%'  order by created_at limit ".$shippedOrderCount;
	$readresult=$write->query($query);
        while ($row = $readresult->fetch() ) 
	{
                $rawReferenceId = explode(":", $row['COMMENT']);
		$referenceIdWithSpace= explode("and",$rawReferenceId[1]);
		$referenceId=explode(" ",$referenceIdWithSpace[0]);

		if($this->getRequestStatus($referenceId[0]))
                {
                        $order=Mage::getModel('sales/order')
                                ->loadByAttribute('ext_order_id',$rawReferenceId[2]);
                        $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE,
                   		 Mage::helper('amazonpayments')->__('Shipment complete notification received from status update of Checkout by Amazon service for OrderID:'.$rawReferenceId[2]))->save();
			$query = "UPDATE sales_flat_order_status_history b set COMMENT = 'Amazon ". $row['COMMENT']."'  WHERE COMMENT= '". $row['COMMENT']."'";
			$write->query($query);                                                                              
                }
        }
	
	//For Refund Polling
	$query = "SELECT COMMENT FROM  sales_flat_order_status_history b WHERE  b.status = 'processing' and b.comment LIKE 'Refund reference ID:%' order by created_at limit ".$refundOrderCount;
	$readresult=$write->query($query);
        while ($row = $readresult->fetch()) 
	{
		$rawReferenceId = explode(":", $row['COMMENT']);
         	$referenceIdWithSpace= explode("and",$rawReferenceId[1]);
                $referenceId=explode(" ",$referenceIdWithSpace[0]);

		if($this->getRequestStatus($referenceId[0]))
                {
                        $order=Mage::getModel('sales/order')
                                ->loadByAttribute('ext_order_id',$rawReferenceId[2]);
                        $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE,
                    		Mage::helper('amazonpayments')->__('Refund complete notification received from status update of Checkout by Amazon service for OrderID:'.$rawReferenceId[2]))->save();
			$query = "UPDATE sales_flat_order_status_history b set COMMENT = 'Amazon ". $row['COMMENT']."'  WHERE COMMENT= '". $row['COMMENT']."'";
			$write->query($query);
                }
        }
}
}
