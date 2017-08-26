<?php

namespace Zwartpet\SwaggerMockerBundle\Tests\Controller;

use KleijnWeb\SwaggerBundle\Document\OperationObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zwartpet\SwaggerMockerBundle\Controller\DefaultController;

/**
 * @group unit
 */
class DefaultControllerUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultController
     */
    private $controller;

    public function setUp()
    {
        $this->controller = new DefaultController(__DIR__ . '/../..');
    }

    /**
     * @test
     */
    public function canReturnExampleResponseFromSpec()
    {
        $request = $this->mockRequest([
            '200' => [
                'examples' => [
                    'application/json' => 'success'
                ]
            ]
        ]);

        $response = $this->controller->getResponse($request);

        $this->assertEquals('success', $response);
    }

    /**
     * @test
     */
    public function canReturnExampleResponseFromFile()
    {
        $request = $this->mockRequest([
            '200' => [
                'examples' => [
                    'application/json' => 'success'
                ]
            ]
        ],[
            '_route_params' => ['id' => 1]
        ]);

        $response = $this->controller->getResponse($request);
        $this->assertEquals(1, $response->id);
        $this->assertEquals('Matt', $response->name);
        $this->assertEquals('Cat', $response->tag);
    }

    /**
     * @test
     */
    public function canReturn204Response()
    {
        $request = $this->mockRequest([
            '204' => [
                'description' => 'empty response'
            ]
        ]);

        /** @var Response $response */
        $response = $this->controller->getResponse($request);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals(null, $response->getContent());
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a successful response in default.yml
     */
    public function willThrowExceptionWhenNoSuccessfullResponseIsFound()
    {
        $request = $this->mockRequest([
            '500' => [
                'description' => 'Errored'
            ]
        ]);

        $this->controller->getResponse($request);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage No example found in default.yml to return
     */
    public function willThrowExceptionWhenNoExampleIsFound()
    {
        $request = $this->mockRequest([
            '200' => [
                'description' => 'Errored'
            ]
        ]);

        $this->controller->getResponse($request);
    }

    /**
     * @param $responses
     * @param array $attributes
     * @param array $query
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Request
     */
    private function mockRequest($responses, $attributes = [], $query = [])
    {
        $request = $this->getMockBuilder(Request::class)->setMethods(['get'])
            ->setConstructorArgs([
                $query,
                [],
                $attributes
            ])
            ->getMock();

        $request->expects($this->atLeastOnce())->method('get')->willReturnCallback(function ($key) use ($responses) {
            if ($key === '_route') {
                return 'swagger.default.pets.id.findPetById';
            }

            $definition = json_decode(json_encode([
                'responses' => $responses
            ]));
            $operation = $this->getMockBuilder(OperationObject::class)->disableOriginalConstructor()->getMock();
            $operation->expects($this->once())->method('getDefinition')->willReturn($definition);

            return $operation;
        });

        return $request;
    }
}
