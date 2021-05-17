<?php
require_once './utilities/Debugger.php';
class Usuario
{
    public $Id;
    public $SectorId;
    public $SectorNombre;
    public $EstadoUsuarioId;
    public $EstadoUsuario;
    public $Nombre;
    public $Apellido;
    public $Clave;
    public $Mail;
    public $FechaCreacion;
    public $FechaUltimaModificacion;
    public $FechaBaja;
    public $UsuarioModificacion;
    public $UsuarioAlta;
    public $TipoUsuarioId;
    public $TipoUsuario;

    public function __construct()
    {
    }

    public function MapeoUsuario(
        $nombre,
        $apellido,
        $clave,
        $mail,
        $tipoUsuarioId = null,
        $sectorId = null,
        $fechaCreacion = null,
        $fechaUltimaModificacion = null,
        $estadoUsuarioId = null,
        $fechaBaja = null,
        $usuarioModificacion = null,
        $usuarioAlta = null
    ) {
        $this->TipoUsuarioId = $tipoUsuarioId;
        $this->SectorId = $sectorId;
        $this->EstadoUsuarioId = $estadoUsuarioId;
        $this->Nombre = $nombre;
        $this->Apellido = $apellido;
        $this->Clave = $clave;
        $this->Mail = $mail;
        $this->FechaCreacion = $fechaCreacion;
        $this->FechaUltimaModificacion = $fechaUltimaModificacion;
        $this->FechaBaja = $fechaBaja;
        $this->UsuarioModificacion = $usuarioModificacion;
        $this->UsuarioAlta = $usuarioAlta; //TODO: Averiguar como obtener el usuario se la seseion logeada
    }
    public function SetNombre($nombre)
    {
        if (isset($nombre)) {
            $this->Nombre = $nombre;
        }
    }
    public function SetApellido($apellido)
    {
        if (isset($apellido)) {
            $this->Apellido = $apellido;
        }
    }
    public function SetClave($clave)
    {
        if (isset($clave)) {
            $this->Clave = $clave;
        }
    }
    public function SetMail($mail)
    {
        if (isset($mail)) {
            $this->Mail = $mail;
        }
    }
    public function SetSectorId($sectorId)
    {
        if (isset($sectorId)) {
            $this->SectorId = $sectorId;
        }
    }
    public function GetClave()
    {
        return $this->Clave;
    }
    public function GetMail()
    {
        return $this->Mail;
    }
    public function ModificarClave()
    {
        try {
            $acceso = AccesoDatos::GetAccesoDatos();
            $consulta = $acceso->RetornarConsulta("UPDATE Usuario SET 
            Clave = '$this->Clave'
            WHERE id = '$this->Id';");
            $consulta->Execute();
            return true;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    public static function ModificarUsuario($usuarioModificado, $id)
    {
        try {
            $usuarioModificacion = "lucas"; //Revisar
            if ($usuarioDB = Usuario::FindById($id)) {
                $usuarioDB->SetNombre($usuarioModificado->Nombre);
                $usuarioDB->SetApellido($usuarioModificado->Apellido);
                $usuarioDB->SetMail($usuarioModificado->Mail);
                $usuarioDB->SetSectorId($usuarioModificado->SectorId);
                $acceso = AccesoDatos::GetAccesoDatos();
                $consulta = $acceso->prepararConsulta("UPDATE Usuario SET
                Nombre = :nombre,
                Apellido = :apellido,
                Clave = :clave,
                Mail = :mail,
                SectorId = :sectorId,
                FechaUltimaModificacion = :fechaUltimaModificacion,
                UsuarioModificacion = :usuarioModificacion 
                WHERE Id = :id");
                $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                $consulta->bindValue(':nombre', $usuarioModificado->Nombre, PDO::PARAM_STR);
                $consulta->bindValue(':apellido', $usuarioModificado->Apellido, PDO::PARAM_STR);
                $consulta->bindValue(':clave', $usuarioModificado->Clave, PDO::PARAM_STR);
                $consulta->bindValue(':mail', $usuarioModificado->Mail, PDO::PARAM_STR);
                $consulta->bindValue(':sectorId', $usuarioModificado->SectorId, PDO::PARAM_INT);
                $consulta->bindValue(':fechaUltimaModificacion', date("y-m-d"), PDO::PARAM_STR);
                $consulta->bindValue(':usuarioModificacion', $usuarioModificacion, PDO::PARAM_STR);
                return $consulta->execute();
            }
        } catch (Exception $ex) {
            throw new Exception("No se pudo modificar, " . $ex->getMessage(), 0, $ex);
        }
    }
    public static function FindBySector($sectorId)
    {
        try {
            $acceso = AccesoDatos::GetAccesoDatos();
            $consulta = $acceso->RetornarConsulta("SELECT 
                id AS Id,
                nombre AS Nombre,
                apellido AS Apellido,
                clave AS Clave,
                mail AS Mail,
                fecha_de_registro AS FechaDeRegistro,
                localidad AS Localidad FROM usuario  WHERE SectorId = $sectorId");
            $consulta->execute();
            $usuarioEncontrado = $consulta->fetchObject('Usuario');
            if (isset($usuarioEncontrado)) {
                return $usuarioEncontrado;
            } else {
                throw new Exception("No se encontró el usuario");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex);
        }
    }

    public static function FindById($id)
    {
        try {
            if (!isset($id)) {
                throw new Exception("El id no puede ser vacio");
            }
            $acceso = AccesoDatos::GetAccesoDatos();
            $consulta = $acceso->prepararConsulta("SELECT                 
            Usuario.Id AS Id,
            SectorId AS  SectorId, 
            Sector.Detalle AS SectorNombre,
            EstadoUsuarioId AS EstadoUsuarioId,
            EstadoUsuario.Detalle AS EstadoUsuario,           
            Nombre AS Nombre,
            Apellido AS Apellido,
            Clave AS Clave,
            Mail AS Mail,
            FechaCreacion AS FechaCreacion,
            FechaultimaModificacion AS FechaultimaModificacion,
            FechaBaja AS FechaBaja,
            UsuarioModificacion AS UsuarioModificacion,
            UsuarioAlta AS UsuarioAlta,
            TipoUsuarioId AS TipoUsuarioId,
            TipoUsuario.Detalle AS TipoUsuario
             FROM Usuario 
                INNER JOIN Sector ON Usuario.SectorId = Sector.Id
                 INNER JOIN TipoUsuario ON Usuario.TipoUsuarioId = TipoUsuario.Id
                 INNER JOIN EstadoUsuario ON Usuario.EstadoUsuarioId = EstadoUsuario.Id 
                 WHERE Usuario.Id = :id;");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
            $usuarioEncontrado = $consulta->fetchObject('Usuario');
            if (isset($usuarioEncontrado) && $usuarioEncontrado) {
                return $usuarioEncontrado;
            } else {
                throw new Exception("No se encontró el usuario");
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode(), $ex);
        }
    }
    public static function GetAll()
    {
        try {
            $acceso = AccesoDatos::GetAccesoDatos();
            $arrayUsuarios = array();
            $consulta = $acceso->prepararConsulta("SELECT                 
                Usuario.Id AS Id,
                SectorId AS  SectorId, 
                Sector.Detalle AS SectorNombre,
                EstadoUsuarioId AS EstadoUsuarioId,
                EstadoUsuario.Detalle AS EstadoUsuario,           
                Nombre AS Nombre,
                Apellido AS Apellido,
                Clave AS Clave,
                Mail AS Mail,
                FechaCreacion AS FechaCreacion,
                FechaultimaModificacion AS FechaultimaModificacion,
                FechaBaja AS FechaBaja,
                UsuarioModificacion AS UsuarioModificacion,
                UsuarioAlta AS UsuarioAlta,
                TipoUsuarioId AS TipoUsuarioId,
                TipoUsuario.Detalle AS TipoUsuario
                 FROM Usuario 
                 INNER JOIN Sector ON Usuario.SectorId = Sector.Id
                 INNER JOIN TipoUsuario ON Usuario.TipoUsuarioId = TipoUsuario.Id
                 INNER JOIN EstadoUsuario ON Usuario.EstadoUsuarioId = EstadoUsuario.Id;");
            $consulta->execute();
            $array = $consulta->fetchAll(PDO::FETCH_CLASS, "Usuario");
            if (is_null($array)) {
                throw new Exception("La lista esta vacia");
            }
            foreach ($array as $usuario) {
                array_push($arrayUsuarios, $usuario);
            }
            return $arrayUsuarios;
        } catch (Exception $th) {
            throw new Exception("No se pudo cargar la lista" . $th->getMessage(), 2, $th);
        }
    }
    public function GuardarUsuario()
    {
        try {
            $usuarioalta = "Lucas"; //Provisorio
            $fechaDeRegistro = date("y-m-d");
            $estadoUsuarioId = 2;
            $acceso = AccesoDatos::GetAccesoDatos();
            $consulta = $acceso->prepararConsulta("INSERT 
            INTO Usuario(SectorId,EstadoUsuarioId,Nombre, Apellido, Clave, Mail, FechaCreacion,TipoUsuarioId,UsuarioAlta) 
            VALUES (:sectorId,:estadoUsuarioID,:nombre,:apellido,:clave,:mail,:fechaCreacion,:tipoUsuarioId,:usuarioAlta);");
            $consulta->bindValue(':sectorId', $this->SectorId, PDO::PARAM_INT);
            $consulta->bindValue(':estadoUsuarioID', $estadoUsuarioId, PDO::PARAM_INT);
            $consulta->bindValue(':nombre', $this->Nombre, PDO::PARAM_STR);
            $consulta->bindValue(':apellido', $this->Apellido, PDO::PARAM_STR);
            $consulta->bindValue(':clave', $this->Clave, PDO::PARAM_STR);
            $consulta->bindValue(':mail', $this->Mail, PDO::PARAM_STR);
            $consulta->bindValue(':fechaCreacion', $fechaDeRegistro, PDO::PARAM_STR);
            $consulta->bindValue(':tipoUsuarioId', $this->TipoUsuarioId, PDO::PARAM_INT);
            $consulta->bindValue(':usuarioAlta', $usuarioalta, PDO::PARAM_STR);
            return $consulta->execute();
        } catch (Exception $th) {
            throw new Exception("No agrego correctamente " . $th->getMessage(), 1, $th);
        }
    }

    public static function BorrarUsuario($id)
    {
        try {
            if (Usuario::FindById($id)) {
                $acceso = AccesoDatos::GetAccesoDatos();
                $consulta = $acceso->prepararConsulta("DELETE FROM Usuario WHERE Id = :id");
                $consulta->bindValue(':id', $id, PDO::PARAM_INT);
                return $consulta->execute();
            } else {
                throw new Exception("No se encontro el usuario");
            }
        } catch (Exception $ex) {
            throw new Exception("No se pudo borrar el usuario, " . $ex->getMessage(), 0, $ex);
        }
    }
    public static function ListarUsuarios($listado)
    {
        $salida = "<ul>";
        foreach ($listado as $usuario) {
            $salida .= "<li>" . $usuario->MostrarDatos() . "</li>";
        }
        return $salida . "</ul>";
    }
    public function MostrarDatos()
    {
        return "$this->Nombre,$this->Apellido,$this->Clave,$this->Mail,$this->FechaDeRegistro,$this->Localidad";
    }
}
