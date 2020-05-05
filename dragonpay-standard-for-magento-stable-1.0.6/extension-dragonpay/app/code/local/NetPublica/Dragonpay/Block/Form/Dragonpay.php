<?php 
class NetPublica_Dragonpay_Block_Form_Dragonpay extends Mage_Payment_Block_Form
{ 
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('dragonpay/form/dragonpay.phtml');
    }
}