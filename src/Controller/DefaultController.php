<?php

namespace Zwartpet\SwaggerMockerBundle\Controller;

use KleijnWeb\SwaggerBundle\Document\OperationObject;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @var string
     */
    private $examplesDir;

    /**
     * @param $rootDir
     */
    public function __construct($rootDir)
    {
        $this->examplesDir = $rootDir . '/../web/swagger/examples/';
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function getResponse(Request $request)
    {
        if ($fileExamples = $this->getExamplesFromFile($request)) {
            return $fileExamples;
        }

        /** @var OperationObject $operation */
        $operation  = $request->get('_swagger_operation');
        $definition = $operation->getDefinition();
        $statusCode = $this->getStatusCode($definition->responses);

        if ($statusCode === 204) {
            return new Response(null, 204);
        }

        if (property_exists($definition->responses->{$statusCode}, 'examples') &&
            property_exists($definition->responses->{$statusCode}->{'examples'}, 'application/json')
        ) {
            return $definition->responses->{$statusCode}->{'examples'}->{'application/json'};
        }

        throw new \Exception('No example found in default.yml to return');
    }

    /**
     * @param Request $request
     * @return bool|string
     */
    private function getExamplesFromFile(Request $request)
    {
        $fs           = new Filesystem();
        $attributes   = [];

        if ($request->attributes->get('_route_params')) {
            foreach ($request->attributes->get('_route_params') as $key => $value) {
                if (substr($key, 0, 1) !== '_') {
                    $attributes[$key] = $value;
                }
            }
        }

        $examplesPath = $request->get('_route') .
            $this->getQueryString($attributes) .
            $this->getQueryString($request->query->all());

        if ($fs->exists($this->examplesDir . $examplesPath . '.json')) {
            return json_decode(file_get_contents($this->examplesDir . $examplesPath . '.json'));
        }

        return false;
    }

    /**
     * @param array $parameters
     * @return string
     */
    private function getQueryString($parameters)
    {
        ksort($parameters);
        $requestParams = http_build_query($parameters);
        
        return ($requestParams) ? "&$requestParams" : '';
    }

    /**
     * @param $responses
     * @return mixed
     * @throws \Exception
     */
    private function getStatusCode($responses)
    {
        $codes = [200, 201, 202, 204];
        foreach ($codes as $code) {
            if (property_exists($responses, $code)) {
                return $code;
            }
        }

        throw new \Exception('Could not find a successful response in default.yml');
    }
}
