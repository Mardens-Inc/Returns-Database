<?php

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
    $returnItem = ReturnItem::fromJson($data);
    try {
        if ($returnItem->insert()) {
            return $response->withStatus(200)->withJson($returnItem);
        } else
            return $response->withStatus(500)->withJson(["error" => "Failed to insert"]);
    } catch (Exception $e) {
        return $response->withStatus(500)->withJson(["error" => $e->getMessage()]);
    }
});

$app->run();