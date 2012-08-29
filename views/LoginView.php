<?php
class LoginView{
    public function render($invalido = false){
?>
<!doctype html>
  <html>
    <head>
      <title>30 y Playa :: Ingreso</title>
      <link href="css/default.css" rel="stylesheet" type="text/css" />
      <meta charset="utf-8">
    </head>
     <body>
       <div class="main">
         <h1>30 y Playa</h1>
         <h2>Iniciar Sesión</h2>
         <form class="login" action="login.php" method="post" id="login">
             <div class="input-line">
                 <label>Usuario</label>
                 <input name="username" type="text" required/>
             </div>
             <div class="input-line">		
                 <label>Contraseña</label>
                 <input name="password" type="password" required/>
             </div>
             <input class="submit" type="submit" value="Ingresar"/>			
         </form>
<?php if ($invalido) { ?>
         <label class="invalid-user">Usuario inválido</label>
<?php } ?> 
         <img class="login-image" src="images/05.jpg"/> 
       </div>
     </body>
  </html>
<?php
    }
}
?>
