<?php

class Liquidacion {

    private $propID;
    private $connection;
    public $data;
    public $saldoTotal;

    public function __construct($propID, $connection){
        $this->propID = $propID;
        $this->connection = $connection;
        $this->cargar();
    }

    public function cargar(){
        $this->data = array();
        $this->saldoTotal = 0;
        try{
            include("settings.php");
            mysql_select_db($db, $this->connection);
            $query = "SELECT * FROM Liquidaciones 
                      WHERE liqPropietario={$this->propID}";
            $result = mysql_query($query, $this->connection);
            if(mysql_num_rows($result)){
                while($row = mysql_fetch_array($result)){
                    $pagar = $row['liqAPagar'];
                    $cobrar = $row['liqACobrar'];
                    $importe = $row['liqImporte'];

                    $saldo = 0;

                    //SALDO A FAVOR DEL PROPIETARIO (Negativo)
                    if ($pagar > $cobrar and $pagar - $cobrar != $importe){
                        $saldo = -($pagar - $cobrar - $importe);

                    //SALDO A FAVOR DE LA COMERCIALIZADORA (Positivo)
                    } else if($cobrar > $pagar and $cobrar - $pagar != $importe){
                        $saldo = $cobrar - $pagar - $importe;

                    } else if ($cobrar == 0 and $pagar == 0) {
                        $saldo = - $importe;
                    }
                    
                    $fecha = Funciones::formatFecha($row['liqFecha']);
                    if ($saldo != 0){
                        $new_row = array('fecha' => $fecha,
                                         'saldo' => $saldo);
                        array_push($this->data, $new_row);
                        $this->saldoTotal += $saldo;
                    }
                }
            }
        }catch(Exception $e){}
    }
}
