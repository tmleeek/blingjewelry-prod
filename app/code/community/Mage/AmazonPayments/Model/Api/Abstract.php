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
 * AmazonPayments Base API Model
 *
 * @category   Mage
 * @package    Mage_AmazonPayments
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_AmazonPayments_Model_Api_Abstract extends Varien_Object
{
    /*
     * payment actions
     */
    const PAYMENT_TYPE_AUTH  = 'Authorization';
    const USER_ACTION_COMMIT = 'commit';

    /*
     * signature generation algorithm name
     */
    protected static $HMAC_SHA1_ALGORITHM = 'sha1';

    /*
     * payment module code
     */
    protected $paymentCode;

    /**
     * Set payment module code
     *
     * @return string
     */
    public function setPaymentCode($paymentCode)
    {
        if(is_null($this->paymentCode)) {
            $this->paymentCode = $paymentCode;
            $this->setData(Mage::getStoreConfig('payment/' . $paymentCode));
        }
    }

    // Wrapper over the counry configuration parameter 
    public function getCountry()
    {
         return Mage::getStoreConfig('payment/amazonpayments_cba/country'); 
    }
   
    // Choose the currency on the basis of country configured
    public function getCurrencyFormat()
    {
      $country = $this->getCountry();
      
      if($country == "US" )
         return "USD";
      else if($country == "UK" )
         return "GBP";
      else if($country == "DE" )
         return "EUR" ;
      else
         Mage::log("Invalid country Configured , please correct the configuration"); 
   }
   
   // Symbols will be used to show in Displayshippinglables
   public function getCurrencySymbol($curr)
   {
                if ($curr=="USD")      
                        $currSymbol="$"; 
                elseif ($curr=="GBP")
                        $currSymbol="£";
                elseif ($curr=="EUR")
                        $currSymbol="€"; 
                else Mage::log("Invalid country Configured , please correct the configuration");

        return $currSymbol;
   }

    // Choose the weight unit on the basis of country configured
    public function getWeightUnit()
    {
      $country = $this->getCountry();
      
      if($country == "US" )
         return "lb";
      else if($country == "UK" || $country == "DE" )
         return "kg";
      else
         Mage::log("Invalid country Configured , please correct the configuration"); 
   }

    /**
     * Return payment url
     *
     * @return string
     */
 
    // Pay Service Url is choosed on the basis of the country , options are there to cinfigure pay service urls for all countries
    public function getPayServiceUrl()
    {
        $Country = $this->getCountry();
        if ($this->getSandboxFlag()) {
            return $this->getData('sandbox_pay_service_url_'.$Country);
        }
        return $this->getData('pay_service_url_'.$Country);
    }

    /**
     * Get value from the payment module config
     *
     * @param string $kay
     * @param string $default
     * @return string
     */
    public function getConfigData($key, $default = false)
    {
        if (!$this->hasData($key)) {
             $value = Mage::getStoreConfig('payment/' . $paymentCode . '/' . $key);
             if (is_null($value) || false===$value) {
                 $value = $default;
             }
            $this->setData($key, $value);
        }
        return $this->getData($key);
    }

    /**
     * Check signed params
     *
     * @param array $params
     * @return bool
     */
    public function checkSignParams($params)
    {
        if (is_array($params) && isset($params[$this->getRequestSignatureParamName()])) {
            $paramSignature = $params[$this->getRequestSignatureParamName()];
            unset($params[$this->getRequestSignatureParamName()]);
            $generateSignature = $this->_getSignatureForArray($params, $this->getSecretKey());
            return $paramSignature == $generateSignature;
        }

        return false;
    }

    /**
     * Add signature param to params array
     *
     * @param array $params
     * @return array
     */
    public function signParams($params)
    {
        $signature = $this->_getSignatureForArray($params, $this->getSecretKey());
        $params[$this->getRequestSignatureParamName()] = $signature;
        return $params;
    }

    /**
     * Return signature for array
     *
     * @param array $array
     * @param string $secretKey
     * @return array
     */
    protected function _getSignatureForArray($array, $secretKey)
    {
        uksort($array, 'strcasecmp');
        $tmpString = '';
        foreach ($array as $paramName => $paramValue) {
           $tmpString = $tmpString . $paramName . $paramValue;
        }
        return $this->_getSignatureForString($tmpString, $secretKey);
    }

    /**
     * Return signature for string
     *
     * @param string $string
     * @param string $secretKey
     * @return string
     */
    protected function _getSignatureForString($string, $secretKey)
    {
        $rawHmac = hash_hmac(self::$HMAC_SHA1_ALGORITHM, $string, $secretKey, true);
        return base64_encode($rawHmac);
    }
}
