<?php

class Grep_Minicart_IndexController extends Mage_Core_Controller_Front_Action
{
    protected $types = array(
        'core/session',
        'customer/session',
        'catalog/session',
        'checkout/session',
        'tag/session',
    );
    
    public function indexAction()
    {
        $this->setResponse();
    }
    
    public function addAction()
    {
        $cart = Mage::getSingleton('checkout/cart');
        $redirect = '';
        
        $id = Mage::app()->getRequest()->getPost('product');
        $product = Mage::getModel('catalog/product')->load($id);
        
        $params = Mage::app()->getRequest()->getPost();
        $related = $this->getRequest()->getParam('related_product');
        
        $success = false;
        $name = 'The selected product';
        try {
            $name = $product->getName();
            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }
            $cart->save();
            $success = true;
        } catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
            
            if ($product && $product->getId()) {
                $redirect = $product->getUrlModel()->getProductUrl($product);
            }
        }
        
        if ($success) {
            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
            Mage::getSingleton('checkout/session')->addSuccess($name . ' was added to your shopping cart.');
        }
        
        $this->setResponse();
    }
    
    public function getMessagesContent()
    {
        $block = Mage::getBlockSingleton('core/messages');
        $block->setLayout(Mage::app()->getLayout());
        
        $content = false;
        foreach ($this->types as $type) {
            $type = Mage::getSingleton($type);
            
            if ($type) {
                $messages = $type->getMessages(true);
                if (count($messages->getItems())) {
                    $content = true;
                    $block->addMessages($messages);
                }
            }
        }
        
        if ($content) {
            return $block->getGroupedHtml();
        }
        
        return '';
    }
    
    public function getItemCount()
    {
        $count = 0;
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();
        
        if ($quote) {
            $items = $quote->getAllItems();
            
            if ($items) {
                foreach ($items as $item) {
                    $count += $item->getQty();
                }
            }
        }
        
        return $count;
    }
    
    public function setResponse($redirect='')
    {
        $this->loadLayout();
        
        $response = array(
            'minicart' => '',
            'messages' => '',
            'headerbasket' => '',
            'redirect' => $redirect,
        );
        
        $minicart_block = $this->getLayout()->getBlock('minicart_sidebar');
        $response['minicart'] = $minicart_block->toHtml();
        
        $count = $this->getItemCount();
        $response['headerbasket'] = "<strong><u>Cart  </u></strong>  ($count)";
        
        if (!$redirect) {
            $response['messages'] = $this->getMessagesContent();
        }
        
        $json = json_encode($response);
        $this->getResponse()->setBody($json);
    }
}
