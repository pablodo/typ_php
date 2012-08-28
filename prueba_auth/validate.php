<?php

$username = $_POST['username'];
$pass = $_POST['password'];

try{
    $server = mysql_connect("localhost", "root", "");
    if (! $server) die(mysql_error());
    mysql_select_db("30yplaya_db");

    $query = "SELECT * FROM UsuariosWeb WHERE AES_DECRYPT(usrPass, 'typ2012') = '$pass' AND usrEmail = '$username'";
    $result = mysql_query($query);
    $user = mysql_fetch_row($result);
    if ($user){
        session_start();
        header("Cache-control: private");
        $_SESSION['user'] = $user;
        header("Location: ./propietario.php");
    }else{
        header("Location: ./login.html");
    }
}catch(Exception $e){
    echo "todo mal";
}
