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


$app->get("/", function ($request, $response, $args) use ($employees) {
    return $response->withHeader("Content-Type", "application/json")->withJson(@$employees->get());
});
$app->get("/search", function ($request, $response, $args) use ($employees) {
    $query = $request->getQueryParams()["q"];
    return $response->withHeader("Content-Type", "application/json")->withJson(@$employees->search($query));
});

$app->run();