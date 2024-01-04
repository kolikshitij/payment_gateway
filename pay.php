<?php 
    require_once("db/connection.php");
	if (!isset($_SESSION['email'])) 
	{
		header("location:index.php");
	}
	else{
		$pid = $_SESSION['pid'];
	}
	include("gateway_config.php");
	$html='';

if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') == 0){
	
	$hash=hash('sha512', $key.'|'.$_POST['txnid'].'|'.$_POST['amount'].'|'.$_POST['productinfo'].'|'.$_POST['firstname'].'|'.$_POST['email'].'|'.$_POST['udf1'].'|'.$_POST['udf2'].'|||'.$_POST['udf5'].'||||||'.$salt);
		
	$_SESSION['salt'] = $salt; //save salt in session to use during Hash validation in response
	
	$html = '<form action="'.$action.'" id="payment_form_submit" method="post">
			<input type="hidden" id="udf5" name="udf5" value="'.$_POST['udf5'].'" />
			<input type="hidden" id="udf1" name="udf1" value="'.$_POST['udf1'].'" />
			<input type="hidden" id="udf2" name="udf2" value="'.$_POST['udf2'].'" />
			<input type="hidden" id="surl" name="surl" value="'.$success_url.'" />
			<input type="hidden" id="furl" name="furl" value="'.$failed_url.'" />
			<input type="hidden" id="curl" name="curl" value="'.$cancelled_url.'" />
			<input type="hidden" id="key" name="key" value="'.$key.'" />
			<input type="hidden" id="txnid" name="txnid" value="'.$_POST['txnid'].'" />
			<input type="hidden" id="amount" name="amount" value="'.$_POST['amount'].'" />
			<input type="hidden" id="productinfo" name="productinfo" value="'.$_POST['productinfo'].'" />
			<input type="hidden" id="firstname" name="firstname" value="'.$_POST['firstname'].'" />
			<input type="hidden" id="Lastname" name="Lastname" value="'.$_POST['Lastname'].'" />
			<input type="hidden" id="Zipcode" name="Zipcode" value="'.$_POST['Zipcode'].'" />
			<input type="hidden" id="email" name="email" value="'.$_POST['email'].'" />
			<input type="hidden" id="phone" name="phone" value="'.$_POST['phone'].'" />
			<input type="hidden" id="address1" name="address1" value="'.$_POST['address1'].'" />
			<input type="hidden" id="address2" name="address2" value="'.(isset($_POST['address2'])? $_POST['address2'] : '').'" />
			<input type="hidden" id="city" name="city" value="'.$_POST['city'].'" />
			<input type="hidden" id="state" name="state" value="'.$_POST['state'].'" />
			<input type="hidden" id="country" name="country" value="'.$_POST['country'].'" />
			<input type="hidden" id="Pg" name="Pg" value="'.$_POST['Pg'].'" />
			<input type="hidden" id="hash" name="hash" value="'.$hash.'" />
			</form>
			<script type="text/javascript"><!--
				document.getElementById("payment_form_submit").submit();	
			//-->
			</script>';
	
} 
function getCallbackUrl()
{
	$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	$uri = str_replace('/index.php','/',$_SERVER['REQUEST_URI']);
	return $protocol . $_SERVER['HTTP_HOST'] . $uri . 'response.php';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-sm-12 form-container">
                <h1>Payment</h1>
                <hr>
                <?php 
                       $firstname = $_SESSION['fname'];
                       $lastname = $_SESSION['lname'];
                       $email = $_SESSION['email'];
                       $mobile = $_SESSION['mobile'];
                       $address = $_SESSION['address'];
                       $note = $_SESSION['note'];
					   $sql ="SELECT * FROM products WHERE pid=:pid";
					   $query = $db -> prepare($sql);
					   $query -> bindParam(':pid', $pid, PDO::PARAM_INT);
					   $query -> execute();
					   $row = $query -> fetch();
					   $price = $row['price'];
					   $title = $row['title'];
                ?>
                <div class="row">
                    <div class="col-8">
						<h4>(Payer Details)</h4>
                        <div class="mb-3">
                            <label class="label">First Name :-</label>
							<?php echo $firstname; ?>
						</div>
                        <div class="mb-3">
                            <label class="label">Last Name :-</label>
							<?php echo $lastname; ?>
						</div>
                        <div class="mb-3">
                            <label class="label">Email :-</label>
							<?php echo $email; ?>
                        </div>
                        <div class="mb-3">
                            <label class="label">Mobile :-</label>
							<?php echo $mobile; ?>
                        </div>
                        <div class="mb-3">
                            <label class="label">Address :-</label>
							<?php echo $address; ?>
                        </div>
                        <div class="mb-3">
                            <label class="label">Note :-</label>
							<?php echo $note; ?>
                        </div>
                        
                    </div>
                    <div class="col-sm-4 text-center">
                    <?php 
                        $sql = "SELECT * FROM products WHERE pid=:pid";
                        $query = $db -> prepare($sql);
                        $query -> bindParam(':pid', $pid, PDO::PARAM_INT);
                        $query -> execute();
                        $row = $query->fetch();
                        
                            echo '<div class="card" style="width: 18rem;">
                                <img src="uploads/'.$row['images'].'" class="card-img-top" alt"Card image cap">
                                    <div class="card-body">
                                        <h5 class="card-title">'.$row['title'].'</h5>
                                        <p class="card-text">'.$row['price'].' INR</p>
                                        </div>
                                    </div>';
                        
                    ?>
                    <br>
                    <form action="" id="payment_form" method="post">
			
			<!-- Contains information of integration type. Consult to PayU for more details.//-->
			<input type="hidden" id="udf5" name="udf5" value="PayUBiz_PHP7_Kit" />					
			<input type="hidden" id="udf1" name="udf1" value="<?php echo $pid; ?>" />					
			<input type="hidden" id="udf2" name="udf2" value="<?php echo $note; ?>" />					
			<div class="dv">
				<span>
				<!-- Required - Unique transaction id or order id to identify and match 
				payment with local invoicing. Datatype is Varchar with a limit of 25 char. //-->
				<input type="hidden" id="txnid" name="txnid" placeholder="Transaction ID" value="<?php echo  "Txn" . rand(10000,99999999)?>" /></span>
			</div>
		
			<div class="dv">
				<span>
				<!-- Required - Transaction amount of float type. //-->
				<input type="hidden" id="amount" name="amount" placeholder="Amount" value="<?php echo $price; ?>" /></span>    
			</div>
    
			<div class="dv">
				<span>
				<!-- Required - Purchased product/item description or SKUs for future reference. 
				Datatype is Varchar with 100 char limit. //-->
				<input type="hidden" id="productinfo" name="productinfo" placeholder="Product Info" value="<?php echo $title; ?>" /></span>
			</div>
    
			<div class="dv">
				<span>
				<!-- Required - Should contain first name of the consumer. Datatype is Varchar with 60 char limit. //-->
				<input type="hidden" id="firstname" name="firstname" placeholder="First Name" value="<?php echo $firstname;?>" /></span>
			</div>
		
			<div class="dv">
				<span>
				<!-- Should contain last name of the consumer. Datatype is Varchar with 50 char limit. //-->
				<input type="hidden" id="Lastname" name="Lastname" placeholder="Last Name" value="<?php echo $lastname; ?>" /></span>
			</div>
    
			<div class="dv">
				<span>
				<!-- Datatype is Varchar with 20 char limit only 0-9. //-->
				<input type="hidden" id="Zipcode" name="Zipcode" placeholder="Zip Code" value="" /></span>
			</div>
    
			<div class="dv">
				<span>
				<!-- Required - An email id in valid email format has to be posted. Datatype is Varchar with 50 char limit. //-->
				<input type="hidden" id="email" name="email" placeholder="Email ID" value="<?php echo $email; ?>" /></span>
			</div>
    
			<div class="dv">
				<span>
				<!-- Required - Datatype is Varchar with 50 char limit and must contain chars 0 to 9 only. 
				This parameter may be used for land line or mobile number as per requirement of the application. //-->
				<input type="hidden" id="phone" name="phone" placeholder="Mobile/Cell Number" value="<?php echo $mobile;?>" /></span>
			</div>
    
			<div class="dv">
				<span>					
				<input type="hidden" id="address1" name="address1" placeholder="Address1" value="<?php echo $address; ?>" /></span>
			</div>
    
			<div class="dv">
				<span>						
				<input type="hidden" id="address2" name="address2" placeholder="Address2" value="" /></span>
			</div>
    
			<div class="dv">
				<span>						
				<input type="hidden" id="city" name="city" placeholder="City" value="" /></span>
			</div>
    
			<div class="dv">
				<span><input type="hidden" id="state" name="state" placeholder="State" value="" /></span>
			</div>
    
			<div class="dv">
				<span><input type="hidden" id="country" name="country" placeholder="Country" value="" /></span>
			</div>
    
			<div class="dv">
				<span>
				<!-- Not mandatory but fixed code can be passed to Payment Gateway to show default payment 
				option tab. e.g. NB, CC, DC, CASH, EMI. Refer PDF for more details. //-->
				<input type="hidden" id="Pg" name="Pg" placeholder="PG" value="" /></span>
			</div>
    
			<div><input type="button" id="btnsubmit" name="btnsubmit" value="Pay" onclick="frmsubmit(); return true;" /></div>
			</form>
			<?php if($html) echo $html; //submit request to PayUBiz  ?>

                    </div>
					<script type="text/javascript">		
						<!--
						function frmsubmit()
						{
							document.getElementById("payment_form").submit();	
							return true;
						}
						//-->
					</script>
                </div>
            </div>
                
        </div>
    </div>
</body>
</html>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>