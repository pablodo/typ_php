<?php
class UsuarioView{
    private $usuario;

    public function __construct($usuario){
        $this->usuario = $usuario;
    }
    public function render(){
?>
<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <link href="css/default.css" rel="stylesheet" type="text/css" />
  </head>
  <body>
    <div class="main">
      <table>
        <th>Unidad</th>
        <th>Estado</th>
        <th>Desde</th>
        <th>Hasta</th>
        <?php
            foreach($this->usuario->reservas as $reserva){
        ?>
              <tr>
                <td><?php echo $reserva['unidad']?></td>
                <td><?php echo $reserva['estado']?></td>
                <td><?php echo $reserva['fecha_in']?></td>
                <td><?php echo $reserva['fecha_out']?></td>
              </tr>
        <?php
        }
        ?>
      </table>
    </div>
  </body>
</html>
<?php
    }
}
?>
