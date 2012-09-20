<?php
class UsuarioModel{
    public $data;
    public $reservas;
    public $movimientos;
    public $historico;
    private $estados = array("", "Reservado", "Cancelado", "", "Propietario");

    public function __construct($username, $password){
        $this->cargar($username, $password);
    }

    public function mostrarReservas(){
        return sizeof($this->reservas) > 0;
    }

    public function mostrarMovimientos(){
        return sizeof($this->movimientos) > 0;
    }

    public function mostrarHistorico(){
        return sizeof($this->historico) > 0;
    }

    public function cargar($username, $password){
        $this->data = array();
        $this->reservas = array();
        $this->movimientos = array();
        $this->historico = array();
        if (empty($username) or empty($password)) { 
            return;
        }
        try{
            include("settings.php");
            $connection = mysql_connect($host, $user, $pass);
            mysql_select_db($db, $connection);
            $query = "SELECT * FROM UsuariosWeb 
                     WHERE usrEmail='{$username}'  
                     AND AES_DECRYPT(usrPass,'typ2012')='{$password}'
                     AND usrPropID > 0"; 
            $result = mysql_query($query, $connection);
            if(mysql_num_rows($result)){
                $this->data = mysql_fetch_array($result);
            }

            //Cargo el estado de las unidades y las liquidaciones
            if ($this->esValido()){
                $query = "SELECT * FROM Alquileres, Propietarios, UnidadesFuncionales 
                          WHERE ufID = alqUF AND propUF = ufID 
                          AND alqEstado > 0 AND propID = {$this->data['usrPropID']}
                          ORDER BY ufID";
                $result = mysql_query($query, $connection);
                while($row = mysql_fetch_array($result)){
                    $new_row = array("unidad" => $row['ufNombre'],
                                     "estado" => $this->estados[$row['alqEstado']],
                                     "fecha_in" => substr($row['alqFIN'], 0, 10),
                                     "fecha_out" => substr($row['alqFOUT'], 0, 10),
                                    );
                    array_push($this->reservas, $new_row);
                }

                $this->cargarMovimientos($connection, $this->data['usrPropID'], false);
                $this->cargarMovimientos($connection, $this->data['usrPropID'], true);
            }
            mysql_close($connection);
        }catch(Exception $e){}
    }

    private function cargarMovimientos($connection, $propID, $historico){
        $saldado = $historico ? 1:0;
        $query = "SELECT * FROM Movimientos
			      INNER JOIN Alquileres ON movAlqID = alqID
			      INNER JOIN UnidadesFuncionales ON alqUF = ufID
			      LEFT JOIN Propietarios as p1 ON alqCuentaImpPropID = p1.propID
			      LEFT JOIN Propietarios as p2 ON alqUF = p2.propUF
                  WHERE (p1.propID = {$propID} OR p2.propID = {$propID}) 
                     AND alqEstado = 2 AND movSaldado = {$saldado} AND movAlqID != 0 ";
        if ($historico){
            $query .= "AND movPropietarioSaldado = {$propID} 
                       GROUP BY movID 
                       ORDER BY alqID, movFechaSaldado, movFecha DESC";
        }else{
            $query .= "GROUP BY movID ORDER BY alqID, movFecha DESC";
        }

        $totales = new TotalMovimientos();
        $importe = new Importe();
        $fechaSaldadoAnt = "";
        $alqIDAnt = 0;
        $result = mysql_query($query, $connection);
        while($row = mysql_fetch_array($result)){
            $movPropID = $row['alqCuentaImpPropID'];

            if ($movPropID > 0 && $movPropID != $propID){
                /*Si tiene una cuenta de deposito, y no es la del 
                 * propietario actual, que siga de largo
                 */
                continue;
            }

            $alqID = $row['alqID'];
            $destino = $row['movDestino'];
            $importe->setImporte($row['movImporte']);
            $importe->porcentajeComision = $row['ufPrecio'];
            $fechaSaldado = $row['movFechaSaldado'];
            
            $agregarHistorico = $historico and 
                                strcmp($fechaSaldadoAnt, $fechaSaldado) != 0 and
                                $fechaSaldadoAnt != "";

            if ($agregarHistorico){
                $totales->fechaSaldado = $fechaSaldadoAnt;
                $totales->calcularTotales();
                $totales = new TotalMovimientos();
            }
            $fechaSaldadoAnt = $fechaSaldado;
            if ($alqID != $alqIDAnt){
                $alqIDAnt = $alqID;
                $importe->sinComision = $row['alqImporteSinComision'];
                $importe->diferenciaImputacion = $row['alqDifImputacion'];
                $totales->sinComision += $importe->sinComision;
                $totales->noImputado += $importe->diferenciaImputacion;

                if(! $historico){
                    $new_row = array('alquiler' => $alqID,
                                     'total' => $row['alqTotal'],
                                     'noImputado' => $importe->getDiferenciaImputacion(),
                                     'sinComision' => $importe->getSinComision(),
                                     'comision' => $importe->porcentajeComision,
                                     );
                    array_push($this->movimientos, $new_row);
                }
            }
            $importe->actualizar();

            //Movimientos
            $totales->comisiones += $importe->comision;
            $importeComercializadora = 0;
            $importePropietario = 0;
            if ($destino == 1)
                $importeComercializadora = $importe->importe;
                $totales->comercializadora += $importeComercializadora;
            if ($destino == 2)
                $importePropietario = $importe->importe;
                $totales->propietario += $importePropietario;

            if (! $historico){
                $new_row = array('fecha' => $row['movFecha'],
                                 'comercializadora' => $importeComercializadora,
                                 'propietario' => $importePropietario,
                                 'destino' => $destino,
                                 'cuenta' => $row['propNCuenta'],
                                 'comision' => $importe->getComision(),
                                 'detalle' => $row['movDetalle']
                                );
                array_push($this->movimientos, $new_row);
            }
        }
        if ($historico){
            $totales->fechaSaldado = $fechaSaldadoAnt;
            $totales->calcularTotales();
            array_push($this->historico, $totales->toRow());
        }else{
            $totales->calcularTotales();
            array_push($this->movimientos, $totales->toRow());
        }
    }

    public function esValido(){
        return ! (empty($this->data) or is_null($this->data));
    }
}

class TotalMovimientos{
    public $comercializadora = 0.0;
    public $propietario = 0.0;
    public $comisiones = 0.0;
    public $noImputado = 0.0;
    public $sinComision = 0.0;
    public $aPagar = 0.0;
    public $aCobrar = 0.0;
    public $ganancia = 0.0;
    public $fechaSaldado = "";

    public function getComercializadora(){
        return $this->comercializadora;
    }
    public function getPropietario(){
        return $this->propietario;
    }
    public function getComisiones(){
        return $this->comisiones;
    }
    public function getNoImputado(){
        return $this->noImputado;
    }
    public function getSinComision(){
        return $this->sinComision;
    }
    public function getAPagar(){
        return $this->aPagar;
    }
    public function getACobrar(){
        return $this->aCobrar;
    }
    public function getGanancia(){
        return $this->ganancia;
    }
    public function calcularTotales(){
        $this->ganancia = $this->comisiones + $this->noImputado;
        $this->aCobrar = $this->comisiones - $this->comercializadora;
        if ($this->aCobrar < 0) $this->aCobrar = 0.0;
        $this->aPagar = $this->comercializadora - $this->comisiones - $this->noImputado;
        if ($this->aPagar < 0) $this->aPagar = 0.0;
    }
    public function toRow() {
        $row = array();
        if ($this->fechaSaldado)
            $row[] = $this->fechaSaldado;
        $row[] = $this->getComercializadora();
        $row[] = $this->getNoImputado();
        $row[] = $this->getSinComision();
        $row[] = $this->getComisiones();
        $row[] = $this->getPropietario();
        $row[] = $this->getAPagar();
        $row[] = $this->getACobrar();
        $row[] = $this->getGanancia();
        return $row;
    }
}

class Importe{
    public $importe = 0.0;
    public $sinComision = 0.0;
    public $conDescuentos = 0.0;
    public $diferenciaImputacion = 0.0;
    public $comision = 0.0;
    public $porcentajeComision = 0.0;

    public function actualizar() {
        //Saco la imputacion a los movimientos
        if ($this->diferenciaImputacion > 0){
            if ($this->conDescuentos >= $this->diferenciaImputacion){
                $this->conDescuentos -= $this->diferenciaImputacion;
                $this->diferenciaImputacion = 0.0;
            }else{
                $this->conDescuentos = 0.0;
                $this->diferenciaImputacion -= $this->conDescuentos;
            }
        }
        //Saco el importe sin comision
        if ($this->sinComision > 0){
            if ($this->conDescuentos >= $this->sinComision){
                $this->conDescuentos -= $this->sinComision;
                $this->sinComision = 0.0;
            }else{
                $this->conDescuentos = 0.0;
                $this->sinComision -= $this->conDescuentos;
            }
        }
        $this->comision = $this->conDescuentos / 100 * $this->porcentajeComision;
    }

    public function setImporte($importe){
        $this->importe = $importe;
        $this->conDescuentos = $importe;
    }
    public function getDiferenciaImputacion() {
        return $this->diferenciaImputacion;
    }
    public function getSinComision() {
        return $this->sinComision;
    }
    public function getImporte() {
        return $this->importe;
    }
    public function getImporteConDescuentos() {
        return $this->conDescuentos;
    }
    public function getComision(){
        return $this->comision;
    }
}
?>
