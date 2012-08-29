<?php
include_once("models/UsuarioModel.php");
class LoginModel{
    public $usuario = null;
    public function ingresar($username, $password){
        $this->usuario = new UsuarioModel($username, $password);
        return $this->usuario->esValido();
    } 
}
?>
