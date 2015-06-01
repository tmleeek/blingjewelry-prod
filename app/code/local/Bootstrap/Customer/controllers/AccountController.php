<?php
// Controllers are not autoloaded so we will have to do it manually:
require_once 'Mage/Customer/controllers/AccountController.php';
class Bootstrap_Customer_AccountController extends Mage_Customer_AccountController
{


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

    /**
     * Customer logout action
     */
    public function logoutAction()
    {
        //$session = $this->_getSession();
        $lastUrl = Mage::getSingleton('core/session')->getLastUrl();
        $this->_getSession()->logout()
            ->renewSession();

        $this->_redirectUrl($lastUrl);
    }       
        
}
