<?php

namespace App\Services;

use App\Exceptions\TripRequestCannotBeUpdatedException;
use App\Models\TripRequest;
use App\Models\TripStatus;
use App\Models\User;

class TripRequestService
{
    public function store(array $data, User $user): TripRequest
    {
        $pendingStatus = TripStatus::pending();

        if (!$pendingStatus) {
            throw new \DomainException('Status "solicitado" não encontrado. Execute o seeder de status.');
        }

        return TripRequest::create([
            'user_id' => $user->id,
            'destination' => $data['destination'],
            'departure_date' => $data['departure_date'],
            'return_date' => $data['return_date'],
            'status_id' => $pendingStatus->id,
        ]);
    }

    public function show(int $id, User $user): TripRequest
    {
        $trip = TripRequest::with('user')->findOrFail($id);

        if ($user->role !== 'admin' && $trip->user_id !== $user->id) {
            abort(404);
        }

        return $trip;
    }

    public function index(User $user, array $filters): \Illuminate\Database\Eloquent\Collection
    {
        $query = TripRequest::query();

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        if (!empty($filters['status'])) {
            $query->withStatus($filters['status']);
        }

        if (!empty($filters['destination'])) {
            $query->withDestination($filters['destination']);
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query->withDateRange($filters['from'], $filters['to']);
        }

        return $query->get();
    }

    public function updateStatus(int $id, string $statusName, User $user): TripRequest
    {
        if ($user->role !== 'admin') {
            throw new \App\Exceptions\ForbiddenException('Apenas administradores podem alterar o status.');
        }

        $travel = TripRequest::findOrFail($id);

        if (!$travel->canChangeStatusTo($statusName)) {
            throw new TripRequestCannotBeUpdatedException(
                $travel->status_name,
                $statusName
            );
        }

        $newStatus = TripStatus::where('name', $statusName)->first();

        if (!$newStatus) {
            throw new \DomainException("Status '{$statusName}' não encontrado.");
        }

        $travel->status_id = $newStatus->id;
        $travel->save();

        return $travel;
    }
}
