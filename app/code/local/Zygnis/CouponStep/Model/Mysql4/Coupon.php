<?php

class Zygnis_CouponStep_Model_Mysql4_Coupon extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct() {
        $this->_init('sales/quote', 'entity_id');
    }
    
    public function checkGuestCondition($id,$email,$coupon)
    {
        $read = $this->_getReadAdapter();
        
    	$select = $read->select()
    			  ->from("sales_flat_quote")
    			  ->where("entity_id != ".$id. " AND customer_email='".$email."' AND coupon_code='".$coupon."'");

    	$result = $read->fetchAll($select);            
        return $result;	
    }
}