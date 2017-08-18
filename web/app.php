<?php
use Symfony\Component\HttpFoundation\Request;

$loader = require_once __DIR__ . '/../app/bootstrap.php.cache';

require_once __DIR__ . '/../app/AppKernel.php';

$request = Request::createFromGlobals();
Request::setTrustedProxies([$request->server->get('REMOTE_ADDR')]);

$env = 'prod';

$kernel = new AppKernel($env, false);

$kernel->loadClassCache();

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
