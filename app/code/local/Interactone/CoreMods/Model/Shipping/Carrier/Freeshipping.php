<?php
/**
 * @category Interactone
 * @package Interactone_CoreMods
 * @author Alexey Poletaev (alexey.poletaev@cyberhull.com)
 */
class Interactone_CoreMods_Model_Shipping_Carrier_Freeshipping
    extends Mage_Shipping_Model_Carrier_Freeshipping
{
    /**
     * @see Mage_Shipping_Model_Carrier_Freeshipping::collectRates()
     * @author Alexey Poletaev (alexey.poletaev@cyberhull.com)
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');

        $this->_updateFreeMethodQuote($request);
        //Ashwani Bhasin 11/10/2014 changed getBaseSubtotalInclTax() and replace it to getPackageValueWithDiscount()
        if (($request->getFreeShipping())
                || ($request->getPackageValueWithDiscount() >=
                        $this->getConfigData('free_shipping_subtotal'))
        ) {
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier('freeshipping');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('freeshipping');
            $method->setMethodTitle($this->getConfigData('name'));

            $method->setPrice('0.00');
            $method->setCost('0.00');

            $result->append($method);
        }

        return $result;
    }
}