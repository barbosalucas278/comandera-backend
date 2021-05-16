<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

interface IApiUsable
{
	public function TraerUno(Request $request, Response $response);
	public function TraerTodos(Request $request, Response $response);
	public function CargarUno(Request $request, Response $response, $args);
	public function BorrarUno(Request $request, Response $response);
	public function ModificarUno(Request $request, Response $response);
}
