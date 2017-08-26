<?php

namespace Zwartpet\SwaggerMockerBundle\Routing;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use KleijnWeb\SwaggerBundle\Document\SwaggerDocument;
use KleijnWeb\SwaggerBundle\Routing\SwaggerRouteLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class SwaggerMockRouteLoader extends SwaggerRouteLoader
{
    const CONTROLLER_KEY = 'swagger.controller.default:getResponse';
    const ROUTE_NAME     = 'swagger.controller.default';

    /**
     * @var array
     */
    private $loadedSpecs = [];

    /**
     * @var DocumentRepository
     */
    private $documentRepository;

    /**
     * @param DocumentRepository $documentRepository
     */
    public function __construct(DocumentRepository $documentRepository)
    {
        $this->documentRepository = $documentRepository;
        parent::__construct($documentRepository);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param mixed  $resource
     * @param string $type
     *
     * @return bool
     */
    public function supports($resource, $type = null)
    {
        return 'swagger-mock' === $type;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param mixed $resource
     * @param null  $type
     *
     * @return RouteCollection
     */
    public function load($resource, $type = null)
    {
        $resource = (string)$resource;
        if (in_array($resource, $this->loadedSpecs)) {
            throw new \RuntimeException("Resource '$resource' was already loaded");
        }

        $document = $this->documentRepository->get($resource);

        $routes = new RouteCollection();

        $basePath = $document->getDefinition()->basePath;
        $paths = $document->getPathDefinitions();
        $router = 'swagger.controller';
        foreach ($paths as $path => $pathSpec) {
            if ($path === 'x-router') {
                $router = $pathSpec;
                unset($paths->$path);
            }
        }
        foreach ($paths as $path => $methods) {
            $relativePath = ltrim($path, '/');
            $resourceName = strpos($relativePath, '/')
                ? substr($relativePath, 0, strpos($relativePath, '/'))
                : $relativePath;
            $routerController = null;
            foreach ($methods as $methodName => $operationSpec) {
                if ($methodName === 'x-router-controller') {
                    $routerController = $operationSpec;
                    unset($methods->$methodName);
                }
            }
            foreach ($methods as $methodName => $operationSpec) {
                $controllerKey = $this->resolveControllerKey(
                    $operationSpec,
                    $methodName,
                    $resourceName,
                    $router,
                    $routerController
                );
                $defaults = [
                    '_controller'   => $controllerKey,
                    '_definition'   => $resource,
                    '_swagger_path' => $path
                ];

                $routePath = ltrim($basePath, '/') . $path;
                $routeName = $this->resolveRouteName($operationSpec, $methodName);

                $route = new Route($routePath, $defaults, $this->resolveRequirements($document, $path, $methodName));
                $route->setMethods($methodName);
                $routes->add($this->createRouteId($resource, $path, $routeName), $route);
            }
        }

        $this->loadedSpecs[] = $resource;

        return $routes;
    }

    /**
     * @param SwaggerDocument $document
     * @param                 $path
     * @param                 $methodName
     *
     * @return array
     */
    private function resolveRequirements(SwaggerDocument $document, $path, $methodName)
    {
        $operationObject = $document->getOperationObject($path, $methodName);

        $requirements = [];

        foreach ($operationObject->getParameters() as $paramDefinition) {
            if ($paramDefinition->in === 'path' && isset($paramDefinition->type)) {
                switch ($paramDefinition->type) {
                    case 'integer':
                        $requirements[$paramDefinition->name] = '\d+';
                        break;
                    case 'string':
                        if (isset($paramDefinition->pattern)) {
                            $requirements[$paramDefinition->name] = $paramDefinition->pattern;
                            break;
                        }
                        if (isset($paramDefinition->enum)) {
                            $requirements[$paramDefinition->name] = '(' .
                                implode('|', $paramDefinition->enum)
                                . ')';
                            break;
                        }
                        break;
                    default:
                        //NOOP
                }
            }
        }

        return $requirements;
    }

    /**
     * @param        $operationSpec
     * @param        $methodName
     * @param        $resourceName
     * @param string $router
     * @param null   $routerController
     *
     * @return string
     */
    private function resolveControllerKey(
        $operationSpec,
        $methodName,
        $resourceName,
        $router,
        $routerController = null
    ) {
        return self::CONTROLLER_KEY;
    }

    /**
     * @param $operationSpec
     * @param $methodName
     *
     * @return string
     */
    private function resolveRouteName(
        $operationSpec,
        $methodName
    ) {
        $operationName = $methodName;
        if (isset($operationSpec->operationId)) {
            if (false !== strpos($operationSpec->operationId, ':')) {
                $operationName = substr($operationSpec->operationId, strpos($operationSpec->operationId, ':'));
                return self::ROUTE_NAME . "$operationName";
            }
            $operationName = $operationSpec->operationId;
        }

        if (property_exists($operationSpec, 'x-router-controller-method')) {
            $operationName = $operationSpec->{'x-router-controller-method'};
        }

        return self::ROUTE_NAME . ":$operationName";
    }

    /**
     * @param string $resource
     * @param string $path
     *
     * @param string $controllerKey
     *
     * @return string
     */
    private function createRouteId($resource, $path, $controllerKey)
    {
        list(, $operationName) = explode(':', $controllerKey);
        $fileName = pathinfo($resource, PATHINFO_FILENAME);
        $normalizedPath = strtolower(trim(preg_replace('/\W+/', '.', $path), '.'));
        $routeName = "swagger.{$fileName}.$normalizedPath.$operationName";

        return $routeName;
    }
}
