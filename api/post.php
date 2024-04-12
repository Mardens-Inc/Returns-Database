<?php

use EmployeeList\Returns;
use Slim\Factory\AppFactory;

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);
$app->setBasePath("/api");
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/php/Employees.php';
$employees = new Returns();


$app->post("/import", function ($request, $response, $args) use ($employees) {
    $file = $_FILES["file"] ?? [];
    $contentType = $request->getHeader("Content-Type")[0];
    return $response->withHeader("Content-Type", "application/json")->withJson(@$employees->import($contentType, $file));
});

$app->run();