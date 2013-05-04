<?php
require_once "models/LiquidacionModel.php";
require_once "lib/Funciones.php";

const EXPENSAS = -1;

class UsuarioModel{
    public $movimientos;
    public $liquidaciones;
    public $totales;
    public $nombre;
    private $estados = array("", "Seña", "Saldo Cancelado", "", "Propietario");

    public function __construct($username, $password){
        $this->cargar($username, $password);
    }

    public function mostrarMovimientos(){
        return sizeof($this->movimientos) > 0;
    }
    public function mostrarLiquidaciones(){
        return sizeof($this->liquidaciones) > 0;
    }

    public function cargar($username, $password){
        $this->data = array();
        $this->movimientos = array();
        $this->liquidaciones = array();
        $this->totales = array();
        if (empty($username) or empty($password)) { 
            return;
        }
        try{
            include("settings.php");
            $connection = mysql_connect($host, $user, $pass);
            mysql_select_db($db, $connection);
            $query = "SELECT * FROM UsuariosWeb
                      INNER JOIN Propietarios on usrPropID = propID
                      WHERE usrEmail='{$username}'  
                      AND AES_DECRYPT(usrPass,'typ2012')='{$password}'"; 
            $result = mysql_query($query, $connection);
            if(mysql_num_rows($result)){
                $this->data = mysql_fetch_array($result);
                $this->nombre = $this->data['propNombre'];
            }

            //Cargo el estado de las unidades y las liquidaciones
            if ($this->esValido()){
                $propID = $this->data['usrPropID'];
                $liquidacion = new Liquidacion($propID, $connection);
                $this->liquidaciones = $liquidacion->data;
                $this->cargarMovimientos($connection, $propID);
                $this->totales['saldo'] += $liquidacion->saldoTotal;
                $this->totales['saldo_anterior'] = number_format($liquidacion->saldoTotal, 2);
                $this->totales['saldo'] = number_format($this->totales['saldo'], 2);
                $this->totales['cobrado_propietario'] = number_format($this->totales['cobrado_propietario'], 2);
                $this->totales['cobrado_comercializadora'] = number_format($this->totales['cobrado_comercializadora'], 2);
            }
            mysql_close($connection);
        }catch(Exception $e){}
    }
    private function cargarMovimientos($connection, $propID){
        $query = "SELECT * FROM Movimientos 
                      LEFT JOIN Alquileres ON movAlqID = alqID 
                      LEFT JOIN UnidadesFuncionales ON alqUF = ufID 
                      LEFT JOIN Propietarios ON alqPropietarioImp = propID 
                  WHERE (propID = {$propID} or movPropietario = {$propID}) 
                     AND movLiquidacion = 0 
                  GROUP BY movID";
        $result = mysql_query($query, $connection);

        $alquileres = array();
        $this->totales['saldo'] = 0;
        $this->totales['cobrado_propietario'] = 0;
        $this->totales['cobrado_comercializadora'] = 0;
        $this->totales['comisiones'] = 0;
        $this->totales['expensas'] = 0;
        while($row = mysql_fetch_array($result)){
            $movPropID = $row['alqCuentaImpPropID'];
            $estado = $row['movOperacion'];
            if ($movPropID > 0 && $movPropID != $propID){
                /*Si tiene una cuenta de deposito, y no es la del 
                 * propietario actual, que siga de largo
                 */
                continue;
            }
        
            $importe = $row['movImporte'];
            $fechaIN = "";
            $fechaOUT = "";
            $desayunos = "";
            $totalAlquiler = 0;
            $comision = 0;
            if ($estado != EXPENSAS){
                $alqID = $row['alqID'];
                if (! array_key_exists($alqID, $alquileres)){
                    $alquileres[$alqID] = array(
                        'dif_imputacion' => $row['alqDifImputacion'],
                        'sin_comision'   => $row['alqImporteSinComision']);                  
                }
                $difImputacion = $alquileres[$alqID]['dif_imputacion'];
                $sinComision = $alquileres[$alqID]['sin_comision'];

                if ($difImputacion > 0){
                    if ($importe >= $difImputacion){
                        $importe -= $difImputacion;
                        $difImputacion = 0.0;
                    }else{
                        $importe = 0.0;
                        $difImputacion -= $importe;
                    }
                }
                if ($sinComision > 0){
                    if ($importe >= $sinComision){
                        $importe -= $sinComision;
                        $sinComision = 0.0;
                    }else{
                        $importe = 0.0;
                        $sinComision -= $importe;
                    }
                }
                $alquileres[$alqID]['dif_imputacion'] = $difImputacion;
                $alquileres[$alqID]['sin_comision'] = $sinComision;
                $fechaIN = Funciones::formatFecha($row['alqFIN']);
                $fechaOUT = Funciones::formatFecha($row['alqFOUT']);
                $desayunos = $row['alqDesayunosImp'];
                $totalAlquiler = $row['alqTotal'] - $row['alqDifImputacion'];
                $comision = $importe / 100 * $row['ufPrecio'];
                $this->totales['comisiones'] += $comision;
            }

            if ($importe == 0){
                continue;
            }

            $detalle = $row['movDetalle'];
            if ($estado > 0){
                $detalle = $this->estados[$estado];
            }
            $cobradoPropietario = 0;
            $cobradoComercializadora = 0;
            $expensas = 0;
            if ($estado == EXPENSAS){
                $expensas = $saldo = $importe;
                $this->totales['expensas'] += $expensas;
            }else{
                if ($row['movDestino'] == 1){
                    $cobradoComercializadora = $importe;
                    $saldo = - $importe + $comision;
                } elseif ($row['movDestino'] == 2) {
                    $cobradoPropietario = $importe;
                    $saldo = $comision;
                }
            }
            $this->totales['saldo'] += $saldo;
            $this->totales['cobrado_propietario'] += $cobradoPropietario;
            $this->totales['cobrado_comercializadora'] += $cobradoComercializadora;

            $new_row = array('fecha_in' => $fechaIN, 
                             'fecha_out' => $fechaOUT, 
                             'desayunos' => $desayunos,
                             'total_alquiler' => number_format($totalAlquiler, 2),
                             'detalle' => $detalle,
                             'comision' => number_format($comision, 2),
                             'expensas' => number_format($expensas, 2),
                             'cobrado_propietario' => number_format($cobradoPropietario, 2),
                             'cobrado_comercializadora' => number_format($cobradoComercializadora, 2),
                            );
            array_push($this->movimientos, $new_row);
        }
    }

    public function esValido(){
        return ! (empty($this->data) or is_null($this->data));
    }
}
