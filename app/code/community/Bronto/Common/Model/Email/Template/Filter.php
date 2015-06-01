<?php

/**
 * @package     Bronto\Email
 * @copyright   2011-2012 Bronto Software, Inc.
 */
class Bronto_Common_Model_Email_Template_Filter extends Mage_Core_Model_Email_Template_Filter
{
    /**
     * @var Bronto_Api_Delivery_Row
     */
    protected $_delivery;

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * @var string
     */
    protected $_messageId;

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_currency;

    /**
     * Assigned template variables
     *
     * @var array
     */
    protected $_variables = array();

    /**
     * Available template variables
     *
     * @var array
     */
    protected $_available = array();

    /**
     * @var array
     */
    protected $_processedAvailable = array();

    /**
     * @var array
     */
    protected $_filteredObjects = array();

    /**
     * @var array
     */
    protected $_queryParams = array();

    /**
     * Map of keys that we would rather have a pretty name for.
     * Rather than a 25 character truncated value.
     *
     * @var array
     */
    protected $_prettyMap = array(
        'subscriberConfirmationLink' => 'subConfirmationLink'
    );

    /**
     * @return array
     */
    protected function _processAvailable()
    {
        $this->_processedAvailable = array();

        foreach ($this->_available as $available) {
            $variable = isset($available['value']) ? $available['value'] : null;
            if (preg_match('/^{{layout handle="[a-zA-Z_]*_order_items"/', $variable)) {
                continue;
            }
            if (preg_match('/^{{skin|store|layout|block/', $variable)) {
                continue;
            }

            $variable = str_replace('{{var ', '', $variable);
            $variable = str_replace('{{htmlescape var=$', '', $variable);
            $variable = str_replace('}}', '', $variable);

            $parts = explode('.', $variable);
            foreach ($parts as $i => $part) {
                if (stripos($part, 'get') === 0) {
                    $parts[$i] = str_replace('get', '', $parts[$i]);
                    $parts[$i] = str_replace('()', '', $parts[$i]);
                }
                if (stripos($part, 'format') === 0) {
                    unset($parts[$i]);
                }
            }

            $variable                    = implode('_', $parts);
            $this->_processedAvailable[] = $this->_camelize($variable);

        }

        return $this->_processedAvailable;
    }

    /**
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    protected function _processQueryParams()
    {
        $this->_queryParams = array();

        // Add rule_id (if available)
        if (isset($this->_variables['rule'])) {
            if (class_exists('Bronto_Reminder_Model_Rule', false) && $this->_variables['rule'] instanceOf Bronto_Reminder_Model_Rule) {
                $this->_queryParams['rule_id'] = $this->_variables['rule']->getId();
            }
        }

        // Add message_id (if available)
        if ($this->getMessageId()) {
            $this->_queryParams['message_id'] = $this->getMessageId();
        }

        return $this;
    }

    /**
     * @param Bronto_Api_Delivery_Row $delivery
     *
     * @return Bronto_Api_Delivery_Row
     */
    public function filter($delivery)
    {
        if (!$delivery instanceof Bronto_Api_Delivery_Row) {
            return parent::filter($delivery);
        }

        $this->_filteredObjects = array();
        $this->_delivery        = $delivery;

        $this->_processAvailable();
        $this->_processQueryParams();

        foreach ($this->_variables as $var => $value) {

            //
            // Handle strings
            if (is_string($value)) {
                $key = $this->_camelize($var);
                if (in_array($key, $this->_processedAvailable)) {
                    $this->setField($key, $value);
                } else {
                    // Sanitize the best we can...
                    $key = preg_replace('/[^\w_]$/', '', $key);
                    $key = $this->_camelize($key);
                    $this->setField($key, $value);
                }
            }

            if (is_object($value)) {

                //
                // Handle properties that can be get()'ed
                foreach ($this->_processedAvailable as $keyValue) {
                    $method = str_replace($var, '', $keyValue);
                    $object = str_replace($method, '', $keyValue);
                    if ($object == $var) {
                        try {
                            $method = "get{$method}";
                            $this->setField($keyValue, $value->{$method}());
                        } catch (Exception $e) {
                            // Ignore
                        }
                    }
                }

                // Coupon
                if ($value instanceof Mage_SalesRule_Model_Coupon) {
                    $this->_filterCoupon($value);
                }

                // Store
                if ($value instanceOf Mage_Core_Model_Store) {
                    $this->_filterStore($value);
                }

                // Admin User
                if ($value instanceOf Mage_Admin_Model_User) {
                    $this->_filterAdmin($value);
                }

                // Subscriber
                if ($value instanceOf Mage_Newsletter_Model_Subscriber) {
                    $this->_filterSubscriber($value);
                }

                // Customer
                if ($value instanceOf Mage_Customer_Model_Customer) {
                    $this->_filterCustomer($value);
                }

                // Shipment
                if ($value instanceOf Mage_Sales_Model_Order_Shipment) {
                    $this->_filterShipment($value);
                }

                // Invoice
                if ($value instanceOf Mage_Sales_Model_Order_Invoice) {
                    $this->_filterInvoice($value);
                }

                // Order
                if ($value instanceOf Mage_Sales_Model_Order) {
                    $this->_filterOrder($value);
                }

                // Credit memo
                if ($value instanceOf Mage_Sales_Model_Order_Creditmemo) {
                    $this->_filterCreditmemo($value);
                }

                // Quote
                if ($value instanceOf Mage_Sales_Model_Quote) {
                    $this->_filterQuote($value);
                }

                // Wishlist
                if ($value instanceOf Mage_Wishlist_Model_Wishlist) {
                    $this->_filterWishlist($value);
                }

                // Product
                if ($value instanceOf Mage_Catalog_Model_Product) {
                    $this->_filterProduct($value);
                }

                if ($value instanceof Mage_Sales_Model_Order_Address) {
                    $this->_filterAddress($value);
                }

            }

        }

        return $this->_delivery;
    }

    /**
     * Add Coupon Code to Email
     *
     * @param Mage_SalesRule_Model_Coupon $coupon
     *
     * @return $this
     */
    protected function _filterCoupon(Mage_SalesRule_Model_Coupon $coupon)
    {
        if (!in_array('coupon', $this->_filteredObjects)) {
            $this->setField('couponCode', $coupon->getCode());
            $this->_filteredObjects[] = 'coupon';
        }

        return $this;
    }

    /**
     * @param Mage_Core_Model_Store $store
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    protected function _filterStore(Mage_Core_Model_Store $store)
    {
        if (!in_array('store', $this->_filteredObjects)) {
            $this->setStore($store);
            $this->setField('storeName', $store->getName());
            $this->setField('storeFrontendName', $store->getFrontendName());
            $this->setField('storeURL', $store->getUrl('cms', $this->getQueryParams()));
            $this->setField('cartURL', $store->getUrl('checkout/cart', $this->getQueryParams()));
            $this->setField('customerURL', $store->getUrl('customer/account', $this->getQueryParams()));
            $this->setField('supportEmail', $store->getConfig('trans_email/ident_support/email'));
            $this->setField('supportPhone', $store->getConfig('general/store_information/phone'));
            $this->setField('salesEmail', $store->getConfig('trans_email/ident_sales/email'));

            // if the theme is not set at all (not a likely occurrence in a real site)
            // then it returns the theme for the Find (RSS feed).
            $theme = Mage::getSingleton('core/design_package')->getTheme('skin');
            if ($theme == 'find') {
                $theme = 'default';
            }
            $package = Mage::getSingleton('core/design_package')->getPackageName();
            $this->setField('emailLogo', Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'frontend' . DS . $package . DS . $theme . DS . 'images/logo_email.gif');

            $this->_filteredObjects[] = 'store';
        }

        return $this;
    }

    /**
     * @param Mage_Admin_Model_User $user
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    protected function _filterAdmin(Mage_Admin_Model_User $user)
    {
        if (!in_array('admin', $this->_filteredObjects)) {
            $this->setField('adminName', $user->getUsername());
            $this->setField('adminPassword', $user->getPlainPassword());
            $this->setField('adminLoginURL', Mage::helper('adminhtml')->getUrl('adminhtml/system_account/'));
            if (version_compare(Mage::getVersion(), '1.6.1.0', '>=')) {
                $this->setField('adminPasswordResetLink', Mage::helper('adminhtml')->getUrl('adminhtml/index/resetpassword', array('_query' => array('id' => $user->getId(), 'token' => $user->getRpToken()))));
            }

            $this->_filteredObjects[] = 'admin';
        }

        return $this;
    }

    /**
     * @param Mage_Newsletter_Model_Subscriber $subscriber
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    protected function _filterSubscriber(Mage_Newsletter_Model_Subscriber $subscriber)
    {
        if (!in_array('subscriber', $this->_filteredObjects)) {
            $customer = Mage::getModel('customer/customer')->load($subscriber->getCustomerId());

            $this->_filterCustomer($customer);
            $this->_filteredObjects[] = 'subscriber';
        }

        return $this;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    protected function _filterCustomer(Mage_Customer_Model_Customer $customer)
    {
        if (!in_array('customer', $this->_filteredObjects)) {
            // Handle Defaults from settings
            $customerName      = (trim($customer->getName()) == '') ? Mage::helper('bronto_common')->getDefaultGreeting('full', 'store', $this->getStoreId()) : $customer->getName();
            $customerPrefix    = (trim($customer->getPrefix()) == '') ? Mage::helper('bronto_common')->getDefaultGreeting('prefix', 'store', $this->getStoreId()) : $customer->getPrefix();
            $customerFirstName = (trim($customer->getFirstname()) == '') ? Mage::helper('bronto_common')->getDefaultGreeting('firstname', 'store', $this->getStoreId()) : $customer->getFirstname();
            $customerLastName  = (trim($customer->getLastname()) == '') ? Mage::helper('bronto_common')->getDefaultGreeting('lastname', 'store', $this->getStoreId()) : $customer->getLastname();

            $this->setField('customerName', $customerName);
            $this->setField('firstName', $customerFirstName);
            $this->setField('prefix', $customerPrefix);
            $this->setField('lastName', $customerLastName);
            $this->setField('customerEmail', $customer->getEmail());
            $this->setField('customerPassword', $customer->getPassword());
            if ($store = $customer->getStore()) {
                $this->setField('confirmationLink', $store->getUrl('customer/account/confirm', array('_query' => array('id' => $customer->getId(), 'key' => $customer->getConfirmation()))));
                if (version_compare(Mage::getVersion(), '1.6.1.0', '>=')) {
                    $this->setField('passwordResetLink', $store->getUrl('customer/account/resetpassword', array('_query' => array('id' => $customer->getId(), 'token' => $customer->getRpToken()))));
                }
            } else {
                $this->setField('confirmationLink', Mage::getUrl('customer/account/confirm', array('_query' => array('id' => $customer->getId(), 'key' => $customer->getConfirmation()))));
                if (version_compare(Mage::getVersion(), '1.6.1.0', '>=')) {
                    $this->setField('passwordResetLink', Mage::getUrl('customer/account/resetpassword', array('_query' => array('id' => $customer->getId(), 'token' => $customer->getRpToken()))));
                }
            }

            $this->_filteredObjects[] = 'customer';
        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param string                 $type
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    protected function _filterOrder(Mage_Sales_Model_Order $order, $type = 'order')
    {
        if (!in_array('order', $this->_filteredObjects)) {
            $this->setStoreId($order->getStoreId());

            $index = 1;
            foreach ($order->getAllItems() as $item/* @var $item Mage_Sales_Model_Order_Item */) {
                if (!$item->getParentItem()) {
                    $this->_filterOrderItem($item, $index);
                    $index++;
                }
            }

            // Order may not be a shippable order
            $shipAddress     = 'N/A';
            $shipDescription = 'N/A';
            if ($order->getIsNotVirtual()) {
                $shipAddress     = $order->getShippingAddress()->format('html');
                $shipDescription = $order->getShippingDescription();
            }

            // Check for guest orders
            $customerName = $order->getCustomerIsGuest() ? $order->getBillingAddress()->getName() : $order->getCustomerName();

            $this->setField('orderIncrementId', $order->getIncrementId());
            $this->setField('orderCreatedAt', $order->getCreatedAtFormated('long'));
            $this->setField('orderBillingAddress', $order->getBillingAddress()->format('html'));
            $this->setField('orderShippingAddress', $shipAddress);
            $this->setField('orderShippingDescription', $shipDescription);
            $this->setField('orderCustomerName', $customerName);
            $this->setField('orderStatusLabel', $order->getStatusLabel());
            $this->setField('orderItems', $this->_filterOrderItems($order));

            // When emailing from the admin, we need to ensure that we're using templates from the frontend
            Mage::getDesign()->setArea('frontend');

            $totals = $this->_getTotalsBlock(Mage::getSingleton('core/layout'), $order, 'sales/order_totals', 'order_totals');
            $this->setField('orderTotals', $totals->toHtml());

            $this->_filteredObjects[] = 'order';
        }

        return $this;
    }

    protected function _filterAddress(Mage_Sales_Model_Order_Address $address)
    {
        if (!in_array('address', $this->_filteredObjects)) {

            $this->setField('billingName', $address->getName());
            $this->_filteredObjects[] = 'address';
        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order_Invoice $invoice
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    protected function _filterInvoice(Mage_Sales_Model_Order_Invoice $invoice)
    {
        if (!in_array('invoice', $this->_filteredObjects)) {
            $index = 1;
            foreach ($invoice->getAllItems() as $item/* @var $item Mage_Sales_Model_Order_Invoice_Item */) {
                $_item = $item->getOrderItem();
                if (!$_item->getParentItem()) {
                    $this->_filterOrderItem($_item, $index);
                    $index++;
                }
            }

            $this->setField('invoiceIncrementId', $invoice->getIncrementId());
            $this->setField('invoiceItems', $this->_filterInvoiceItems($invoice));

            $this->_filteredObjects[] = 'invoice';
        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    protected function _filterShipment(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $this->setStoreId($shipment->getOrder()->getStoreId());
        if (!in_array('shipment', $this->_filteredObjects)) {
            $index = 1;
            foreach ($shipment->getAllItems() as $item/* @var $item Mage_Sales_Model_Order_Shipment_Item */) {
                $_item = $item->getOrderItem();
                if (!$_item->getParentItem()) {
                    $this->_filterOrderItem($_item, $index);
                    $index++;
                }
            }

            $createdAt = $shipment->getCreatedAtStoreDate();
            if (empty($createdAt)) {
                // unset the blank string to force current timestamp
                $createdAt = null;
            }

            $this->setField('shipmentIncrementId', $shipment->getIncrementId());
            $this->setField('shipmentCreatedAt', Mage::helper('core')->formatDate($createdAt, 'long', true)); // TODO: needed?
            $this->setField('shipmentItems', $this->_filterShipmentItems($shipment));
            $this->setField('shipmentTracking', $this->_getShipmentTrackingNumber($shipment, $shipment->getOrder()));

            $this->_filteredObjects[] = 'shipment';
        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    protected function _filterCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        if (!in_array('creditmemo', $this->_filteredObjects)) {
            $index = 1;
            foreach ($creditmemo->getAllItems() as $item/* @var $item Mage_Sales_Model_Order_Creditmemo_Item */) {
                $_item = $item->getOrderItem();
                if (!$_item->getParentItem()) {
                    $this->_filterOrderItem($_item, $index);
                    $index++;
                }
            }

            $createdAt = $creditmemo->getCreatedAtStoreDate();
            if (empty($createdAt)) {
                // unset the blank string to force current timestamp
                $createdAt = null;
            }

            $this->setField('creditmemoIncrementId', $creditmemo->getIncrementId());
            $this->setField('creditmemoCreatedAt', Mage::helper('core')->formatDate($createdAt, 'long', true)); // TODO: needed?
            $this->setField('creditmemoItems', $this->_filterCreditmemoItems($creditmemo));

            $this->_filteredObjects[] = 'creditmemo';
        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    protected function _filterQuote(Mage_Sales_Model_Quote $quote)
    {
        if (!in_array('quote', $this->_filteredObjects)) {
            $this->setStoreId($quote->getStoreId());
            $currencyCode = $quote->getQuoteCurrencyCode();

            if (Mage::helper('bronto_common')->displayPriceIncTax($quote->getStoreId())) {
                $totals = $quote->getTotals();
                $this->setField('subtotal', $this->formatPrice($totals['subtotal']->getValue(), $currencyCode));
                $this->setField('grandTotal', $this->formatPrice($totals['grand_total']->getValue(), $currencyCode));
            } else {
                $this->setField('subtotal', $this->formatPrice($quote->getSubtotal(), $currencyCode));
                $this->setField('grandTotal', $this->formatPrice($quote->getGrandTotal(), $currencyCode));
            }

            $index = 1;
            foreach ($quote->getAllItems() as $item/* @var $item Mage_Sales_Model_Quote_Item */) {
                if (!$item->getParentItem()) {
                    $this->_filterQuoteItem($item, $index);
                    $index++;
                }
            }

            $queryParams       = $this->getQueryParams();
            $queryParams['id'] = urlencode(base64_encode(Mage::helper('core')->encrypt($quote->getId())));
            if ($store = $this->getStore()) {
                $this->setField('quoteURL', $store->getUrl('reminder/load/index', $queryParams));
            } else {
                $this->setField('quoteURL', Mage::getUrl('reminder/load/index', $queryParams));
            }

            // Setup quote items as a template
            if (class_exists('Bronto_Reminder_Block_Cart_Items', false)) {
                $layout = Mage::getSingleton('core/layout');

                /* @var $items Mage_Sales_Block_Items_Abstract */
                $items = $layout->createBlock('bronto/bronto_reminder_cart_items', 'items');
                $items->setTemplate('bronto/reminder/items.phtml');
                $items->setQuote($item->getQuote());

                // When emailing from the admin, we need to ensure that we're using templates from the frontend
                Mage::getDesign()->setArea('frontend');
                $this->setField("cartItems", $items->toHtml());
            }

            $this->_filteredObjects[] = 'quote';
        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $item
     * @param int                         $index
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    protected function _filterQuoteItem($item, $index = null)
    {
        if ($item->getParentItem()) {
            return $this;
        }

        if (Mage::helper('bronto_common')->displayPriceIncTax($item->getStore())) {
            $checkout = Mage::helper('checkout');
            $this->setField("productPrice_{$index}", $this->formatPrice($checkout->getPriceInclTax($item)));
            $this->setField("productTotal_{$index}", $this->formatPrice($checkout->getSubtotalInclTax($item)));
        } else {
            $this->setField("productPrice_{$index}", $this->formatPrice($item->getConvertedPrice()));
            $this->setField("productTotal_{$index}", $this->formatPrice($item->calcRowTotal()->getRowTotal()));
        }

        $this->setField("productName_{$index}", $item->getName());
        $this->setField("productSku_{$index}", $item->getSku());
        $this->setField("productQty_{$index}", $item->getQty());
        $this->setField("productUrl_{$index}", $this->_getQuoteItemUrl($item));

        /* @var $product Mage_Catalog_Model_Product */
        $product = $item->getProduct();
        if (!$product) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
        }
        $this->_filterProduct($product, $index);

        return $this;
    }

    /**
     * @param Mage_Wishlist_Model_Wishlist $wishlist
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    protected function _filterWishlist(Mage_Wishlist_Model_Wishlist $wishlist)
    {
        if (!in_array('wishlist', $this->_filteredObjects)) {
            $index = 1;
            foreach ($wishlist->getItemCollection() as $item/* @var $item Mage_Wishlist_Model_Item */) {
                if (!$item->getParentItem()) {
                    $this->_filterWishlistItem($item, $index);
                    $index++;
                }
            }

            $queryParams                = $this->getQueryParams();
            $queryParams['wishlist_id'] = urlencode(base64_encode(Mage::helper('core')->encrypt($wishlist->getId())));
            if ($store = $this->getStore()) {
                $this->setField('wishlistURL', $store->getUrl('reminder/load/index', $queryParams));
            } else {
                $this->setField('wishlistURL', Mage::getUrl('reminder/load/index', $queryParams));
            }

            // Setup wishlist items as a template
            if (class_exists('Bronto_Reminder_Block_Wishlist_Items', false)) {
                $layout = Mage::getSingleton('core/layout');

                /* @var $items Mage_Sales_Block_Items_Abstract */
                $items = $layout->createBlock('bronto/bronto_reminder_wishlist_items', 'items');
                $items->setTemplate('bronto/reminder/items.phtml');
                $items->setWishlist($item->getWishlist());

                // When emailing from the admin, we need to ensure that we're using templates from the frontend
                Mage::getDesign()->setArea('frontend');
                $this->setField("wishlistItems", $items->toHtml());
            }

            $this->_filteredObjects[] = 'wishlist';
        }

        return $this;
    }

    /**
     * @param Mage_Wishlist_Model_Item $item
     * @param int                      $index
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    protected function _filterWishlistItem(Mage_Wishlist_Model_Item $item, $index = null)
    {
        if ($item->getParentItem()) {
            return $this;
        }

        $this->setStoreId($item->getStoreId());
        $this->setField("productName_{$index}", $item->getName());
        $this->setField("productPrice_{$index}", $this->formatPrice($item->getPrice()));
        $this->setField("productQty_{$index}", $item->getQty());
        $this->setField("productUrl_{$index}", $this->_getWishlistItemUrl($item));

        /* @var $product Mage_Catalog_Model_Product */
        $product = $item->getProduct();
        if (!$product) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
        }
        $this->setField("productSku_{$index}", $product->getSku());

        $this->_filterProduct($product, $index);

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return String                 containing HTML for order items
     */
    protected function _filterOrderItems(Mage_Sales_Model_Order $order)
    {
        $layout = Mage::getSingleton('core/layout');

        /* @var $items Mage_Sales_Block_Items_Abstract */
        $items = $layout->createBlock('sales/order_email_items', 'items');
        $items->setTemplate('email/order/items.phtml');
        $items->setOrder($order);

        // Setup templates to use for products
        $items->addItemRender('default', 'sales/order_email_items_order_default', 'email/order/items/order/default.phtml');
        $items->addItemRender('grouped', 'sales/order_email_items_order_grouped', 'email/order/items/order/default.phtml');
        $items->addItemRender('bundle', 'bundle/sales_order_items_renderer', 'bundle/email/order/items/order/default.phtml');

        // When emailing from the admin, we need to ensure that we're using templates from the frontend
        Mage::getDesign()->setArea('frontend');

        $totals = $this->_getTotalsBlock($layout, $order, 'sales/order_totals', 'order_totals');
        $items->append($totals, 'order_totals');

        return $items->toHtml();
    }

    /**
     * @param Mage_Sales_Model_Order_Item $item
     * @param int                         $index
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    protected function _filterOrderItem($item, $index = null)
    {
        if ($item->getParentItem()) {
            return $this;
        }

        // Product Price Excluding Tax
        if (Mage::helper('tax')->displaySalesPriceExclTax($this->getStore()) || Mage::helper('tax')->displaySalesBothPrices($this->getStore())) {
            if (Mage::helper('weee')->typeOfDisplay($item, array(0, 1, 4), 'email', $this->getStore())) {
                $this->setField("productPriceExclTax_{$index}", $this->formatPrice($item->getRowTotal() + $item->getWeeeTaxAppliedRowAmount() + $item->getWeeeTaxRowDisposition()));
            } else {
                $this->setField("productPriceExclTax_{$index}", $this->formatPrice($item->getRowTotal()));
            }
        }

        // Product Price Including Tax
        if (Mage::helper('tax')->displaySalesPriceInclTax($this->getStore()) || Mage::helper('tax')->displaySalesBothPrices($this->getStore())) {
            $_incl = Mage::helper('checkout')->getSubtotalInclTax($item);
            if (Mage::helper('weee')->typeOfDisplay($item, array(0, 1, 4), 'email', $this->getStore())) {
                $this->setField("productPriceInclTax_{$index}", $this->formatPrice($_incl + $item->getWeeeTaxAppliedRowAmount()));
            } else {
                $this->setField("productPriceInclTax_{$index}", $this->formatPrice($_incl - $item->getWeeeTaxRowDisposition()));
            }
        }

        // Set Product Detail Fields
        $this->setField("productName_{$index}", $item->getName());
        $this->setField("productSku_{$index}", $item->getSku());
        $this->setField("productPrice_{$index}", $this->formatPrice($item->getConvertedPrice()));
        $this->setField("productTotal_{$index}", $this->formatPrice($item->getRowTotal()));
        $this->setField("productQty_{$index}", $item->getQtyOrdered() * 1);
        $this->setField("productUrl_{$index}", $this->_getOrderItemUrl($item));
        $this->setField("productDescription_{$index}", $item->getDescription());

        // Handle Gift Message Details
        if ($item->getGiftMessageId() && $_giftMessage = Mage::helper('giftmessage/message')->getGiftMessage($item->getGiftMessageId())) {
            $this->setField("giftMessage_{$index}", $_giftMessage->getMessage());
            $this->setField("giftMessageFrom_{$index}", $_giftMessage->getSender());
            $this->setField("giftMessageTo_{$index}", $_giftMessage->getRecipient());
        }

        /* @var $product Mage_Catalog_Model_Product */
        $product = $item->getProduct();
        if (!$product) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
        }
        $this->_filterProduct($product, $index);

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order_Invoice $invoice
     *
     * @return String                         containing HTML for invoice items
     */
    protected function _filterInvoiceItems(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order  = $invoice->getOrder();
        $layout = Mage::getSingleton('core/layout');

        /* @var $items Mage_Sales_Block_Items_Abstract */
        $items = $layout->createBlock('sales/order_email_invoice_items', 'items');
        $items->setTemplate('email/order/invoice/items.phtml');
        $items->setOrder($order);
        $items->setInvoice($invoice);

        // Setup templates to use for products
        $items->addItemRender('default', 'sales/order_email_items_order_default', 'email/order/items/invoice/default.phtml');
        $items->addItemRender('grouped', 'sales/order_email_items_order_grouped', 'email/order/items/invoice/default.phtml');
        $items->addItemRender('bundle', 'bundle/sales_order_items_renderer', 'bundle/email/order/items/invoice/default.phtml');

        // When emailing from the admin, we need to ensure that we're using templates from the frontend
        Mage::getDesign()->setArea('frontend');

        $totals = $this->_getTotalsBlock($layout, $order, 'sales/order_invoice_totals', 'invoice_totals');
        $items->append($totals, 'invoice_totals');

        return $items->toHtml();
    }

    /**
     * @param Mage_Sales_Model_Order_Item $item
     * @param int                         $index
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    protected function _filterInvoiceItem($item, $index = null)
    {
        if ($item->getParentItem()) {
            return $this;
        }

        // Product Price Excluding Tax
        if (Mage::helper('tax')->displaySalesPriceExclTax($this->getStore()) || Mage::helper('tax')->displaySalesBothPrices($this->getStore())) {
            if (Mage::helper('weee')->typeOfDisplay($item, array(0, 1, 4), 'email', $this->getStore())) {
                $this->setField("productPriceExclTax_{$index}", $this->formatPrice($item->getRowTotal() + $item->getWeeeTaxAppliedRowAmount() + $item->getWeeeTaxRowDisposition()));
            } else {
                $this->setField("productPriceExclTax_{$index}", $this->formatPrice($item->getRowTotal()));
            }
        }

        // Product Price Including Tax
        if (Mage::helper('tax')->displaySalesPriceInclTax($this->getStore()) || Mage::helper('tax')->displaySalesBothPrices($this->getStore())) {
            $_incl = Mage::helper('checkout')->getSubtotalInclTax($item);
            if (Mage::helper('weee')->typeOfDisplay($item, array(0, 1, 4), 'email', $this->getStore())) {
                $this->setField("productPriceInclTax_{$index}", $this->formatPrice($_incl + $item->getWeeeTaxAppliedRowAmount()));
            } else {
                $this->setField("productPriceInclTax_{$index}", $this->formatPrice($_incl - $item->getWeeeTaxRowDisposition()));
            }
        }

        // Set Product Detail Fields
        $this->setField("productName_{$index}", $item->getName());
        $this->setField("productSku_{$index}", $item->getSku());
        $this->setField("productPrice_{$index}", $this->formatPrice($item->getConvertedPrice()));
        $this->setField("productTotal_{$index}", $this->formatPrice($item->getRowTotal()));
        $this->setField("productQty_{$index}", $item->getQtyOrdered() * 1);
        $this->setField("productUrl_{$index}", $this->_getOrderItemUrl($item));
        $this->setField("productDescription_{$index}", $item->getDescription());

        // Handle Gift Message Details
        if ($item->getGiftMessageId() && $_giftMessage = Mage::helper('giftmessage/message')->getGiftMessage($item->getGiftMessageId())) {
            $this->setField("giftMessage_{$index}", $_giftMessage->getMessage());
            $this->setField("giftMessageFrom_{$index}", $_giftMessage->getSender());
            $this->setField("giftMessageTo_{$index}", $_giftMessage->getRecipient());
        }

        /* @var $product Mage_Catalog_Model_Product */
        $product = $item->getProduct();
        if (!$product) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
        }
        $this->_filterProduct($product, $index);

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return String                          containing HTML for shipment items and tracking numbers
     */
    protected function _filterShipmentItems(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $order  = $shipment->getOrder();
        $layout = Mage::getSingleton('core/layout');

        /* @var $items Mage_Sales_Block_Items_Abstract */
        $items = $layout->createBlock('sales/order_email_shipment_items', 'items');
        $items->setTemplate('email/order/shipment/items.phtml');
        $items->setOrder($order);
        $items->setShipment($shipment);

        // Setup templates to use for products
        $items->addItemRender('default', 'sales/order_email_items_order_default', 'email/order/items/shipment/default.phtml');
        $items->addItemRender('grouped', 'sales/order_email_items_order_grouped', 'email/order/items/shipment/default.phtml');
        $items->addItemRender('bundle', 'bundle/sales_order_items_renderer', 'bundle/email/order/items/shipment/default.phtml');

        // When emailing from the admin, we need to ensure that we're using templates from the frontend
        Mage::getDesign()->setArea('frontend');

        return $items->toHtml();
    }

    /**
     * Get the shipment tracking info.
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param Mage_Sales_Model_Order          $order
     */
    protected function _getShipmentTrackingNumber(Mage_Sales_Model_Order_Shipment $shipment, Mage_Sales_Model_Order $order)
    {
        $layout = Mage::getSingleton('core/layout');
        $block  = $layout->createBlock('core/template')->setTemplate('email/order/shipment/track.phtml');
        $block->setOrder($order);
        $block->setShipment($shipment);
        $block->setArea('frontend');

        return $block->toHtml();
    }

    /**
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     *
     * @return String                            containing HTML for credit memo items
     */
    protected function _filterCreditmemoItems(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order  = $creditmemo->getOrder();
        $layout = Mage::getSingleton('core/layout');

        /* @var $items Mage_Sales_Block_Items_Abstract */
        $items = $layout->createBlock('sales/order_email_creditmemo_items', 'items');
        $items->setTemplate('email/order/creditmemo/items.phtml');
        $items->setOrder($order);
        $items->setCreditmemo($creditmemo);

        // Setup templates to use for products
        $items->addItemRender('default', 'sales/order_email_items_order_default', 'email/order/items/creditmemo/default.phtml');
        $items->addItemRender('grouped', 'sales/order_email_items_order_grouped', 'email/order/items/creditmemo/default.phtml');
        $items->addItemRender('bundle', 'bundle/sales_order_items_renderer', 'bundle/email/order/items/creditmemo/default.phtml');

        // When emailing from the admin, we need to ensure that we're using templates from the frontend
        Mage::getDesign()->setArea('frontend');

        $totals = $this->_getTotalsBlock($layout, $order, 'sales/order_creditmemo_totals', 'creditmemo_totals');
        $items->append($totals, 'creditmemo_totals');

        return $items->toHtml();
    }

    /**
     * Get the totals block for order-style emails.
     *
     * @param Mage_Core_Model_Layout $layout
     * @param Mage_Sales_Model_Order $order
     * @param String                 $totals_block_type
     * @param String                 $totals_block_name
     *
     * @return Mage_Core_Block_Template
     */
    protected function _getTotalsBlock($layout, $order, $totals_block_type, $totals_block_name)
    {
        $totals = $layout->createBlock($totals_block_type, $totals_block_name);
        $totals->setOrder($order);
        $totals->setTemplate('sales/order/totals.phtml');
        $totals->setLabelProperties('colspan="3" align="right" style="padding:3px 9px"');
        $totals->setValueProperties('align="right" style="padding:3px 9px"');

        $tax = $layout->createBlock('tax/sales_order_tax', 'tax');
        $tax->setOrder($order);
        $tax->setTemplate('tax/order/tax.phtml');
        $tax->setIsPlaneMode(1);
        $totals->append($tax, 'tax');

        return $totals;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $index
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    protected function _filterProduct(Mage_Catalog_Model_Product $product, $index = null)
    {
        // Load full product
        $product = Mage::getModel('catalog/product')
            ->setStoreId($product->getStoreId())
            ->load($product->getId());

        if ($index !== null) {
            try {
                $imageUrl = Mage::helper('bronto_common')->getProductImageUrl($product);
                $this->setField("productImgUrl_{$index}", $imageUrl);
            } catch (Exception $e) {
                Mage::log('Error loading image: ' . $e);
            }
        } else {
            $this->setField('productUrl', $product->getUrl());
            $this->setField('productName', $product->getName());
        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $item
     *
     * @return string
     */
    protected function _getQuoteItemUrl(Mage_Sales_Model_Quote_Item $item)
    {
        if ($item->getRedirectUrl()) {
            return $item->getRedirectUrl();
        }

        return $item->getProduct()->getProductUrl();
    }

    /**
     * @param Mage_Wishlist_Model_Item $item
     *
     * @return string
     */
    protected function _getWishlistItemUrl(Mage_Wishlist_Model_Item $item)
    {
        if ($item->getRedirectUrl()) {
            return $item->getRedirectUrl();
        }

        return $item->getProduct()->getProductUrl();
    }

    /**
     * @param Mage_Sales_Model_Order_Item $item
     *
     * @return string
     */
    protected function _getOrderItemUrl(Mage_Sales_Model_Order_Item $item)
    {
        if ($item->getRedirectUrl()) {
            return $item->getRedirectUrl();
        }

        if ($item->getProduct()) {
            return $item->getProduct()->getProductUrl();
        }

        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->getStoreId())
            ->load($item->getProductId());

        if ($product->getId()) {
            return $product->getProductUrl();
        }

        return '';
    }

    /**
     * @param string       $key
     * @param string|array $value
     * @param string       $type
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    public function setField($key, $value, $type = 'html')
    {
        if (!is_string($key) || empty($key)) {
            return $this;
        }

        if (is_array($value)) {
            // Address objects come in as an array on payment failed emails
            $delim = $type == 'html' ? '<br/>' : "\n\r";
            if (isset($value['address_id'])) {
                $new_value = $value['street'] . $delim;
                $new_value .= $value['city'] . $delim;
                $new_value .= $value['region'] . $delim;
                $new_value .= $value['postcode'] . $delim;
                $new_value .= $value['country_id'];
                $this->_delivery->setField($key, $new_value, $type);
            }
        } else {
            if (isset($this->_prettyMap[$key])) {
                // Overwrite $key if we have a mapped overridden value
                $key = $this->_prettyMap[$key];
            }
            $this->_delivery->setField($key, $value, $type);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->_queryParams;
    }

    /**
     * Setter
     *
     * @param integer $storeId
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;

        return $this;
    }

    /**
     * Getter
     * if $_storeId is null return Design store id
     *
     * @return integer
     */
    public function getStoreId()
    {
        if (null === $this->_storeId) {
            $this->_storeId = Mage::app()->getStore()->getId();
        }

        return $this->_storeId;
    }

    /**
     * @param Mage_Core_Model_Store $store
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    public function setStore(Mage_Core_Model_Store $store)
    {
        $this->_store = $store;
        $this->setStoreId($store->getId());

        return $this;
    }

    /**
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        // Only attempt to load the store if a storeId is present
        if ($this->_storeId) {
            if (is_null($this->_store) || $this->_store->getId() != $this->_storeId) {
                $this->_store = Mage::getModel('core/store')->load($this->_storeId);
            }
        } else if (is_null($this->_store)) {
            $this->_store = Mage::app()->getStore();
        }

        return $this->_store;
    }

    /**
     * @param string $messageId
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    public function setMessageId($messageId)
    {
        $this->_messageId = $messageId;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->_messageId;
    }

    /**
     * @param array $variables
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    public function setVariables(array $variables)
    {
        foreach ($variables as $name => $value) {
            $this->_variables[$name] = $value;
        }

        return $this;
    }

    /**
     * @param array $variables
     *
     * @return Bronto_Common_Model_Email_Template_Filter
     */
    public function setAvailable($variables = array())
    {
        if (!is_array($variables)) {
            $variables = array();
        }
        foreach ($variables as $name => $value) {
            $this->_available[$name] = $value;
        }

        return $this;
    }

    /**
     * Converts field names for setters and getters
     *
     * @param string $name
     *
     * @return string
     */
    protected function _underscore($name)
    {
        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));

        return $result;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function _camelize($name)
    {
        return $this->_lcfirst(uc_words($name, ''));
    }

    /**
     * For PHP < 5.3
     *
     * @param string $string
     *
     * @return string
     */
    protected function _lcfirst($string)
    {
        if (function_exists('lcfirst') !== false) {
            return lcfirst($string);
        } else {
            if (!empty($string)) {
                $string{0} = strtolower($string{0});
            }
        }

        return $string;
    }

    /**
     * Convenience method for formatting currency values
     *
     * @param float  $price
     * @param string $currencyCode (Optional)
     *
     * @return string
     */
    protected function formatPrice($price, $currencyCode = null)
    {
        $options = array(
            'precision' => 2,
            'display'   => Zend_Currency::NO_SYMBOL,
        );

        if (Mage::helper('bronto_common')->useCurrencySymbol($this->getStore()->getId())) {
            unset($options['display']);
        }

        $currencyCode = $currencyCode ? $currencyCode : $this->getStore()->getDefaultCurrencyCode();
        if (is_null($this->_currency) || $this->_currency->getCode() != $currencyCode) {
            $this->_currency = Mage::getModel('directory/currency')->load($currencyCode);
        }

        return $this->_currency->formatTxt($price, $options);
    }
}
