<?php
class UsuarioView{
    private $usuario;
    private $total_class;

    public function __construct($usuario){
        $this->usuario = $usuario;
        $this->total_class = "td_positive";
        if($this->usuario->totales['saldo'] < 0){
            $this->total_class = "td_negative";
        }
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
            <th>Alquiler Desde</th>
            <th>Alquiler Hasta</th>
            <th>Desayunos</th>
            <th>Total Alquiler</th>
            <th>Detalle de la Operación</th>
            <th>Saldo</th>
            <th>Cobrado cta. Propietario</th>
            <th>Cobrado cta. Comercializadora</th>
            <th>Comisión</th>
            <?php
                if ($this->usuario->mostrarLiquidaciones()){
                  foreach($this->usuario->liquidaciones as $liquidaciones){
            ?>
                    <tr>
                      <td class="td_fecha"><?php echo ""?></td>
                      <td class="td_fecha"><?php echo ""?></td>
                      <td class="td_numero"><?php echo ""?></td>
                      <td class="td_importe"><?php echo ""?></td>
                      <td class="td_detalle"><?php echo ""?></td>
                      <td class="td_importe"><?php echo $liquidaciones['saldo']?></td>
                      <td class="td_importe"><?php echo ""?></td>
                      <td class="td_importe"><?php echo ""?></td>
                      <td class="td_importe"><?php echo ""?></td>
                    </tr>
            <?php
                  }
                }
                foreach($this->usuario->movimientos as $movimientos){
            ?>
                    <tr>
                      <td class="td_fecha"><?php echo $movimientos['fecha_in']?></td>
                      <td class="td_fecha"><?php echo $movimientos['fecha_out']?></td>
                      <td class="td_numero"><?php echo $movimientos['desayunos']?></td>
                      <td class="td_importe td_importante"><?php echo $movimientos['total_alquiler']?></td>
                      <td class="td_detalle"><?php echo $movimientos['detalle']?></td>
                      <td class="td_importe td_importante"><?php echo $movimientos['saldo']?></td>
                      <td class="td_importe td_importante"><?php echo $movimientos['cobrado_propietario']?></td>
                      <td class="td_importe td_importante"><?php echo $movimientos['cobrado_comercializadora']?></td>
                      <td class="td_importe"><?php echo $movimientos['comision']?></td>
                    </tr>
            <?php
                }
            ?>
                <tr class="totales">
                  <td class="td_fecha"><?php echo ""?></td>
                  <td class="td_fecha"><?php echo ""?></td>
                  <td class="td_numero"><?php echo ""?></td>
                  <td class="td_importe"><?php echo ""?></td>
                  <td class="td_detalle"><?php echo "Totales"?></td>
                  <td class="td_importe"><?php echo ""?></td>
                  <td class="td_importe"><?php echo $this->usuario->totales['cobrado_propietario']?></td>
                  <td class="td_importe"><?php echo $this->usuario->totales['cobrado_comercializadora']?></td>
                  <td class="td_importe"><?php echo ""?></td>
                </tr>
                <tr class="totales">
                  <td class="td_fecha"><?php echo ""?></td>
                  <td class="td_fecha"><?php echo ""?></td>
                  <td class="td_numero"><?php echo ""?></td>
                  <td class="td_importe"><?php echo ""?></td>
                  <td class="td_detalle"><?php echo "Saldo anterior"?></td>
                  <td class="td_importe"><?php echo $this->usuario->totales['saldo_anterior']?></td>
                  <td class="td_importe"><?php echo ""?></td>
                  <td class="td_importe"><?php echo ""?></td>
                  <td class="td_importe"><?php echo ""?></td>
                </tr>
                <tr class="totales">
                  <td class="td_fecha"><?php echo ""?></td>
                  <td class="td_fecha"><?php echo ""?></td>
                  <td class="td_numero"><?php echo ""?></td>
                  <td class="td_importe"><?php echo ""?></td>
                  <td class="td_detalle"><?php echo "Comisiones"?></td>
                  <td class="td_importe"><?php echo $this->usuario->totales['comisiones']?></td>
                  <td class="td_importe"><?php echo ""?></td>
                  <td class="td_importe"><?php echo ""?></td>
                  <td class="td_importe"><?php echo ""?></td>
                </tr>
                <tr class="totales">
                  <td class="td_fecha"><?php echo ""?></td>
                  <td class="td_fecha"><?php echo ""?></td>
                  <td class="td_numero"><?php echo ""?></td>
                  <td class="td_importe"><?php echo ""?></td>
                  <td class="td_detalle"><?php echo "Expensas"?></td>
                  <td class="td_importe"><?php echo $this->usuario->totales['expensas']?></td>
                  <td class="td_importe"><?php echo ""?></td>
                  <td class="td_importe"><?php echo ""?></td>
                  <td class="td_importe"><?php echo ""?></td>
                </tr>
                <tr class="totales">
                  <td class="td_fecha"><?php echo ""?></td>
                  <td class="td_fecha"><?php echo ""?></td>
                  <td class="td_numero"><?php echo ""?></td>
                  <td class="td_importe"><?php echo ""?></td>
                  <td class="td_detalle"><?php echo "Total"?></td>
                  <td class="td_importe <?php echo $this->total_class ?>"><?php echo $this->usuario->totales['saldo']?></td>
                  <td class="td_importe"><?php echo ""?></td>
                  <td class="td_importe"><?php echo ""?></td>
                  <td class="td_importe"><?php echo ""?></td>
                </tr>
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
