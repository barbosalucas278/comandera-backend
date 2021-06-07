<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \App\models\Producto as Producto;

require_once './models/PDF.php';
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';
class ProductosController implements IApiUsable
{
  public function DescargaPDF(Request $request, Response $response, array $args)
  {
    $ruta = "./temp/" . date("Gis") . "productos.pdf";
    try {
      ob_start();
      $pdf = new PDF();
      $pdf->SetTitle("Productos");
      $productos = Producto::all();
      $pdf->AliasNbPages();
      $pdf->AddPage();
      $pdf->SetFont('Times', '', 12);
      foreach ($productos as $prod) {
        $pdf->Body($this->ToCvs($prod));
      }
      $pdf->Output();
      ob_end_flush();
      $payload = json_encode(array("Resultado" => "Descargado"));
      $response->getBody()->write($payload);

      return $response
        ->withHeader('Content-Type', 'application/csv')
        ->withStatus(200);
    } catch (Exception $ex) {
      $error = $ex->getMessage();
      $datosError = json_encode(array("Error" => $error));
      $response->getBody()->write($datosError);
      return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
  }
  public function CargaCSV(Request $request, Response $response, array $args)
  {
    try {
      if ($request->getUploadedFiles()) {
        $archivo = $request->getUploadedFiles();
        $destino = "./temp/";
        $nombreAnterior = $archivo['archivo']->getClientFileName();
        $extension = explode(".", $nombreAnterior);
        $extension = array_reverse($extension)[0];
        if ($extension != "csv") {
          throw new Exception("El tipo de foto es incorrecto");
        }
        $pathArchivo = $destino . "." . date("Gis");
        $archivo = $archivo['archivo'];
        $archivo->moveTo($pathArchivo);


        $archivoAbierto = fopen($pathArchivo, 'r');
        $arrayObjetos = array();
        if ($archivoAbierto != null) {
          while (!feof($archivoAbierto)) {
            $aux = fgets($archivoAbierto);
            $lectura = explode(',', $aux);
            if (
              isset($lectura[0]) && !empty($lectura[0]) &&
              isset($lectura[1]) && !empty($lectura[1]) &&
              isset($lectura[2]) && !empty($lectura[2]) &&
              isset($lectura[3]) && !empty($lectura[3]) &&
              isset($lectura[4]) && !empty($lectura[4])
            ) {
              $prodGenerico = (object)[
                "Codigo" => $lectura[0], "TipoProductoId" => $lectura[1],
                "Nombre" => $lectura[2], "Stock" => $lectura[3], "Precio" => $lectura[4]
              ];
              array_push($arrayObjetos, $prodGenerico);
            }
          }
          fclose($archivoAbierto);
          unlink($pathArchivo);
          if (count($arrayObjetos) > 0) {
            $this->ActualizarProductos($arrayObjetos);
            $payload = json_encode(array("Resultado" => "Agregado"));
            $response->getBody()->write($payload);
          }
        }
        return $response
          ->withHeader('Content-Type', 'application/csv')
          ->withStatus(200);
      }
      throw new Exception("No se subio ningun archivo");
    } catch (Exception $ex) {
      $error = $ex->getMessage();
      $datosError = json_encode(array("Error" => $error));
      $response->getBody()->write($datosError);
      return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
  }
  private function ActualizarProductos($listaNueva)
  {
    if (isset($listaNueva)) {
      $listaActual = Producto::all();
      foreach ($listaNueva as $prodNuevo) {
        $flagExists = 0;
        foreach ($listaActual as $prodViejo) {
          if ($this->Equals($prodNuevo, $prodViejo)) {
            $this->ModificarStock($prodNuevo->Stock, $prodViejo);
            $this->ModificarPrecio($prodNuevo->Precio, $prodViejo);
            $prodViejo->save();
            $flagExists = 1;
            continue;
          }
        }
        if ($flagExists == 0) {
          $newProducto = new Producto();
          $newProducto->Codigo = $prodNuevo->Codigo;
          $newProducto->TipoProductoId = $prodNuevo->TipoProductoId;
          $newProducto->Nombre = $prodNuevo->Nombre;
          $newProducto->Stock = $prodNuevo->Stock;
          $newProducto->Precio = $prodNuevo->Precio;
          $newProducto->save();
        }
      }
    }
  }
  public function ModificarPrecio($precioNuevo, $productoViejo)
  {
    if ($productoViejo->Precio < $precioNuevo) {
      $productoViejo->Stock = $precioNuevo;
    }
  }
  public function ModificarStock($cantidad, $productoViejo)
  {
    $productoViejo->Stock += $cantidad;
  }
  public function Equals($prod1, $prod2)
  {
    return $prod1->Codigo == $prod2->Codigo;
  }
  public function DescargaCSV(Request $request, Response $response, array $args)
  {
    $ruta = __DIR__ . "/temp/" . date("Gis") . "productos.csv";
    try {
      $archivo = fopen($ruta, "a");
      if ($archivo != null) {
        $productos = Producto::all();
        foreach ($productos as $prod) {
          fwrite($archivo, $this->ToCvs($prod));
        }
        fclose($archivo);
      }
      if (readfile($ruta)) {
        unlink($ruta);
        return $response
          ->withHeader('Content-Type', 'application/csv')
          ->withStatus(200);
      }
    } catch (Exception $ex) {
      $error = $ex->getMessage();
      $datosError = json_encode(array("Error" => $error));
      $response->getBody()->write($datosError);
      return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
  }
  private function ToCvs($producto)
  {
    $datos = "$producto->Codigo,$producto->TipoProductoId,$producto->Nombre,$producto->Stock,$producto->Precio,$producto->FechaCreacion,$producto->FechaUltimaModificacion,$producto->Eliminado\n";
    return $datos;
  }
  public function TraerUno(Request $request, Response $response, array $args)
  {
    try {
      //Los datos ingresados por la url se buscan en args
      $id = $args["id"];
      $producto = Producto::where('Id', '=', $id)->first();
      if (is_null($producto)) {
        throw new Exception("El producto no existe");
      }
      $datos = json_encode($producto);
      $response->getBody()->write($datos);
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
    } catch (Exception $ex) {
      $error = $ex->getMessage();
      $datosError = json_encode(array("Error" => $error));
      $response->getBody()->write($datosError);
      return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
  }
  public function TraerTodos(Request $request, Response $response, array $args)
  {
    try {
      $datos = json_encode(Producto::all());
      $response->getBody()->write($datos);
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
    } catch (Exception $ex) {
      $error = $ex->getMessage();
      $datosError = json_encode(array("Error" => $error));
      $response->getBody()->write($datosError);
      return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
  }
  public function CargarUno(Request $request, Response $response, array $args)
  {
    try {
      $datosIngresados = $request->getParsedBody()["body"];
      //Validación de datosIngresados
      if (
        !isset($datosIngresados["codigo"])
        || !isset($datosIngresados["tipoProductoId"])
        || !isset($datosIngresados["nombre"])
        || !isset($datosIngresados["stock"])
        || !isset($datosIngresados["precio"])
      ) {
        $error = json_encode(array("Error" => "Datos incompletos"));
        $response->getBody()->write($error);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(404);
      }
      $codigo = $datosIngresados["codigo"];
      $tipoProductoId = $datosIngresados["tipoProductoId"];
      $nombre = $datosIngresados["nombre"];
      $stock = $datosIngresados["stock"];
      $precio = $datosIngresados["precio"];
      $newProducto = new Producto();
      $newProducto->Codigo = $codigo;
      $newProducto->TipoProductoId = $tipoProductoId;
      $newProducto->Nombre = $nombre;
      $newProducto->Stock = $stock;
      $newProducto->Precio = $precio;
      if ($newProducto->save()) {
        $payload = json_encode(array("Resultado" => "Agregado"));
      }
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
    } catch (Exception $ex) {
      $error = $ex->getMessage();
      $datosError = json_encode(array("Error" => $error));
      $response->getBody()->write($datosError);
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(500);
    }
  }
  public function BorrarUno(Request $request, Response $response, array $args)
  {
  }
  public function ModificarUno(Request $request, Response $response, array $args)
  {
  }
}
