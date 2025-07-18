<?php

    // Display All Error 
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $servername = getenv('MYSQL_HOST') ?: 'localhost';
    $username   = getenv('MYSQL_USER') ?: 'root';
    $password   = getenv('MYSQL_PASSWORD') ?: '';
    $myDB       = getenv('MYSQL_DATABASE') ?: 'booking_system'; 

    // 1. MySQLi : Object Oriented Connection
    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $myDB);

    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    // echo "Connected successfully";

    // 2. MySQLi : Procedure Connection 
    // // Create connection
    // $conn = mysqli_connect($servername, $username, $password);
    // // Check connection
    // if (!$conn) {
    // die("Connection failed: " . mysqli_connect_error());
    // }
    // echo "Connected successfully";


    // 3. PDO Connection
    // try {
    //     $conn = new PDO("mysql:host=$servername;dbname=$myDB", $username, $password);
    //     // set the PDO error mode to exception
    //     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     echo "Connected successfully";
    //   } catch(PDOException $e) {
    //     echo "Connection failed: " . $e->getMessage();
    //   }

?>