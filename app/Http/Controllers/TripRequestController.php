<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TripRequest;
use App\Models\TripStatus;
use OpenApi\Attributes as OA;

class TripRequestController extends Controller
{
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'required|string',
            'departure_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:departure_date',
        ]);

        // Buscar o status 'solicitado'
        $pendingStatus = TripStatus::pending();

        if (!$pendingStatus) {
            return response()->json([
                'error' => 'Status "solicitado" não encontrado. Execute o seeder de status.'
            ], 500);
        }

        $travel = TripRequest::create([
            'user_id' => auth()->id(),
            'destination' => $validated['destination'],
            'departure_date' => $validated['departure_date'],
            'return_date' => $validated['return_date'],
            'status_id' => $pendingStatus->id
        ]);

        return response()->json($travel, 201);
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
    public function show($id)
    {
        $travel = TripRequest::with('user')->findOrFail($id);

        // If the user is not an admin, ensure they own the trip request
        if (auth()->user()->role !== 'admin' && $travel->user_id !== auth()->id()) {
            abort(404); // Or 403, but 404 is better for security (don't reveal existence)
        }

        return response()->json($travel);
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
    public function index(Request $request)
    {
        $query = TripRequest::query();

        // If the user is not an admin, only show their own trip requests
        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        // Filtro por status (usando o nome do status)
        if ($request->has('status')) {
            $query->withStatus($request->status);
        }

        // Filtro por destino
        if ($request->has('destination')) {
            $query->withDestination($request->destination);
        }

        // Filtro por período de datas
        if ($request->has(['from', 'to'])) {
            $query->withDateRange($request->from, $request->to);
        }

        $tripRequests = $query->get();

        return response()->json($tripRequests);
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
    public function updateStatus(Request $request, $id)
    {
        $travel = TripRequest::findOrFail($id);

        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Apenas administradores podem alterar o status.'], 403);
        }

        $request->validate([
            'status' => 'required|in:aprovado,cancelado'
        ]);

        // Verificar se o status atual permite a mudança
        if (!$travel->canChangeStatusTo($request->status)) {
            return response()->json([
                'error' => "Não é possível alterar de '{$travel->status_name}' para '{$request->status}'."
            ], 400);
        }

        // Buscar o novo status
        $newStatus = TripStatus::where('name', $request->status)->first();

        if (!$newStatus) {
            return response()->json([
                'error' => "Status '{$request->status}' não encontrado."
            ], 400);
        }

        $travel->status_id = $newStatus->id;
        $travel->save();

        return response()->json($travel);
    }
}
