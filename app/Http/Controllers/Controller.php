<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "API de Viagens",
    description: "API para gerenciamento de solicitações de viagem"
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Servidor de desenvolvimento local"
)]
#[OA\Tag(
    name: "Solicitações de Viagem",
    description: "Operações relacionadas às solicitações de viagem"
)]
#[OA\Tag(
    name: "Autenticação",
    description: "Operações de autenticação"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
abstract class Controller
{
    //
}
