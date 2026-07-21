<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTripRequestRequest;
use App\Http\Requests\UpdateTripStatusRequest;
use App\Services\TripRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TripRequestController extends Controller
{
    public function __construct(
        private TripRequestService $tripRequestService
    ) {}

    #[OA\Post(
        path: "/api/trip-requests",
        summary: "Criar uma nova solicitação de viagem",
        tags: ["Solicitações de Viagem"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            description: "Dados da solicitação de viagem",
            required: true,
            content: new OA\JsonContent(
                required: ["destination", "departure_date", "return_date"],
                properties: [
                    new OA\Property(property: "destination", type: "string", example: "Nova Iorque"),
                    new OA\Property(property: "departure_date", type: "string", format: "date", example: "2024-08-01"),
                    new OA\Property(property: "return_date", type: "string", format: "date", example: "2024-08-10")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Solicitação de viagem criada com sucesso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "user_id", type: "integer", example: 1),
                        new OA\Property(property: "destination", type: "string", example: "Nova Iorque"),
                        new OA\Property(property: "departure_date", type: "string", format: "date", example: "2024-08-01"),
                        new OA\Property(property: "return_date", type: "string", format: "date", example: "2024-08-10"),
                        new OA\Property(property: "status", type: "string", example: "solicitado")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Erro de validação")
        ]
    )]
    public function store(StoreTripRequestRequest $request): JsonResponse
    {
        $trip = $this->tripRequestService->store($request->validated(), $request->user());

        return response()->json($trip, 201);
    }

    #[OA\Get(
        path: "/api/trip-requests/{id}",
        summary: "Obter uma solicitação de viagem específica",
        security: [["bearerAuth" => []]],
        tags: ["Solicitações de Viagem"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalhes da solicitação de viagem",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "user_id", type: "integer", example: 1),
                        new OA\Property(property: "destination", type: "string", example: "Nova Iorque"),
                        new OA\Property(property: "departure_date", type: "string", format: "date", example: "2024-08-01"),
                        new OA\Property(property: "return_date", type: "string", format: "date", example: "2024-08-10"),
                        new OA\Property(property: "status", type: "string", example: "solicitado"),
                        new OA\Property(property: "user", type: "object")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Solicitação de viagem não encontrada")
        ]
    )]
    public function show(Request $request, int $id): JsonResponse
    {
        $trip = $this->tripRequestService->show($id, $request->user());

        return response()->json($trip);
    }

    #[OA\Get(
        path: "/api/trip-requests",
        summary: "Listar todas as solicitações de viagem",
        security: [["bearerAuth" => []]],
        tags: ["Solicitações de Viagem"],
        parameters: [
            new OA\Parameter(name: "status", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "destination", in: "query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "from", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "to", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de solicitações de viagem",
                content: new OA\JsonContent(type: "array", items: new OA\Items(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "destination", type: "string", example: "Nova Iorque"),
                        new OA\Property(property: "departure_date", type: "string", format: "date", example: "2024-08-01"),
                        new OA\Property(property: "return_date", type: "string", format: "date", example: "2024-08-10"),
                        new OA\Property(property: "status", type: "string", example: "solicitado")
                    ]
                ))
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $trips = $this->tripRequestService->index(
            $request->user(),
            $request->only(['status', 'destination', 'from', 'to'])
        );

        return response()->json($trips);
    }

    #[OA\Patch(
        path: "/api/trip-requests/{id}/status",
        summary: "Atualizar status da solicitação de viagem",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [
                    new OA\Property(property: "status", type: "string", enum: ["aprovado", "cancelado"], example: "aprovado")
                ]
            )
        ),
        tags: ["Solicitações de Viagem"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID da solicitação de viagem",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Status atualizado com sucesso"),
            new OA\Response(response: 403, description: "Apenas administradores podem alterar o status"),
            new OA\Response(response: 400, description: "Viagens aprovadas não podem ser canceladas")
        ]
    )]
    public function updateStatus(UpdateTripStatusRequest $request, int $id): JsonResponse
    {
        $trip = $this->tripRequestService->updateStatus($id, $request->status, $request->user());

        return response()->json($trip);
    }
}
