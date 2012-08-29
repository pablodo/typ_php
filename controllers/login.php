<?php
include_once("models/LoginModel.php");
include_once("views/LoginView.php");
include_once("controllers/Usuario.php");

$view = new LoginView();
if (isset($_POST['username']) and isset($_POST['password'])){
    $model = new LoginModel();
    $valido = $model->ingresar($_POST['username'], $_POST['password']);
    if ($valido){
        $usuario = new Usuario($model->usuario);
        $usuario->view->render();
    }else{
        $view->render(! $valido);
    }
    exit();
}
$view->render();
?>
