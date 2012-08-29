<?php
class UsuarioModel{
    public $data;
    public $reservas;
    private $estados = array("", "Reservado", "Cancelado");

    public function __construct($username, $password){
        $this->cargar($username, $password);
    }

    public function cargar($username, $password){
        $this->data = array();
        $this->reservas = array();
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

                //Historico
                $query = "SELECT * FROM Movimientos 
                          INNER JOIN Alquileres ON alqID = movAlqID 
                          WHERE alqCuentaImpPropID = {$this-data['usrPropID']}
                          ";
            }
            mysql_close($connection);
        }catch(Exception $e){}
    }

    public function esValido(){
        return ! (empty($this->data) or is_null($this->data));
    }
}
?>
