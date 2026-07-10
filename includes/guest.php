<?php

session_start();

if(isset($_SESSION['user_id'])){

    switch($_SESSION['role']){

        case "admin":

            header("Location: ../admin/dashboard.php");
            exit();

        case "provider":

            header("Location: ../provider/dashboard.php");
            exit();

        default:

            header("Location: ../client/dashboard.php");
            exit();

    }

}