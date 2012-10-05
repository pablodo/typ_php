<?php

class Liquidacion {

    private $propID;
    private $connection;
    public $data;

    public function __construct($propID, $connection){
        $this->propID = $propID;
        $this->connection = $connection;
        $this->cargar();
    }

    public function cargar(){
        $this->data = array();
        try{
            mysql_select_db($db, $this->connection);
            $query = "SELECT * FROM Liquidaciones 
                      WHERE liqPropietario={$this->propID}";
            $result = mysql_query($query, $this->connection);
            if(mysql_num_rows($result)){
                while($row = mysql_fetch_array($result)){
                    $importe = $row['liqImporte'];
                    if ($row['liqAPagar'] > $row['liqACobrar']){
                        $importe = - $importe;
                    }
                    $fecha = Funciones::formatFecha($row['liqFecha']);
                    if ($importe != 0){
                        $new_row = array('fecha' => $fecha,
                                         'saldo' => $importe);
                        array_push($this->data, $new_row);
                    }
                }
            }
        }catch(Exception $e){}
    }
}
