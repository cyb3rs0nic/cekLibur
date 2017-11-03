<?php
require 'vendor/autoload.php';
$app = new \Slim\App([
    'settings' => [
    "displayErrorDetails" => false
]
]);
$container = $app->getContainer();
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__.'/template', [
        'cache' => false
    ]);
    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
    return $view;
};
$container['holiday'] = function ($container) {
    $client = new Google_Client();
    $client->setApplicationName("My Project");
    $client->setDeveloperKey("AIzaSyB_kmKUXvk5ARSttby5rDDYYSCtedE4bI8");
    $service = new Google_Service_Calendar($client);
    return $service;
};
$app->get('/', function ($request, $response) {
    return $this->view->render($response, 'ceklibur.html');
});
$app->get('/check/{date}', function ($request, $response, $args) {
    $optParams = array(
        "timeMin" => $args['date']."T00:00:00.000Z",
        "timeMax" => $args['date']."T23:59:59.000Z"
    );
    $results = $this->holiday->events->listEvents('id.indonesian#holiday@group.v.calendar.google.com', $optParams);
    if (count($results->items) > 0) {
        $data = array(
            'status' => 'Libur Nasional',
            'Keterangan' => $results->items[0]->summary
        );
        $newResponse = $response->withJson($data);
        return $newResponse;
    } else {
        $data = array(
            'status' => 'Bukan Libur Nasional'
        );
        $newResponse = $response->withJson($data);
        return $newResponse;
    }
});
$app->run();
