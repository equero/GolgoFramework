<?php
namespace Controllers;

use Helpers\HelperUtils;

/**
 * UNDER DEVELOPMENT
 * @author Diego Lopez Rivera <forgin50@gmail.com>
 * @version 0.0.1
 */
class ExceptionController {

	private static $msg;
	private static $code;

	public static function PermissionDenied() {
		self::$code = 401;
		self::$msg = "No tiene permisos para esta operacion.";
		self::showMessage();

	}
	public static function noOPFound() {
		self::$code = 404;
		self::$msg = "Operacion OP no encontrada.";
		self::showMessage();
	}
	public static function noSOPFound() {
		self::$code = 404;
		self::$msg = "Operacion SOP no encontrada.";
		self::showMessage();
	}
	public static function noContent() {
		self::$code = 404;
		self::$msg = "Sin contenidos.";
		self::showMessage();
	}
	public static function missingParams() {
		self::$code = 400;
		self::$msg = "Solicitud incorrecta.";
		self::showMessage();
	}
	public static function entityNotFound() {
		self::$code = 404;
		self::$msg = "Solicitud entidad no encontrada.";
		self::showMessage();
	}

	public static function subdomainNotFound() {
		self::$code = 404;
		self::$msg = "Subdominio no encontrado.";
		self::showMessage();
	}

	public static function routeNotFound() {
		self::$code = 400;
		self::$msg = "Route not found";
		self::showMessage();
	}

	public static function invalidUrl() {
		self::$code = 400;
		self::$msg = "Url no valida.";
		self::showMessage();
	}

	public static function invalidEntityLogic() {
		self::$code = 404;
		self::$msg = "Archivo de logica asociada a la entidad no encontrada.";
		self::showMessage();
	}

	public static function invalidEntityLogicAsociation() {
		self::$code = 404;
		self::$msg = "La entidad no tiene logica asociada.";
		self::showMessage();
	}

	public static function invalidUserType() {
		self::$code = 403;
		self::$msg = "Tipo de usuario no valido.";
		self::showMessage();
	}

	public static function entityDataError() {
		self::$code = 400;
		self::$msg = utf8_encode("Datos de entidad incorrectos, formulario erroneo");
		self::showMessage();
	}

	public static function customError($msg, $code) {
		self::$code = $code;
		self::$msg = HelperUtils::stringToUTF8($msg);
		self::showMessage();
	}

	public static function missingCSRF() {
		self::$code = 400;
		self::$msg = utf8_encode("Missing CSRF form token");
		self::showMessage();
	}
	public static function invalidCSRF() {
		self::$code = 401;
		self::$msg = utf8_encode("Invalid CSRF token");
		self::showMessage();
	}

	private static function showMessage() {

		if (!function_exists('http_response_code'))
		{
			header('X-PHP-Response-Code: '.self::$code, true, self::$code);

		} else {
			http_response_code(self::$code);
		}

		header('Content-type: application/json');
		echo json_encode(array(
				"code" => self::$code,
				"msg" => self::$msg)
				);
		exit();
	}

}