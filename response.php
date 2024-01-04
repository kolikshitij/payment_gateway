<?php
require_once("db/connection.php");
include("gateway_config.php");
$postdata = $_POST;
$msg = '';
$salt = $_SESSION['salt'];
$status = '';

if (isset($postdata['key'])) {
    $key                =   $postdata['key'];
    $txnid                 =     $postdata['txnid'];
    $amount              =     $postdata['amount'];
    $productInfo          =     $postdata['productinfo'];
    $firstname            =     $postdata['firstname'];
    $lastname            =     $postdata['lastname'];
    $phone            =     $postdata['phone'];
    $address1            =     $postdata['address1'];
    $email                =    $postdata['email'];
    $udf5                =   $postdata['udf5'];
    $pid                =   $postdata['udf1'];
    $note                =   $postdata['udf2'];
    $status                =     $postdata['status'];
    $resphash            =     $postdata['hash'];
    //Calculate response hash to verify	
    $keyString               =      $key . '|' . $txnid . '|' . $amount . '|' . $productInfo . '|' . $firstname . '|' . $email . '|' . $postdata['udf1'] . '|' . $postdata['udf2'] . '|||' . $udf5 . '|||||';
    $keyArray               =     explode("|", $keyString);
    $reverseKeyArray     =     array_reverse($keyArray);
    $reverseKeyString    =    implode("|", $reverseKeyArray);
    $CalcHashString     =     strtolower(hash('sha512', $salt . '|' . $status . '|' . $reverseKeyString)); //hash without additionalcharges

    //check for presence of additionalcharges parameter in response.
    $additionalCharges  =     "";

    if (isset($postdata["additionalCharges"])) {
        $additionalCharges = $postdata["additionalCharges"];
        //hash with additionalcharges
        $CalcHashString     =     strtolower(hash('sha512', $additionalCharges . '|' . $salt . '|' . $status . '|' . $reverseKeyString));
    }
    //Comapre status and hash. Hash verification is mandatory.
    if ($status == 'success'  && $resphash == $CalcHashString) {
        $msg = "Transaction Successful, Hash Verified...<br />";
        //Do success order processing here...
        //Additional step - Use verify payment api to double check payment.
        if (verifyPayment($key, $salt, $txnid, $status))
            $msg = "Transaction Successful, Hash Verified...Payment Verified...";
        else
            $msg = "Transaction Successful, Hash Verified...Payment Verification failed...";
    } else {
        //tampered or failed
        $msg = "Payment failed for Hash not verified...";
    }
} else exit(0);

function verifyPayment($key, $salt, $txnid, $status)
{
    $command = "verify_payment"; //mandatory parameter

    $hash_str = $key  . '|' . $command . '|' . $txnid . '|' . $salt;
    $hash = strtolower(hash('sha512', $hash_str)); //generate hash for verify payment request

    $r = array('key' => $key, 'hash' => $hash, 'var1' => $txnid, 'command' => $command);

    $qs = http_build_query($r);
    //for production
    //$wsUrl = "https://info.payu.in/merchant/postservice.php?form=2";

    //for test
    $wsUrl = "https://test.payu.in/merchant/postservice.php?form=2";

    try {
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $wsUrl);
        curl_setopt($c, CURLOPT_POST, 1);
        curl_setopt($c, CURLOPT_POSTFIELDS, $qs);
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_SSLVERSION, 6); //TLS 1.2 mandatory
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
        $o = curl_exec($c);
        if (curl_errno($c)) {
            $sad = curl_error($c);
            throw new Exception($sad);
        }
        curl_close($c);

        $response = json_decode($o, true);

        if (isset($response['status'])) {
            // response is in Json format. Use the transaction_detailspart for status
            $response = $response['transaction_details'];
            $response = $response[$txnid];

            if ($response['status'] == $status) //payment response status and verify status matched
                return true;
            else
                return false;
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-sm-12 form-container">
                <h1>Payment Status</h1>
                <hr>

                <div class="row">
                    <div class="col-8">
                        <?php
                        if ($status == 'success'  && $resphash == $CalcHashString && $txnid != '') {
                            $subject = "Your payment has been successfull...";
                            $currency = 'INR';
                            $date = new DateTime(null, new DateTimeZone("Asia/Kolkata"));
                            $payment_date = $date->format('Y-m-d H:i:s');
                            $sql = "SELECT count(*) FROM payments WHERE txnid=:txnid";
                            $query = $db->prepare($sql);
                            $query->bindParam(':txnid', $txnid, PDO::PARAM_STR);
                            $query->execute();
                            $countts = $query->fetchColumn();
                            if ($countts <= 0) {
                                $sql = "INSERT INTO payments(firstname,lastname,ammount,status,txnid,pid,payer_email,currency,mobile,address,note,payment_date) VALUES(:firstname,:lastname,:amount,:status,:txnid,:pid,:payer_email,:currency,:mobile,:address1,:note,:payment_date)";
                                $query = $db->prepare($sql);
                                $query->bindParam(':firstname', $firstname, PDO::PARAM_STR);
                                $query->bindParam(':lastname', $lastname, PDO::PARAM_STR);
                                $query->bindParam(':amount', $amount, PDO::PARAM_STR);
                                $query->bindParam(':status', $status, PDO::PARAM_STR);
                                $query->bindParam(':txnid', $txnid, PDO::PARAM_STR);
                                $query->bindParam(':pid', $pid, PDO::PARAM_STR);
                                $query->bindParam(':payer_email', $email, PDO::PARAM_STR);
                                $query->bindParam(':currency', $currency, PDO::PARAM_STR);
                                $query->bindParam(':mobile', $mobile, PDO::PARAM_STR);
                                $query->bindParam(':address1', $address, PDO::PARAM_STR);
                                $query->bindParam(':note', $note, PDO::PARAM_STR);
                                $query->bindParam(':payment_date', $payment_date, PDO::PARAM_STR);
                                $query -> execute();
                            }
                            echo '<h2 style="color:#33FF00";>' . $subject . '</h2>   <hr>';
                            echo '<table class="table">';
                            echo '<tr>';
                            $rows = $sql = "SELECT * FROM payments WHERE txnid=:txnid";
                            $query = $db->prepare($sql);
                            $query->bindParam(':txnid', $txnid, PDO::PARAM_STR);
                            $query->execute();
                            $rows = $query->fetchAll();
                            foreach ($rows as $row) 
                            {
                                $dbdate = $row['payment_date'];
                            }
                            echo '<tr>
                                        <th>Transaction ID :</th>
                                        <td>' . $txnid . '</td>
                                    </tr>
                                    <tr>
                                        <th>Paid Amount :</th>
                                        <td>' . $amount . ' ' . $currency . '</td>
                                    </tr>
                                    <tr>
                                        <th>Payment Status :</th>
                                        <td>' . $status . '</td>
                                    </tr>
                                    <tr>
                                        <th>Payer Email :</th>
                                        <td>' . $email . '</td>
                                    </tr>
                                    <tr>
                                        <th>Name :</th>
                                        <td>' . $firstname . ' ' . $lastname . '</td>
                                    </tr>
                                    <tr>
                                        <th>Mobile No :</th>
                                        <td>' . $phone . '</td>
                                    </tr>
                                    <tr>
                                        <th>Address :</th>
                                        <td>' . $address1 . '</td>
                                    </tr>
                                    <tr>
                                        <th>Note :</th>
                                        <td>' . $note . '</td>
                                    </tr>
                                    <tr>
                                        <th>Date :</th>
                                        <td>' . $dbdate . '</td>
                                    </tr>
                                </table>';
                        } else {
                            $html = "<p><div class='errmsg'>Invalid Transaction. Please Try Again</div></p>";
                            $error_found = 1;
                        }
                        if (isset($html)) {
                            echo $html;
                        }
                        ?>


                    </div>
                    <div class="col-sm-4 text-center">
                        <?php
                        if (!isset($error_found)) {
                            $sql = "SELECT * FROM products WHERE pid=:pid";
                            $query = $db->prepare($sql);
                            $query->bindParam(':pid', $pid, PDO::PARAM_INT);
                            $query->execute();
                            $row = $query->fetch();

                            echo '<div class="card" style="width: 18rem;">
                                <img src="uploads/' . $row['images'] . '" class="card-img-top" alt"Card image cap">
                                    <div class="card-body">
                                        <h5 class="card-title">' . $row['title'] . '</h5>
                                        <p class="card-text">' . $row['price'] . ' INR</p>
                                        </div>
                                    </div>';
                        }

                        ?>
                        <br>

                    </div>
                </div>
            </div>

        </div>
    </div>
</body>

</html>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>