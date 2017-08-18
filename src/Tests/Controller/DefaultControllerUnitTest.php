<?php

namespace Vancis\SwaggerMockerBundle\Tests\Controller;

use KleijnWeb\SwaggerBundle\Document\OperationObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vancis\SwaggerMockerBundle\Controller\DefaultController;

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
        $this->controller = new DefaultController();
    }

    /**
     * @test
     */
    public function canReturnExampleResponse()
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

    private function mockRequest($responses)
    {
        $definition = json_decode(json_encode([
            'responses' => $responses
        ]));
        $operation = $this->getMockBuilder(OperationObject::class)->disableOriginalConstructor()->getMock();
        $operation->expects($this->once())->method('getDefinition')->willReturn($definition);

        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $request->expects($this->once())->method('get')->willReturn($operation);

        return $request;
    }
}
