<?php
// Controllers are not autoloaded so we will have to do it manually:
require_once 'Mage/Customer/controllers/AccountController.php';
class Bootstrap_Customer_AccountController extends Mage_Customer_AccountController
{

    // PRODUCT ALERT by TOM
  public function createPostAction() {
      $alreadyLoggedin = false;
      $session = $this->_getSession();
      if($session->isLoggedIn()){
          $alreadyLoggedin = true;
      }

      parent::createPostAction();

      if($session->isLoggedIn() && !$alreadyLoggedin){
          $product_id = $this->getRequest()->getParam('productalert_product');
          if($product_id != 'hello'){
              $this->stockAction($product_id, '/accessories', true);
          }
      }
  }

  public function stockAction($productId, $backurl, $redirect)
    {
        $session = Mage::getSingleton('catalog/session');
        /* @var $session Mage_Catalog_Model_Session */
        //$backUrl    = $this->getRequest()->getParam(Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED);
        //$productId  = (int) $this->getRequest()->getParam('product_id');
        /*if (!$backUrl || !$productId) {
            $this->_redirect('/');
            return ;
            }
        */
        if (!$product = Mage::getModel('catalog/product')->load($productId)) {
            /* @var $product Mage_Catalog_Model_Product */
            $session->addError($this->__('Not enough parameters.'));
            $this->_redirectUrl($backUrl);
            return ;
        }

        try {
            $model = Mage::getModel('productalert/stock')
                ->setCustomerId(Mage::getSingleton('customer/session')->getId())
                ->setProductId($product->getId())
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
            $model->save();

            $session->addSuccess($this->__('Alert subscription has been saved.'));
        }
        catch (Exception $e) {
            $session->addException($e, $this->__('Unable to update the alert subscription.'));
        }
        if($redirect){
            //$this->_redirectUrl($backUrl);
            //return;
            $this->_redirectReferer();
        }
    }
    // END PRODUCT ALERT
  
  protected function _loginPostRedirect() {
        $session = $this->_getSession();

            if ($session->isLoggedIn()) {
                    $referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
                    if ($referer) {

                        // Rebuild referer URL to handle the case when SID was changed
                        $referer = Mage::getModel('core/url')->getRebuiltUrl(Mage::helper('core')->urlDecode($referer));
                        if ($this->_isUrlInternal($referer)) {
                            $session->setBeforeAuthUrl($referer);
                        }
                    }

                   	if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {
						$session->setBeforeAuthUrl( Mage::getBaseUrl());
					}

                    // --------------- PRODUCT ALERT STUFF by TOM ------------
                    $product_id = $this->getRequest()->getParam('productalert_product');
                    if($product_id != 'hello'){
                        $this->stockAction($product_id, '/accessories', true);
                        return;
                    }
                    // --------------- PRODUCT ALERT END ------------

            } else {
            	if ($session->getBeforeAuthUrl() == Mage::helper('customer')->getLogoutUrl()){
                	$session->setBeforeAuthUrl(Mage::getBaseUrl());
                }else{
                	$session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
                }
            }
        $this->_redirectUrl($session->getBeforeAuthUrl(true));




 /*
        if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {
            // Set default URL to redirect customer to
            $session->setBeforeAuthUrl(Mage::helper('customer')->getAccountUrl());
            // Redirect customer to the last page visited after logging in
            if ($session->isLoggedIn()) {
                if (!Mage::getStoreConfigFlag(Mage_Customer_Helper_Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD)) {
                    $referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
                    if ($referer) {
                        // Rebuild referer URL to handle the case when SID was changed
                        $referer = Mage::getModel('core/url')
                            ->getRebuiltUrl(Mage::helper('core')->urlDecode($referer));
                        if ($this->_isUrlInternal($referer)) {
                            $session->setBeforeAuthUrl($referer);
                        }
                    }
                } else if ($session->getAfterAuthUrl()) {
                    $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
                }
            } else {
                $session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
            }
        } else if ($session->getBeforeAuthUrl() == Mage::helper('customer')->getLogoutUrl()) {
            $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
        } else {
            if (!$session->getAfterAuthUrl()) {
            	// added this line to redirect to login url
                //$session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
                $session->setAfterAuthUrl($session->getBeforeAuthUrl());
            }
            if ($session->isLoggedIn()) {
                $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
            }
        }
 */



  }

}
