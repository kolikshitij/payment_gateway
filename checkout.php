<?php 
    require_once("db/connection.php");
    $pid = $_GET['product_id'];
    $sql = "SELECT * FROM products WHERE pid=:pid";
    $query = $db -> prepare($sql);
    $query -> bindParam(':pid', $pid, PDO::PARAM_INT);
    $query -> execute();
    $count = $query -> fetchColumn();
    if ($count==0) 
    {
        header("location:index.php");
        exit(); 
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-sm-12 form-container">
                <h1>Checkout</h1>
                <hr>
                <?php 
                    if (isset($_POST['submit_form'])) 
                    {
                        $_SESSION['fname'] =$_POST['fname'];
                        $_SESSION['lname'] =$_POST['lname'];
                        $_SESSION['email'] =$_POST['email'];
                        $_SESSION['mobile'] =$_POST['mobile'];
                        $_SESSION['address'] =$_POST['address'];
                        $_SESSION['note'] =$_POST['note'];
                        $_SESSION['pid'] =$pid;
                        if ($_POST['email']!='') 
                        {
                            header("location:pay.php");
                        }

                    }
                ?>
                <div class="row">
                    <div class="col-8">
                        <form action="" method="post">
                        <div class="mb-3">
                            <label class="label">First Name</label>
                            <input type="text" class="form-control" name="fname" required>
                        </div>
                        <div class="mb-3">
                            <label class="label">Last Name</label>
                            <input type="text" class="form-control" name="lname" required>
                        </div>
                        <div class="mb-3">
                            <label class="label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="label">Mobile</label>
                            <input type="text" class="form-control" name="mobile" required>
                        </div>
                        <div class="mb-3">
                            <label class="label">Address</label>
                            <textarea name="address" class="form-control" name="address" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="label">Note</label>
                            <textarea name="note" class="form-control" name="note" required></textarea>
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
                    <button type="submit" class="btn btn-primary" name="submit_form">Place Order</button>
                    </form>
                    </div>
                </div>
            </div>
                
        </div>
    </div>
</body>
</html>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>