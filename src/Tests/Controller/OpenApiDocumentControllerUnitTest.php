<?php

namespace Vancis\SwaggerMockerBundle\Tests\Controller;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use KleijnWeb\SwaggerBundle\Document\SwaggerDocument;
use Vancis\SwaggerMockerBundle\Controller\OpenApiDocumentController;

/**
 * @group unit
 */
class OpenApiDocumentControllerUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $document;

    /**
     * @var OpenApiDocumentController
     */
    private $controller;
    
    public function setUp()
    {
        $repo = $this->getMockBuilder(DocumentRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->document = $this->getMockBuilder(SwaggerDocument::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->once())
            ->method('get')
            ->willReturn($this->document);

        /** @var DocumentRepository $repo */
        $this->controller = new OpenApiDocumentController($repo);
    }

    /**
     * @test
     */
    public function canGetDefinition()
    {
        $this->document->expects($this->once())
            ->method('getDefinition')
            ->willReturn('test');

        $definitionResponse = $this->controller->get('path');
        $this->assertEquals(200, $definitionResponse->getStatusCode());
        $this->assertEquals('"test"', $definitionResponse->getContent());
    }
}
