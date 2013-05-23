<?php

class Zygnis_CouponStep_Block_Checkout_Onepage_Coupon extends Mage_Checkout_Block_Onepage_Abstract
{
    protected function _construct()
    {
    	//Discount Coupon Information
    	$this->getCheckout()->setStepData('coupon', array(
            'label'     => Mage::helper('checkout')->__('Coupons & Gift Certificate'),
            'is_show'   => $this->isShow()
        ));
		
        parent::_construct();
    }

    public function getCouponCode()
    {
        return $this->getQuote()->getCouponCode();
    }
} 