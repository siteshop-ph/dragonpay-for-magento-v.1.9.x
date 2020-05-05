<?PHP




        require_once (dirname(__FILE__).'/config.php'); // to get $CMS_path

     

        // Include Magento application                       
        require_once ( $CMS_path.'/app/Mage.php' );
        umask(0);
	 
        // Initialize Magento
        Mage::app("default");
	 
        // You have two options here,
        // "frontend" for frontend session or "adminhtml" for admin session
        Mage::getSingleton("core/session", array("name" => "adminhtml"));

        //$session = Mage::getSingleton("customer/session"); 



    



   //Process with the CSV file content
   $file = fopen(dirname(__FILE__).'/csv_answer_GetMerchantTxns.php', 'r');  

   $data = fgetcsv($file, 5000000, ";"); //Remove if CSV file does not have column headings

      while (($line = fgetcsv($file, 5000000, ";")) !== FALSE) {

      
         
            $dragonpaymicropayments_ws_refNo =  $line[0];
            $dragonpaymicropayments_ws_refDate = $line[1];
            $dragonpaymicropayments_ws_merchantId = $line[2];
            $dragonpaymicropayments_ws_merchantTxnId = $line[3];
            $dragonpaymicropayments_ws_amount = $line[4];
            $dragonpaymicropayments_ws_currency = $line[5];
            $dragonpaymicropayments_ws_description = $line[6];
            $dragonpaymicropayments_ws_email = $line[7];
            $dragonpaymicropayments_ws_status = $line[8];
            $dragonpaymicropayments_ws_procId = $line[9];
            $dragonpaymicropayments_ws_procMsg = $line[10];
            $dragonpaymicropayments_ws_billerId = $line[11];
            $dragonpaymicropayments_ws_settleDate  = $line[12];
        




           // Protect database imput
            $dragonpaymicropayments_ws_refNo =  filter_var( $dragonpaymicropayments_ws_refNo, FILTER_SANITIZE_STRING);
            $dragonpaymicropayments_ws_refDate = filter_var( $dragonpaymicropayments_ws_refDate, FILTER_SANITIZE_STRING);
            $dragonpaymicropayments_ws_merchantId = filter_var( $dragonpaymicropayments_ws_merchantId, FILTER_SANITIZE_STRING);
            $dragonpaymicropayments_ws_merchantTxnId = filter_var( $dragonpaymicropayments_ws_merchantTxnId, FILTER_SANITIZE_STRING);
            $dragonpaymicropayments_ws_amount = filter_var( $dragonpaymicropayments_ws_amount, FILTER_SANITIZE_STRING);
            $dragonpaymicropayments_ws_currency = filter_var( $dragonpaymicropayments_ws_currency, FILTER_SANITIZE_STRING);
            $dragonpaymicropayments_ws_description = filter_var( $dragonpaymicropayments_ws_description, FILTER_SANITIZE_STRING);
            $dragonpaymicropayments_ws_email = filter_var( $dragonpaymicropayments_ws_email, FILTER_SANITIZE_EMAIL);
            $dragonpaymicropayments_ws_status = filter_var( $dragonpaymicropayments_ws_status, FILTER_SANITIZE_STRING);
            $dragonpaymicropayments_ws_procId = filter_var( $dragonpaymicropayments_ws_procId, FILTER_SANITIZE_STRING);
            $dragonpaymicropayments_ws_procMsg = filter_var( $dragonpaymicropayments_ws_procMsg, FILTER_SANITIZE_STRING);
            $dragonpaymicropayments_ws_billerId = filter_var( $dragonpaymicropayments_ws_billerId, FILTER_SANITIZE_STRING);
            $dragonpaymicropayments_ws_settleDate  = filter_var( $dragonpaymicropayments_ws_settleDate, FILTER_SANITIZE_STRING);






           $orderIncrementId = $dragonpaymicropayments_ws_merchantTxnId;


           $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);






             // START:     process for each line


                    ## if transaction is "S" (Success)
                    if ($dragonpaymicropayments_ws_status == 'S'){


        
                                 ## Check that $dragonpaymicropayments_ws_refNo has not been previously processed sucessfully in Magento
                                   ## It's mean check if order (1) exit and is (2) pending status

                                   if($order->getStatus()=='pending'){



                          // FYI: result of a pending order:
                               // echo $order->getState(); // give "new"   : it's the STATE CODE     
                               // echo $order->getStatus();  // give "pending" : it's the STATUS CODE
                               // echo $order->getStatusLabel();  // give "Pending" : it's the STATUS LABEL (but issue is customer could change it), that why it's not appropriate

                                            
                                                
                                                     


########################### START:   Payment was successful & order need update in Magento ##################
				


                                

######### START:  Magento Transaction CAPTURE Creation #########
  
    $msg = 'Dragonpay Micropayment RECEIVED, ';                              
    $payment = $order->getPayment();
    $payment->setTransactionId($dragonpaymicropayments_ws_refNo);
    $transaction = $payment->addTransaction ( Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, null, false, $msg );
    //$transaction->setParentTxnId($dragonpaymicropayments_ws_refNo);
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
 

######### END:  Magento Transaction CAPTURE Creation #########









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







########################### END:   Payment was successful & order need update in Magento ##################






                        }   //END:    if($order->getStatus()=='pending')  for "S" case  , to check if there was order existing in Magento



                         else {


                                // Nothing to do ;


                             }
                                                  


                                  





                   ## if transaction is "V" (VOID)
                   }elseif($dragonpaymicropayments_ws_status == 'V'){


        

                                  ## Check that $dragonpaymicropayments_ws_refNo has not been previously processed sucessfully in Magento
                                   ## It's mean check if order (1) exit and is (2) pending status
                                   ## not previous transaction should exist in Magento before a VOID transaction


                                   if($order->getStatus()=='pending'){



                          // FYI: result of a pending order:
                               // echo $order->getState(); // give "new"   : it's the STATE CODE     
                               // echo $order->getStatus();  // give "pending" : it's the STATUS CODE
                               // echo $order->getStatusLabel();  // give "Pending" : it's the STATUS LABEL (but issue is customer could change it), that why it's not appropriate

                                            
                                                
                                                     


########################### START:   Payment was VOID & order need update in Magento ##################
				


                                

######### START:  Magento Transaction VOID Creation #########
  
    $msg = 'Dragonpay Micropayment VOID, ';                              
    $payment = $order->getPayment();
    $payment->setTransactionId($dragonpaymicropayments_ws_refNo);
    $transaction = $payment->addTransaction ( Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID, null, false, $msg );
    //$transaction->setParentTxnId($dragonpaymicropayments_ws_refNo);
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
                             $history = $order->addStatusHistoryComment('Dragonpay Micropayment VOID', false);
                             $history->setIsCustomerNotified(true);

                             // save both above order changes
                             $order->save();

                              



########################### END:   Payment was VOID & order need update in Magento ##################






                        }   //END:    if($order->getStatus()=='pending') for VOID case  , to check if there was existing order in Magento



                         else {


                                // Nothing to do ;


                             }
                                                  












                   ## if transaction is "K" (CHARGEBACK)
                   }elseif($dragonpaymicropayments_ws_status == 'K'){


        

                                  ## Check if order exist & if not ever got chargeback (closed)
                                   if($order->getStatus()!='closed'){


                                        
                                                    


########################### START:   Payment was CHARGEBACK & order need update in Magento ##################
				


                                

######### START:  Magento Transaction CHARGEBACK Creation #########
  
    $msg = 'Dragonpay Micropayment CHARGEBACK, ';                              
    $payment = $order->getPayment();
    $payment->setTransactionId($dragonpaymicropayments_ws_refNo);
    $transaction = $payment->addTransaction ( Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER, null, false, $msg );
    //$transaction->setParentTxnId($dragonpaymicropayments_ws_refNo);
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
                             $history = $order->addStatusHistoryComment('Dragonpay Micropayment CHARGEBACK', false);
                             $history->setIsCustomerNotified(true);

                             // save both above order changes
                             $order->save();





########################### END:   Payment was VOID & order need update in Magento ##################






                        }   //END:    if($order->getStatus()) for CHARGEBACK case , to check if there was existing order in Magento



                         else {


                                // Nothing to do ; // as there is no order in Magento for this transaction


                             }
                                                  












                  ## if transaction is "R" (REFUND)
                   }elseif($dragonpaymicropayments_ws_status == 'R'){


        

                                  ## Check if order exist & if not ever refunded (closed)
                                   if($order->getStatus()!='closed'){


  

########################### START:   Payment was REFUND & order need update in Magento ##################
				


                                

######### START:  Magento Transaction REFUND Creation #########
  
    $msg = 'Dragonpay Micropayment REFUND, ';                              
    $payment = $order->getPayment();
    $payment->setTransactionId($dragonpaymicropayments_ws_refNo);
    $transaction = $payment->addTransaction ( Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND, null, false, $msg );
    //$transaction->setParentTxnId($dragonpaymicropayments_ws_refNo);
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
                             $history = $order->addStatusHistoryComment('Dragonpay Micropayment REFUND', false);
                             $history->setIsCustomerNotified(true);

                             // save both above order changes
                             $order->save();




########################### END:   Payment was REFUND & order need update in Magento ##################






                        }   //END:    if($order->getStatus()) for REFUND case // to check if there was existing order in Magento



                         else {


                                // Nothing to do ;


                             }
                                                  





                  ## if transaction is "P" (PENDING)
                  }elseif($dragonpaymicropayments_ws_status == 'P'){
      
                                  // nothing to do: wait OTC payment



                  ## if transaction is "F" (FAILURE)
                  }elseif($dragonpaymicropayments_ws_status == 'F'){
      
                                  // nothing to do: wait new payment try  



                 ## if transaction is "U" (UNKNOWN)
                   }elseif($dragonpaymicropayments_ws_status == 'U'){
       
          
                                 // nothing to do: wait new payment try 



                ## if transaction is "A" (AUTHORIZED)
                   }elseif($dragonpaymicropayments_ws_status == 'A'){
       
          
                                 // nothing to do: just wait 

	

		// No transaction status case	
		}else{


                                // Nothing to do


              	} // END: if transaction is "S"
		







// END:       process for each line
     





   } // END:    While

fclose($file);















?>
