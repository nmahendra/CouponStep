<?php
require_once 'Mage/Checkout/controllers/OnepageController.php';

class Zygnis_CouponStep_Checkout_OnepageController extends Mage_Checkout_OnepageController{
    
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }    
    
    public function saveBillingAction() {
        if($this->_expireAjax()){
            return;
        }
        if($this->getRequest()->isPost())
        {
            $postData = $this->getRequest()->getPost('billing',array());
            $data = $this->_filterPostData($postData);
            $CustomerAddressId = $this->getRequest()->getPost('billing_address_id', false); 
            Mage::log('Customer Address ID :' . $CustomerAddressId);
            if(isset($data['email'])){
                $data['email'] = trim($data['email']);
            }
            
            $result = $this->getOnepage()->saveBilling($data, $customerAddressId); 
            
            if(!isset($result['error'])){
                
                if ($this->getOnepage()->getQuote()->isVirtual()) {
                    $result['goto_section'] = 'payment';
                    $result['update_section'] = array(
                        'name' => 'payment_method',
                        'html' => $this->_getPaymentMethodsHtml()
                    );

                }
                elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                    $this->loadLayout('checkout_onepage_coupon');
                    
                    $result['goto_section'] = 'coupon';
                    $result['update_section'] = array(
                        'name' => 'coupon',
                        'html' => $this->_getCouponHtml()
                    );
                    
                    $result['allow_sections'] = array('shipping');
                    $result['duplicateBillingInfo'] = 'true';
                    
                }  else {
                    $result['goto_section'] = 'shipping';
                }
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
         
    }
    
    public function saveShippingAction() {
        parent::saveShippingAction();
    }
    
    public function saveCouponAction(){
        $this->_expireAjax();
        if($this->getRequest()->isPost()){
            
            if(!$this->getOnepage()->getQuote()->getItemsCount()){
                $this->_redirect('checkout/cart');
                return;
            }
            
            $couponCode = trim((string) $this->getRequest()->getParam('coupon_code'));
            $oldCouponCode = $this->getOnePage()->getQuote()->getCouponCode();
            
            Mage::log('Coupon Code : '.$couponCode);
            
            $isGuest = $this->getOnePage()->getQuote()->getData('customer_is_guest');
            $checkoutMethod = $this->getOnePage()->getQuote()->getData('checkout_method');
            
            if (!empty($couponCode) && ($isGuest>0 || $checkoutMethod=='guest')) {
                
            }  else {
        	$cert = Mage::getModel('ugiftcert/cert')->load($couponCode, 'cert_number');
                if ($cert->getId() && $cert->getStatus() == 'A' && $cert->getBalance() > 0) {
                    $quote = $this->getOnePage()->getQuote();
                    $cert->addToQuote($quote);
                    $quote->collectTotals()->save();
                }
                // Save Coupon Code.         	       	
                $result = $this->getOnepage()->saveCoupon($couponCode, $oldCouponCode, $this->getRequest()->getParam('remove'));
            }
            
            $redirectUrl = $this->getOnePage()->getQuote()->getCheckoutRedirectUrl();
            if (empty($result['error']) && !$redirectUrl) {
            	
                $result['goto_section'] = 'shipping_method';
                $result['update_section'] = array(
                        'name' => 'shipping-method',
                        'html' => $this->_getShippingMethodsHtml()
                );
            }
            else {
                
            }

            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
            $this->getResponse()->setBody(Zend_Json::encode($result));            
        }
        Mage::helper('firephp')->send('Result : ' . $result);
    }
    
    public function cancelCouponAction(){
        
    }
    
    protected function _getCouponHtml() {
        return $this->getLayout()->getBlock('root')->toHtml();
    }    
}