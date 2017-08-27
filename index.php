<?php

use Core\Controllers\Router\RouteCollection;
use Core\Controllers\Router\RouteModel;
use Core\Controllers\Http\Request;

define("ROOT_PATH", __DIR__);
require_once 'GFStarter.php';
setShowError(true);




$routerCollection = RouteCollection::getInstance();
attachCustomRoutes($routerCollection);
$activeModules = array();



/*
 * Add your modules here
 */
$modules[] = "Modules\GFStarterKit\Bootstrap";
$modules[] = "Modules\GFFileManager\Bootstrap";



$App = new GFStarter();
$App->attachCustomRoutes()

$App->initModules($modules);

$App->start();


function attachCustomRoutes(RouteCollection &$routerCollection) {


	$config = array();
	$config["name"] = "";
	$config["checkCSRF"] = false;

	$config["targetClass"] = "Modules\GFStarterKit\ViewsLogic\Pages\PAGAssignGenerator";
	$route = RouteModel::withConfig("/generador", $config);
	$routerCollection->attachRoute($route);

	$route = RouteModel::withFunction("/func", function() {
		$request = Request::getInstance();
		$request->setResponseBody("<b>It Works!</b>");
	});
	$routerCollection->attachRoute($route);
}



function setShowError($showError) {
	if ($showError) {
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
	}
}