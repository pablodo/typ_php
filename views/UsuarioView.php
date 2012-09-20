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
      <?php 
        if ($this->usuario->mostrarReservas()){
      ?>
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
      <?php 
        }else{
      ?>
          <label>No se encontraron unidades reservadas</label>
      <?php
        }
        if ($this->usuario->mostrarMovimientos()){
      ?>
          <table>
            <th>Fecha</th>
            <th>Comercializadora</th>
            <th>Propietario</th>
            <?php
                foreach($this->usuario->movimientos as $movimientos){
            ?>
                  <tr>
                    <td><?php echo $movimientos['fecha']?></td>
                    <td><?php echo $movimientos['comercializadora']?></td>
                    <td><?php echo $movimientos['propietario']?></td>
                  </tr>
            <?php
            }
            ?>
          </table>
      <?php
        }else{ 
      ?>
          <label>No se encontraron movimientos historicos</label>
      <?php
        }

        if ($this->usuario->mostrarHistorico()){
      ?>
          <table>
            <th>Fecha</th>
            <th>Comercializadora</th>
            <th>Propietario</th>
            <?php
                foreach($this->usuario->historico as $historico){
            ?>
                  <tr>
                    <td><?php echo $historico['fecha']?></td>
                    <td><?php echo $historico['comercializadora']?></td>
                    <td><?php echo $historico['propietario']?></td>
                  </tr>
            <?php
            }
            ?>
          </table>
      <?php 
        }else{ 
      ?>
          <label>No se encontraron movimientos historicos</label>
      <?php 
        }
      ?>
    </div>
  </body>
</html>
<?php
    }
}
?>
