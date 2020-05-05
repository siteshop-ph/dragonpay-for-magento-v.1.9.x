<?php




///////////////////   YOUR DRAGONPAY MERCHANTID  /////////////////////////////////////

$ws_merchantId = "here-your-merchant-id";


/////////////////   YOUR DRAGONPAY PASSWORD  /////////////////////////////////////////

$ws_password = "my-dragonpay-password";


/////////////////   PATH OF YOUR MAGENTO INSTALL  //////////////////////////////////////
        //absolute path of your Magento install      ****  WITHOUT THE ENDING SLASH  ***** , 
              //example for Magento install at public root level:   "/home/myuser/public_html" 
             //example for Magento install at a sub-directory level:  "/home/myuser/public_html/mydir"

$CMS_path = "/my/path/to/magento"; 


/////////////////  TEST MODE  /////////////////////////////////////////////////////////////////
          // if "1" :  it will be the Dragonpay test URL that will be used with your Dragonpay  TEST  ACCOUNT , 
         // if "0"  or  different than "1" :  it will be Dragonpay live URL that will be used with your Dragonpay  LIVE  ACCOUNT

$test_mode = "1"; 


/////////////////   CRON DEBUG/LOG MODE  /////////////////////////////////////////////////
         //If "1":  there will be writting in the log.txt file in this directory
         // if "0": There will be no writting in the log.txt file in this directory

$debug_mode = "1";



?>
