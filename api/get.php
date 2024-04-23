<?php

use ReturnsDatabase\Customer;
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
        $limit = $request->getQueryParams()["limit"] ?? 10;
        $limit = min($limit, 100);
        $offset = $request->getQueryParams()["offset"] ?? 0;
        $sort_column = $request->getQueryParams()["sort"] ?? "date";
        $ascending = $request->getQueryParams()["ascending"] ?? true;
        $items = @ReturnItem::range($limit, $offset, $sort_column, $ascending);
        return $response->withHeader("Content-Type", "application/json")->withJson(["items" => $items, "count" => count($items), "total" => ReturnItem::count()]);
    } catch (Exception $e) {
        return $response->withHeader("Content-Type", "application/json")->withJson(["error" => $e->getMessage()])->withStatus(500);
    }
});
$app->get("/search", function ($request, $response, $args) {
    $query = $request->getQueryParams()["q"];
    $limit = $request->getQueryParams()["limit"] ?? 10;
    $limit = min($limit, 100);
    $offset = $request->getQueryParams()["offset"] ?? 0;
    $sort_column = $request->getQueryParams()["sort"] ?? "date";
    $ascending = $request->getQueryParams()["ascending"] ?? true;
    return $response->withHeader("Content-Type", "application/json")->withJson(@ReturnItem::search($query, $limit, $offset, $sort_column, $ascending));
});

$app->get("/template", function ($request, $response, $args) {
    try {
        return $response->withHeader("Content-Type", "application/json")->withJson(@ReturnItem::empty());
    } catch (Exception $e) {
        return $response->withHeader("Content-Type", "application/json")->withJson(["error" => $e->getMessage()])->withStatus(500);
    }
});

$app->get("/count", function ($request, $response, $args) {
    return $response->withHeader("Content-Type", "application/json")->withJson(["count" => ReturnItem::count()]);
});
$app->get("/stores", function ($request, $response, $args) {
    return $response->withHeader("Content-Type", "application/json")->withJson(@\ReturnsDatabase\Store::range(50, 0, "id", true));
});



$app->get("/{id}", function ($request, $response, $args) {
    $id = $args["id"];
    $item = @ReturnItem::by_id($id);
    if ($item == null) {
        return $response->withHeader("Content-Type", "application/json")->withJson(["error" => "Item not found"])->withStatus(404);
    }
    return $response->withHeader("Content-Type", "application/json")->withJson($item);
});

$app->run();