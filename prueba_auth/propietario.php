<?php
session_start();
header("Cache-control: private");
if ($_SESSION) {
$user_id = $_SESSION['user'][0];
if ($user_id < 0){
    header("Location: login.html");
    die();
}
print_r($_SESSION['user'][0]);
$_SESSION = array();
}else{
    header("Location: login.html");
}
?>
