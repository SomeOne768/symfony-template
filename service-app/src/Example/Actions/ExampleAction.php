<?php

namespace App\Example\Actions;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route('/api/example', name: 'api_example', methods: ['GET'])]
final class ExampleAction
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->request->all();
        return new JsonResponse(['message' => 'Action executed', 'data' => $data]);
    }
}