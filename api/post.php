<?php

use ReturnsDatabase\DrivesLicense;
use ReturnsDatabase\ReturnItem;
use Slim\Factory\AppFactory;

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);
$app->setBasePath("/api");
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/php/ReturnItem.php';


$app->post("/", function ($request, $response, $args) {
    $data = $request->getParsedBody();
    try {
        $returnItem = @ReturnItem::from_json($data);
        @$returnItem->save();
        return $response->withStatus(200)->withJson($returnItem);
    } catch (Exception $e) {
        return $response->withStatus(500)->withJson(["error" => $e->getMessage()]);
    }
});

$app->post("/license", function ($request, $response, $args) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/php/DrivesLicense.php';
    $code = file_get_contents("php://input");
    return $response->withHeader("Content-Type", "application/json")->withJson(@(new DrivesLicense($code))->toArray());
});

$app->run();