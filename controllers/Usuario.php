<?php
include_once("views/UsuarioView.php");
include_once("models/UsuarioModel.php");

class Usuario{
    public $view;
    public $usuario;

    public function __construct($usuario){
        $this->usuario = $usuario;
        $this->view = new UsuarioView($usuario);
    }
}
?>
