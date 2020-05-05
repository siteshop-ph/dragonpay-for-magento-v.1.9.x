<?php
class NetPublica_Dragonpaymicropayments_Model_Standard extends Mage_Payment_Model_Method_Abstract {
	
   

        protected $_code = 'dragonpaymicropayments';
        
        protected $_formBlockType = 'dragonpaymicropayments/form_dragonpaymicropayments';
        protected $_infoBlockType = 'dragonpaymicropayments/info_dragonpaymicropayments';
	
	    protected $_isInitializeNeeded      = true;
	    protected $_canUseInternal          = true;
	    protected $_canUseForMultishipping  = false;
	
    
    
    
	public function getOrderPlaceRedirectUrl() {
		return Mage::getUrl('dragonpaymicropayments/payment/redirect', array('_secure' => true));
}







public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setFilteringMode($data->getFilteringMode());
        return $this;
    }
 
 
 
 
 
    public function validate()
    {
        parent::validate();
 
        $info = $this->getInfoInstance();
 
        $no = $info->getFilteringMode();             //$no come from dragonpay payment option/group selected by shopper at checkout
        
        
 
 
 
 
 
                 if(empty($no) ){              //$no come from dragonpay payment option/group selected by shopper at checkout
                 
            
                                          // get "dragonpay_filtering_mode" cookie value  
                                         $mode = Mage::getModel('core/cookie')->get('dragonpay_filtering_mode') ;     
         
                                                            if ( $mode == "not-set-yet-or-missing") {    
                                                                                                       
                                                                                                $errorCode = 'invalid_data';
                                                                                                $errorMsg = $this->_getHelper()->__('Dragonpay Sub-Option is required');       
                                                                                                
                                                                                } 
   
                       
           } else {
               
               // CASE:   Shopper selected a dragonpay payment Option (Filtering group) at checkout
               
                             // delete "dragonpay_filtering_mode" cookie    // N.B.:  a cookie can never be updated, so we need to delete it before to create/recreate it
                           Mage::getModel('core/cookie')->delete('dragonpay_filtering_mode') ;   
                 
                           // create "dragonpay_filtering_mode" cookie with the filtering value customer selected from option at checkout
                          Mage::getModel('core/cookie')->set('dragonpay_filtering_mode',$no) ;
                    
        }
 
 
 
 
 
 
 
 
        if($errorMsg){
            Mage::throwException($errorMsg);      // to echo error message definited above
        }
        return $this;
        
        
  }










}
?>
