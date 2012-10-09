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
    <div class="main movimientos">
    <h2>Bienvenido <?php echo $this->usuario->nombre ?></h2>
      <?php
        if ($this->usuario->mostrarMovimientos() || $this->usuario->mostrarLiquidaciones()){
      ?>
          <table>
            <th>Fecha Operacion</th>
            <th>Alquiler Desde</th>
            <th>Alquiler Hasta</th>
            <th>Desayunos</th>
            <th>Total Alquiler</th>
            <th>Detalle de la Operación</th>
            <th>Saldo</th>
            <th>Cobrado cta. Propietario</th>
            <th>Cobrado cta. Comercializadora</th>
            <?php
                if ($this->usuario->mostrarLiquidaciones()){
                  foreach($this->usuario->liquidaciones as $liquidaciones){
            ?>
                    <tr>
                      <td class="t1"><?php echo $liquidaciones['fecha']?></td>
                      <td class="t2"><?php echo ""?></td>
                      <td class="t3"><?php echo ""?></td>
                      <td class="t4"><?php echo ""?></td>
                      <td class="t5"><?php echo ""?></td>
                      <td class="t6"><?php echo ""?></td>
                      <td class="t7"><?php echo $liquidaciones['saldo']?></td>
                      <td class="t8"><?php echo ""?></td>
                      <td class="t9"><?php echo ""?></td>
                    </tr>
            <?php
                  }
                }
                foreach($this->usuario->movimientos as $movimientos){
            ?>
                    <tr>
                      <td class="t1"><?php echo $movimientos['fecha_operacion']?></td>
                      <td class="t2"><?php echo $movimientos['fecha_in']?></td>
                      <td class="t3"><?php echo $movimientos['fecha_out']?></td>
                      <td class="t4"><?php echo $movimientos['desayunos']?></td>
                      <td class="t5"><?php echo $movimientos['total_alquiler']?></td>
                      <td class="t6"><?php echo $movimientos['detalle']?></td>
                      <td class="t7"><?php echo $movimientos['saldo']?></td>
                      <td class="t8"><?php echo $movimientos['cobrado_propietario']?></td>
                      <td class="t9"><?php echo $movimientos['cobrado_comercializadora']?></td>
                    </tr>
            <?php
                }
            ?>
          </table>
      <?php
        }else{ 
      ?>
          <label>No se encontraron movimientos</label>
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
