<?php

use ReturnsDatabase\ReturnItem;
use Slim\Factory\AppFactory;

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);
$app->setBasePath("/api");
require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/php/ReturnItem.php';

$app->get("/", function ($request, $response, $args) {
    try {
        return $response->withHeader("Content-Type", "application/json")->withJson(@ReturnItem::getAll());
    } catch (Exception $e) {
        return $response->withHeader("Content-Type", "application/json")->withJson(["error" => $e->getMessage()])->withStatus(500);
    }

});
$app->get("/search", function ($request, $response, $args) {
    $query = $request->getQueryParams()["q"];
    return $response->withHeader("Content-Type", "application/json")->withJson(@ReturnItem::search($query));
});

$app->get("/template", function ($request, $response, $args) {
    try {
        return $response->withHeader("Content-Type", "application/json")->withJson(@ReturnItem::template());
    } catch (Exception $e) {
        return $response->withHeader("Content-Type", "application/json")->withJson(["error" => $e->getMessage()])->withStatus(500);
    }
});

$app->run();