<?php

namespace Zwartpet\SwaggerMockerBundle\Controller;

use KleijnWeb\SwaggerBundle\Document\OperationObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController
{
    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function getResponse(Request $request)
    {
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
