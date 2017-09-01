<?php
namespace Zwartpet\SwaggerMockerBundle\Controller;

use KleijnWeb\SwaggerBundle\Document\DocumentRepository;
use Symfony\Component\HttpFoundation\Response;

class OpenApiDocumentController
{
    /**
     * @var DocumentRepository
     */
    private $repository;

    /**
     * @param DocumentRepository $repository
     */
    public function __construct(DocumentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return Response
     */
    public function get()
    {
        $document  = $this->repository->get("default.json");
        $response  = new Response();

        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($document->getDefinition(), JSON_PRETTY_PRINT));

        return $response;
    }
}
