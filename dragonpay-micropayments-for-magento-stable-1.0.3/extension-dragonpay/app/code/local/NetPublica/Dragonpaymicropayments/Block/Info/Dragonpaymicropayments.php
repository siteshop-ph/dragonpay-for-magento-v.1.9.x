<?php
class NetPublica_Dragonpaymicropayments_Block_Info_Dragonpaymicropayments extends Mage_Payment_Block_Info
{
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $info = $this->getInfo();
        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);
   
   
        
        
        return $transport;
    }
}