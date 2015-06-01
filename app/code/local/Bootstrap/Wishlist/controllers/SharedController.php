<?php
// Controllers are not autoloaded so we will have to do it manually:
require_once 'Mage/Wishlist/controllers/SharedController.php';
class Bootstrap_Wishlist_SharedController extends Mage_Wishlist_SharedController
{
    /**
     * Retrieve wishlist instance by requested code
     *
     * @return Mage_Wishlist_Model_Wishlist|false
     */
    protected function _getWishlist()
    {
        $id     = (string)$this->getRequest()->getParam('id');
        $code     = (string)$this->getRequest()->getParam('code');
        if (empty($code)) {
            if (empty($id)) {
                return false;
            }else{
                $wishlist = Mage::getModel('wishlist/wishlist')->load($id);
            }
        }else{
            $wishlist = Mage::getModel('wishlist/wishlist')->loadByCode($code);
        }
        if (!$wishlist->getId()) {
            return false;
        }

        Mage::getSingleton('checkout/session')->setSharedWishlist($code);

        return $wishlist;
    }
}