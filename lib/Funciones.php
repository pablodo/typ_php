<?php
class Funciones{
    public static function formatFecha($fecha){
        $fecha = new DateTime($fecha);
        return $fecha->format('d-m-y');
    }
}
