# Trip API

API REST para gerenciamento de solicitações de viagem com autenticação JWT e controle de acesso por roles.

## Tecnologias

- Laravel 12 / PHP 8.2
- MySQL 8.0 / SQLite (dev local)
- Docker & Docker Compose
- JWT Auth (tymon/jwt-auth)
- PHPUnit
- Swagger/OpenAPI (l5-swagger)

## Início Rápido

```bash
./up.sh    # Sobe containers, instala deps, roda migrations/seeders
./down.sh  # Para o ambiente
```

**Usuários criados automaticamente:**
- Admin: `admin@example.com` / `password`
- Usuários normais: 5 usuários aleatórios com senha `password`

**Acessos:**
- API: `http://localhost:8000`
- Swagger: `http://localhost:8000/api/documentation`

## Banco de Dados

Alternar entre MySQL (Docker) e SQLite (local):
```bash
cp .env.local .env   # SQLite local
cp .env.example .env  # MySQL Docker (padrão)
```

## Endpoints

### Autenticação
| Método | Rota | Descrição |
|--------|------|-----------|
| POST | `/api/register` | Registrar usuário |
| POST | `/api/login` | Login |
| POST | `/api/logout` | Logout |
| POST | `/api/refresh` | Refresh token |
| GET | `/api/user` | Dados do usuário |

### Solicitações de Viagem
| Método | Rota | Descrição |
|--------|------|-----------|
| POST | `/api/trip-requests` | Criar solicitação |
| GET | `/api/trip-requests` | Listar (com filtros) |
| GET | `/api/trip-requests/{id}` | Detalhes |
| PATCH | `/api/trip-requests/{id}/status` | Atualizar status (admin) |

### Filtros
```
GET /api/trip-requests?status=aprovado
GET /api/trip-requests?destination=Paris
GET /api/trip-requests?from=2024-08-01&to=2024-12-31
```

## Roles

- **user**: criar e visualizar próprias solicitações
- **admin**: todas as permissões + alterar status + ver todas as solicitações

## Estrutura do Projeto

```
trip-api/
├── app/
│   ├── Exceptions/
│   │   ├── ForbiddenException.php
│   │   └── TripRequestCannotBeUpdatedException.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php
│   │   │   ├── AuthController.php
│   │   │   └── TripRequestController.php
│   │   ├── Middleware/
│   │   │   └── CheckRole.php
│   │   └── Requests/
│   │       ├── StoreTripRequestRequest.php
│   │       └── UpdateTripStatusRequest.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── TripRequest.php
│   │   └── TripStatus.php
│   └── Services/
│       ├── AuthService.php
│       └── TripRequestService.php
├── bootstrap/
│   └── app.php              # Exception handlers
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── api.php
├── tests/
│   └── Feature/
├── docker-compose.yml
├── up.sh
└── down.sh
```

## Testes

```bash
php artisan test
```

## Comandos Úteis

```bash
docker exec trip_laravel_app bash                          # Acessar container
docker exec trip_laravel_app php artisan cache:clear       # Limpar cache
docker exec trip_laravel_app php artisan migrate:fresh --seed  # Recriar banco
docker-compose logs -f                                     # Ver logs
```
