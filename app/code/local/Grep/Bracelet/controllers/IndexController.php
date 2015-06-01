<?php

class Grep_Bracelet_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function pendantsAction()
    {
        $block = Mage::getBlockSingleton('grep_bracelet/charms');
        $block->setTemplate('bracelet/list.phtml');
        $block->setLayout(Mage::app()->getLayout());
        
        $content = $block->toHtml();
        
        $block = Mage::getBlockSingleton('grep_bracelet/charms');
        $block->setTemplate('bracelet/charms.phtml');
        $block->setLayout(Mage::app()->getLayout());
        
        $title = $block->toHtml();
        
        $response = array(
            'charmlist' => $content,
            'title' => $title,
        );
        
        $json = json_encode($response);
        $this->getResponse()->setBody($json);
        
    }
    
    public function addtocartAction()
    {
        $cart = Mage::getSingleton('checkout/cart');
        $products = Mage::app()->getRequest()->getParam('product_ids');
        
        $product_ids = explode(',', $products);
        
        foreach ($product_ids as $product_id) {
            $params = array(
                'product' => $product_id,
                'related_product' => null,
                'qty' => 1,
            );
            
            $product = Mage::getModel('catalog/product')->load($product_id);
            try {
                $cart->addProduct($product, $params);
            } catch (Exception $e) {}
        }
        
        $cart->save();
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        Mage::getSingleton('checkout/session')->addSuccess($this->__('The selected products were added to your shopping cart.'));
        
        $this->_redirect('checkout/cart');
    }
}
