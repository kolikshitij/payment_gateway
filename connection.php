<?php 
    session_start();

    define('DBHOST', 'localhost');
    define('DBUSER', 'root');
    define('DBPASS', '');
    define('DBNAME', 'payment_gateway');
    try{
        $db = new PDO("mysql:hosts=".DBHOST.";dbname=".DBNAME,DBUSER,DBPASS);
        $db -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // echo "success";
       }
       catch(PDOException $e)
       {
        echo "ISSUE -> Connection Failed: " . $e -> getMessage();
       }
?>