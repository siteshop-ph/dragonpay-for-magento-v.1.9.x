<?php 
class NetPublica_Dragonpaymicropayments_Block_Form_Dragonpaymicropayments extends Mage_Payment_Block_Form
{ 
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('dragonpaymicropayments/form/dragonpaymicropayments.phtml');
    }
}