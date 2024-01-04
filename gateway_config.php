<?php 
    $key="";
    $salt="";
    $mode='test';
    $success_url="http://localhost/Projects/Infinitech%20projects/Payment_gateway/response.php";
    $failed_url="http://localhost/Projects/Infinitech%20projects/Payment_gateway/failed.php";
    $cancelled_url="http://localhost/Projects/Infinitech%20projects/Payment_gateway/cancelled.php";
    if($mode=='live')
    {
        $action = 'https://secure.payu.in/_payment';
    }
    else
    {
        $action = 'https://test.payu.in/_payment';    
        $key="oZ7oo9";
        $salt="UkojH5TS";
    }
?>