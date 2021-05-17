<?php
class AccesoDatos
{
    private static $objAccesoDatos;
    private $objetoPDO;

    private function __construct()
    {
        try {
            $this->objetoPDO = new PDO(
                'mysql:host=' . getenv('MYSQL_HOST') . ';dbname=' . getenv('MYSQL_DB') . ';port=' . getenv('MYSQL_PORT') . ';charset=utf8',
                getenv('MYSQL_USER'),
                getenv('MYSQL_PASS'),
                array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
            /*$this->objetoPDO = new PDO(
                'mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DB . ';charset=utf8',
                MYSQL_USER,
                MYSQL_PASS,
                array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );*/
            //$this->objetoPDO->exec("SET CHARACTER SET utf8");
        } catch (PDOException $e) {
            print "Error: " . $e->getMessage();
            die();
        }
    }

    public static function GetAccesoDatos()
    {
        if (!isset(self::$objAccesoDatos)) {
            self::$objAccesoDatos = new AccesoDatos();
        }
        return self::$objAccesoDatos;
    }

    public function prepararConsulta($sql)
    {
        return $this->objetoPDO->prepare($sql);
    }

    public function obtenerUltimoId()
    {
        return $this->objetoPDO->lastInsertId();
    }

    public function __clone()
    {
        trigger_error('ERROR: La clonación de este objeto no está permitida', E_USER_ERROR);
    }
}
