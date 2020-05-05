<?php
/*
Dragonpay Payment Controller
By: Guy Frankin
www.netpublica.com
*/






class NetPublica_Dragonpaymicropayments_PaymentController extends Mage_Core_Controller_Front_Action {





##################################################################################################################################
	// Checkout Redirect to Dragonpay website
	public function redirectAction() {
		$this->loadLayout();
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template','dragonpaymicropayments',array('template' => 'dragonpaymicropayments/redirect.phtml'));
		$this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
	}
##################################################################################################################################	











##################################################################################################################################

////  SECTION  WEB RETURN RECEPTION


public function returnAction() {



## Web return from Dradonpay website
         ## N.B.: Transaction, order or ticket are not updated in db/table from here (WebReturn) but only from the Dragonpay IPN
         ## N.B.: generate paid order, send email are not done from here (WebReturn) but only from the Dragonpay IPN  

         ## example of Web Return URL to give to Dragonpay support:
           //its' need (1) index.ph within to be able to work with SEO webdirection disabled or enabled and (2) it's need the ending slash
          // http://YOUR-MAGNETO/index.php/dragonpay/payment/return/   

         ## example of complete Web redirection URL structure from Dragonpay website:
         /** 
         http://YOUR-MAGNETO/index.php/dragonpay/payment/return/?txnid=145000085&refno=UDC5FWM7&status=S&message=[000]+BOG+Reference+No%3a+20141105191254+%23UDC5FWM7&digest=4029986540082a71a1ea38cc62239c335f89999d&param1=353.55
  
         */



    ## Purge old values
    $dragonpaymicropayments_webReturn_txnid = "";
    $dragonpaymicropayments_webReturn_refno = "";
    $dragonpaymicropayments_webReturn_status = "";
    $dragonpaymicropayments_webReturn_message = "";
    $dragonpaymicropayments_webReturn_digest = "";
    $dragonpaymicropayments_webReturn_param1 = "";
    $dragonpaymicropayments_webReturn_param2 = "";






 	
## Get data posted by Dragonpay
 $params = $this->getRequest()->getParams(); 

    $dragonpaymicropayments_webReturn_txnid = $this->getRequest()->getParam('txnid'); 
    $dragonpaymicropayments_webReturn_refno = $this->getRequest()->getParam('refno'); 
    $dragonpaymicropayments_webReturn_status = $this->getRequest()->getParam('status');
    $dragonpaymicropayments_webReturn_message = $this->getRequest()->getParam('message');
    $dragonpaymicropayments_webReturn_digest = $this->getRequest()->getParam('digest');
    $dragonpaymicropayments_webReturn_param1 = $this->getRequest()->getParam('param1');
    $dragonpaymicropayments_webReturn_param2 = $this->getRequest()->getParam('param2');






    ## dragonpaymicropayments_api_password
    //sectionName, groupName and fieldName are present in etc/system.xml file of your module.
    //format:  Mage::getStoreConfig('sectionName/groupName/fieldName');
    $dragonpaymicropayments_api_password = Mage::getStoreConfig('payment/dragonpaymicropayments/api_password'); 




     ## To check later the authenticity of the digest  
       ## Purge old values
       $webReturn_digest_str = "";
       $true_webReturn_digest = "";
       ## As per Dragonpay requirement, param1 & param2 are not used to create the below digest 
        ## N.B.: strings bellow to calculate the digest are string in an URL DECODE format.   
       $webReturn_digest_str = $dragonpaymicropayments_webReturn_txnid.':'.$dragonpaymicropayments_webReturn_refno.':'.$dragonpaymicropayments_webReturn_status.':'.$dragonpaymicropayments_webReturn_message.':'.$dragonpaymicropayments_api_password ;
	     
       $true_webReturn_digest = sha1($webReturn_digest_str, $raw_output = false);   ## to create 40 Char sha1



 



    
        





 ## Check the digest of webReturn
 if ($dragonpaymicropayments_webReturn_digest == $true_webReturn_digest) {           # Disable this line to test Webreturn different templateS display





     
      if ($dragonpaymicropayments_webReturn_status == 'S'){

              ## THE PAYMENT WENT TOTALLY SUCCESSFUL     "S"     SUCCESS   STATUS  ##

                     ## Show transaction SUCCESS message to the client.
                                                                   
                          //For test purpose   // to see echo, it's need to disable (1) above "if" function and (2) the below redirection
                            //echo $dragonpaymicropayments_webReturn_status.PHP_EOL;
                            //echo $dragonpaymicropayments_webReturn_digest.PHP_EOL;    
                            //echo $true_webReturn_digest.PHP_EOL;

                          

                            Mage::getSingleton('core/session')->addSuccess('CONGRATULATIONS! THIS IS TO CONFIRM THAT YOUR PAYMENT HAS BEEN COMPLETE');
                            //session_write_close();  // can work with or without


                          
                          // for this redirect work, it's need product in cart & active cession, if not it's redirect to cart empty
                          Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('checkout/onepage/success'));  

              
              ## Removing the variables session, it's not needed anymore.
		                






	}elseif($dragonpaymicropayments_webReturn_status == 'F'){	
    
			
                            ## THE PAYMENT WENT TOTALLY WRONG     "F"     FAILURE   STATUS  ##
                  

                           // Show transaction FAILURE message to the client.
                                   //Mage::getSingleton('core/session')->addError(Mage::helper('module_name')->__('Inform the customer for failed transaction'));
                                   Mage::getSingleton('core/session')->addError('TRANSACTION FAILED');
                                   Mage::getSingleton('core/session')->addSuccess('PLEASE TRY TO PAY AGAIN');
                   
                   
                              //session_write_close();  // can work with or without                             



                                 // Get back the content of the cart
                                       if(Mage::getSingleton('checkout/session')->getLastRealOrderId()){

                                                     if ($lastQuoteId = Mage::getSingleton('checkout/session')->getLastQuoteId()){
                                                                  $quote = Mage::getModel('sales/quote')->load($lastQuoteId);
                                                                  $quote->setIsActive(true)->save();
                                                     }
                                      }


        
        
                             // redirection
                                     //$this->_redirect('checkout/cart');   // can work, but it's go only at cart, and note at checkout
                                     //$this->_redirect('checkout/onepage/');  // can work (but it's specific hard coded only for onepage checkout)
                                     Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::helper('checkout/url')->getCheckoutUrl());  // redirect on the used checkout template (opc other other)
                             
                                    return;  // seem not really needed
                          
                          
      
                    ## Removing the variables session, it's not needed anymore.
		   


                    }elseif($dragonpaymicropayments_webReturn_status == 'P'){
			
			## THE PAYMENT HAVE      "P"   PENDING STATUS (for OTC) ##
			
                              ## Show transaction SUCCESS message to the client (it's do not mean it's paid, but transaction start is OK)


                                    Mage::getSingleton('core/session')->addNotice('YOUR PAYMENT IS PENDING, PLEASE CHECK YOUR EMAIL INBOX TO GET DEPOSIT INSTRUCTIONS.');
                                   //session_write_close();  // can work with or without

                          

                                    // for this redirect work, it's need product in cart & active cession, if not it's redirect to cart empty
                                    Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('checkout/onepage/success'));  

                               
                       ## Removing the variables session, it's not needed anymore.
		    





		}elseif($dragonpaymicropayments_webReturn_status == 'U'){
			


			## THE PAYMENT HAVE      "U"   UNKNOWN  STATUS ##
			

                             // Show transaction FAILURE message to the client.
                                   //Mage::getSingleton('core/session')->addError(Mage::helper('module_name')->__('Inform the customer for failed transaction'));
                                   Mage::getSingleton('core/session')->addError('TRANSACTION HAS UNKNOWN STATUS');
                                   Mage::getSingleton('core/session')->addSuccess('PLEASE TRY TO PAY AGAIN');
                   
                   
                              //session_write_close();  // can work with or without                             



                                 // Get back the content of the cart
                                       if(Mage::getSingleton('checkout/session')->getLastRealOrderId()){

                                                     if ($lastQuoteId = Mage::getSingleton('checkout/session')->getLastQuoteId()){
                                                                  $quote = Mage::getModel('sales/quote')->load($lastQuoteId);
                                                                  $quote->setIsActive(true)->save();
                                                     }
                                      }


        
        
                             // redirection
                                     //$this->_redirect('checkout/cart');   // can work, but it's go only at cart, and note at checkout
                                     //$this->_redirect('checkout/onepage/');  // can work (but it's specific hard coded only for onepage checkout)
                                     Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::helper('checkout/url')->getCheckoutUrl());  // redirect on the used checkout template (opc other other)
                             
                                    return;  // seem not really needed
                                   
                                   
      
                    ## Removing the variables session, it's not needed anymore.
		    








                   }elseif($dragonpaymicropayments_webReturn_status == 'R'){
			
			## THE PAYMENT HAVE      "R"   REFUND   STATUS ##

                               ## Show EMPTY CART to the client.


                                     Mage::getSingleton('core/session')->addNotice('DRAGONPAY MICROPAYMENT HAS REFUND STATUS');
                                     //session_write_close();  // can work with or without

  
                                    Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));			
		 

                        ## Removing the variables session, it's not needed anymore.
		   




                    }elseif($dragonpaymicropayments_webReturn_status == 'K'){
			
			## THE PAYMENT HAVE      "K"   CHARGEBACK  STATUS ##
                               
                               ## Show EMPTY CART to the client.

                                   
                                     Mage::getSingleton('core/session')->addNotice('DRAGONPAY MICROPAYMENT HAS CHARGEBACK STATUS');
                                     //session_write_close();  // can work with or without

                              
                                    Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
                                    
			
                        ## Removing the variables session, it's not needed anymore.
		    





                    }elseif($dragonpaymicropayments_webReturn_status == 'V'){
			

			## THE PAYMENT HAVE      "V"   VOID   STATUS ##	 


                          // Show transaction FAILURE message to the client.
                                   //Mage::getSingleton('core/session')->addError(Mage::helper('module_name')->__('Inform the customer for failed transaction'));
                                   Mage::getSingleton('core/session')->addError('TRANSACTION VOIDED');
                                   Mage::getSingleton('core/session')->addSuccess('PLEASE TRY TO PAY AGAIN');
                   
                   
                              //session_write_close();  // can work with or without                             



                                 // Get back the content of the cart
                                       if(Mage::getSingleton('checkout/session')->getLastRealOrderId()){

                                                     if ($lastQuoteId = Mage::getSingleton('checkout/session')->getLastQuoteId()){
                                                                  $quote = Mage::getModel('sales/quote')->load($lastQuoteId);
                                                                  $quote->setIsActive(true)->save();
                                                     }
                                      }


        
        
                             // redirection
                                     //$this->_redirect('checkout/cart');   // can work, but it's go only at cart, and note at checkout
                                     //$this->_redirect('checkout/onepage/');  // can work (but it's specific hard coded only for onepage checkout)
                                     Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::helper('checkout/url')->getCheckoutUrl());  // redirect on the used checkout template (opc other other)
                             
                                    return;  // seem not really needed
                          
                          
      
                    ## Removing the variables session, it's not needed anymore.
	



                      }elseif($dragonpaymicropayments_webReturn_status == 'A'){
			
			## THE PAYMENT HAVE      "A"   AUTHORIZED  STATUS ## (it's do not mean it's paid, but transaction start is OK)


                              ## Show transaction SUCCESS message to the client.

                                     Mage::getSingleton('core/session')->addSuccess('YOUR PAYMENT HAS AUTHORIZED STATUS');
                                   //session_write_close();  // can work with or without



                                    // for this redirect work, it's need product in cart & active cession, if not it's redirect to cart empty
                                    Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('checkout/onepage/success'));  
                                  
	        
                        ## Removing the variables session, it's not needed anymore.




			
		}else{
			
			## UNKNOWN ERROR      NO ERROR CODE OR STATUS GIVEN IN BACK     - DON'T PROCEED ##				
			

			     ## Show transaction FAILURE message to the client.

                                   // Set error message value, if not, redirect to checkout/onepage/failure 
                                   // will end to checkout/cart & empty cart message  
                                   Mage::getSingleton('checkout/session')->setErrorMessage("Dragonpay Micropayment has Unknown Error & No Status");


                                    Mage::getSingleton('core/session')->addError('DRAGONPAY MICROPAYMENT HAS UNKNOWN ERROR & NO STATUS');
                                    //session_write_close();  // can work with or without


                                   Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('checkout/onepage/failure'));
                            


                            ## Removing the variables session, it's not needed anymore.


			
			
		} # END:   if transaction is "S" (Success)



    } # END:   if ($dragonpaymicropayments_webReturn_digest == $true_webReturn_digest)   # Disable this line to test Webreturn different templateS display







} # END:     public function returnAction()
##################################################################################################################################



























##################################################################################################################################

////  SECTION  POSTBACK (IPN)  RECEPTION






## IPN (Instant Payment Notification): Postback data from Dragonpay
	public function ipnAction() {
	

              $dragonpaymicropayments_ipn_txnid = "";
              $dragonpaymicropayments_ipn_refno = "";
              $dragonpaymicropayments_ipn_status = "";
              $dragonpaymicropayments_ipn_message = "";
              $dragonpaymicropayments_ipn_digest = "";
              $dragonpaymicropayments_ipn_param1 = "";
              $dragonpaymicropayments_ipn_param2 = "";





##  Get data posted by Dragonpay IPN      		
$params = $this->getRequest()->getParams(); 
            




              ## Assign posted variables to local variables & Protect futur database imput
	      $dragonpaymicropayments_ipn_txnid = filter_var( $this->getRequest()->getParam('txnid'), FILTER_SANITIZE_STRING); 
              $dragonpaymicropayments_ipn_refno = filter_var( $this->getRequest()->getParam('refno'), FILTER_SANITIZE_STRING);
              $dragonpaymicropayments_ipn_status = filter_var( $this->getRequest()->getParam('status'), FILTER_SANITIZE_STRING); 
              $dragonpaymicropayments_ipn_message = filter_var( $this->getRequest()->getParam('message'), FILTER_SANITIZE_STRING); 
              $dragonpaymicropayments_ipn_digest = filter_var( $this->getRequest()->getParam('digest'), FILTER_SANITIZE_STRING);
              $dragonpaymicropayments_ipn_param1 = filter_var( $this->getRequest()->getParam('param1'), FILTER_SANITIZE_STRING);
              $dragonpaymicropayments_ipn_param2 = filter_var( $this->getRequest()->getParam('param2'), FILTER_SANITIZE_STRING);



              
            $dragonpaymicropayments_api_password = Mage::getStoreConfig('payment/dragonpaymicropayments/api_password'); 




             ## To check later the authenticity of the digest  
               ## Purge old values
               $ipn_digest_str = "";
               $true_ipn_digest = "";
               ## As per Dragonpay requirement, param1 & param2 are not used to create the below digest 
                ## N.B.: strings bellow to calculate the digest are string posted by dragonpay when string included in the webReturn URL are only in an URL format.     
           $ipn_digest_str = $dragonpaymicropayments_ipn_txnid.':'.$dragonpaymicropayments_ipn_refno.':'.$dragonpaymicropayments_ipn_status.':'.$dragonpaymicropayments_ipn_message.':'.$dragonpaymicropayments_api_password ;
	     
           $true_ipn_digest = sha1($ipn_digest_str, $raw_output = false);   ## to create 40 Char sha1



        

           

          $orderIncrementId = $dragonpaymicropayments_ipn_txnid;

         
          $response_str = $dragonpaymicropayments_ipn_txnid.':'.$dragonpaymicropayments_ipn_refno.':'.$dragonpaymicropayments_ipn_status.':'.$dragonpaymicropayments_ipn_message.':'.$dragonpaymicropayments_ipn_digest.':'.$dragonpaymicropayments_ipn_param1.':'.$dragonpaymicropayments_ipn_param2 ;








    ## notification_parser
    //sectionName, groupName and fieldName are present in etc/system.xml file of your module.
    //format:  Mage::getStoreConfig('sectionName/groupName/fieldName');
    $notification_parser = Mage::getStoreConfig('payment/dragonpaymicropayments/notification_parser'); 





// START:   if notification parser is set to ON
if ($notification_parser == 1){









       ## Check the digest of IPN
	 if ($dragonpaymicropayments_ipn_digest == $true_ipn_digest) {

             
               
                   $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

      


#################### START:   If transaction is "S" (SUCCESS) ####################
                    if ($dragonpaymicropayments_ipn_status == 'S'){

                            // Payment was successful, so update the order's state 
				
                                

######### START:  Magento Transaction Creation #########


    
  
    $msg = 'Dragonpay MICROPAYMENT RECEIVED, ';                              
    $payment = $order->getPayment();
    $payment->setTransactionId($dragonpaymicropayments_ipn_refno);
    $transaction = $payment->addTransaction ( Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, null, false, $msg );
    //$transaction->setParentTxnId($dragonpaymicropayments_ipn_refno);
    $transaction->setIsClosed(1);


   /*  //FYI : other possibles Mage_Sales_Model_Order_Payment_Transaction::TYPE_.......
TYPE_PAYMENT = 'payment';            // do not work, type is empty in magento backend
TYPE_ORDER = 'order';               // work
TYPE_AUTH = 'authorization';
TYPE_CAPTURE = 'capture';          //work
TYPE_VOID = 'void';                //work
TYPE_REFUND = 'refund';            //work
Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
*/

 
    
   $transaction->save();

   
  //To send New Order by email
  //work if email on Order is enabled in admin interface with appropriate email template in: System > Configuration > Sales > Sales Email
     $order->sendNewOrderEmail();
 

######### END:  Magento Transaction Creation #########











######### START:  Magento Invoice Creation #########

try {


if(!$order->canInvoice())
{
Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
}

  
$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
  

if (!$invoice->getTotalQty()) {
Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
}
  

$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
//Or you can use
//$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);


$invoice->register();
   // Not used we just rely on native magento server admin choise
       //To say if the customer has to get the invoice by email
      //$invoice->getOrder()->setCustomerNoteNotify(true);
// To change the order state to processing
$invoice->getOrder()->setIsInProcess(true);


//To send Invoice by email
//work if email on Invoice is enabled in admin interface with appropriate email template in: System > Configuration > Sales > Sales Email
$invoice->sendEmail();



$transactionSave = Mage::getModel('core/resource_transaction')  //transactionSave is only for Invoice in Magento VS transaction in Magento
->addObject($invoice)
->addObject($invoice->getOrder())
->save();

 
}  //END of "try"



catch (Mage_Core_Exception $e) {
  
}

######### END:  Magento Invoice Creation #########




Mage::getSingleton('checkout/session')->unsQuoteId();






#################### If transaction is "F" (FAILURE) ####################
                    
                       }elseif($dragonpaymicropayments_ipn_status == 'F'){

        // Nothing to do : just to wait payment re-try from customer	
			
		     

#################### If transaction is "P" (PENDING) ####################

                      }elseif($dragonpaymicropayments_ipn_status == 'P'){ 

        // Pending is for case like waiting for OTC payment
        // Nothing to do : just to wait payment from customer



#################### If transaction is "U" (UNKNOW  STATUS) ####################
                    
                       }elseif($dragonpaymicropayments_ipn_status == 'U'){

        // Nothing to do : just to wait payment re-try from customer




#################### If transaction is "R" (REFUND) ####################
                    
                       }elseif($dragonpaymicropayments_ipn_status == 'R'){



######## START:  Magento Transaction REFUND Creation #########
  
    $msg = 'Dragonpay MICROPAYMENT REFUND, ';                              
    $payment = $order->getPayment();
    $payment->setTransactionId($dragonpaymicropayments_ipn_refno);
    $transaction = $payment->addTransaction ( Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND, null, false, $msg );
    //$transaction->setParentTxnId($dragonpaymicropayments_ipn_refno);
    $transaction->setIsClosed(1);


   /*  //FYI : other possibles Mage_Sales_Model_Order_Payment_Transaction::TYPE_.......
TYPE_PAYMENT = 'payment';            // do not work, type is empty in magento backend
TYPE_ORDER = 'order';               // work
TYPE_AUTH = 'authorization';
TYPE_CAPTURE = 'capture';          //work
TYPE_VOID = 'void';                //work
TYPE_REFUND = 'refund';            //work
Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
*/

 
    
   $transaction->save();

   
######### END:  Magento Transaction REFUND Creation #########


// this line needed?
Mage::getSingleton('checkout/session')->unsQuoteId();


                         



                             // Change Order Status
                             $order->setData('state', "closed");
                             $order->setStatus("closed");  

                             // Add History Comment for the order    
                             $history = $order->addStatusHistoryComment('Dragonpay MICROPAYMENT REFUND.', false);
                             $history->setIsCustomerNotified(true);

                             // save both above order changes
                             $order->save();





#################### If transaction is "K" (CHARGEBACK) ####################
                    
                      }elseif($dragonpaymicropayments_ipn_status == 'k'){


######### START:  Magento Transaction CHARGEBACK Creation #########
  
    $msg = 'Dragonpay MICROPAYMENT CHARGEBACK, ';                              
    $payment = $order->getPayment();
    $payment->setTransactionId($dragonpaymicropayments_ipn_refno);
    $transaction = $payment->addTransaction ( Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER, null, false, $msg );
    //$transaction->setParentTxnId($dragonpaymicropayments_ipn_refno);
    $transaction->setIsClosed(1);


   /*  //FYI : other possibles Mage_Sales_Model_Order_Payment_Transaction::TYPE_.......
TYPE_PAYMENT = 'payment';            // do not work, type is empty in magento backend
TYPE_ORDER = 'order';               // work
TYPE_AUTH = 'authorization';
TYPE_CAPTURE = 'capture';          //work
TYPE_VOID = 'void';                //work
TYPE_REFUND = 'refund';            //work
Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
*/

 
    
   $transaction->save();

   
######### END:  Magento Transaction CHARGEBACK Creation #########


// this line needed?
Mage::getSingleton('checkout/session')->unsQuoteId();




                             // Change Order Status
                             $order->setData('state', "closed");
                             $order->setStatus("closed");  

                             // Add History Comment for the order    
                             $history = $order->addStatusHistoryComment('Dragonpay MICROPAYMENT CHARGEBACK.', false);
                             $history->setIsCustomerNotified(true);

                             // save both above order changes
                             $order->save();





#################### If transaction is "V" (VOID  ERROR) ####################
                    
                       }elseif($dragonpaymicropayments_ipn_status == 'V'){

                                

######### START:  Magento Transaction VOID Creation #########
  
    $msg = 'Dragonpay MICROPAYMENT VOID, ';                              
    $payment = $order->getPayment();
    $payment->setTransactionId($dragonpaymicropayments_ipn_refno);
    $transaction = $payment->addTransaction ( Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID, null, false, $msg );
    //$transaction->setParentTxnId($dragonpaymicropayments_ipn_refno);
    $transaction->setIsClosed(1);


   /*  //FYI : other possibles Mage_Sales_Model_Order_Payment_Transaction::TYPE_.......
TYPE_PAYMENT = 'payment';            // do not work, type is empty in magento backend
TYPE_ORDER = 'order';               // work
TYPE_AUTH = 'authorization';
TYPE_CAPTURE = 'capture';          //work
TYPE_VOID = 'void';                //work
TYPE_REFUND = 'refund';            //work
Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
*/

 
    
   $transaction->save();

   
######### END:  Magento Transaction VOID Creation #########


// this line needed?
Mage::getSingleton('checkout/session')->unsQuoteId();



                             // Change Order Status
                             $order->setData('state', "canceled");
                             $order->setStatus("canceled");  

                                                                       
                             // Add History Comment for the order    
                             $history = $order->addStatusHistoryComment('Dragonpay MICROPAYMENT VOID', false);
                             $history->setIsCustomerNotified(true);

                             // save both above order changes
                             $order->save();                              






#################### If transaction is "A" (AUTHORIZED) ####################
                    
                       }elseif($dragonpaymicropayments_ipn_status == 'A'){

                          // Nothing to do : just to wait payment from customer




#################### If transaction is  NO ERROR CODE OR STATUS GIVEN IN BACK ####################
                      
                        }else{

			
			      ## UNKNOWN ERROR      NO ERROR CODE OR STATUS GIVEN IN BACK     - DON'T PROCEED ##


                        }


#################### END:   If transaction is "S" (SUCCESS) ####################







     }  ## END :   if ($dragonpaymicropayments_ipn_digest == $true_ipn_digest) 


  }  # END  :  if ($notification_parser == 1)


}  # END  :   IPN function : Postback data from Dragonpay

##################################################################################################################################























}  //END:    class NetPublica_Dragonpaymicropayments_PaymentController 
