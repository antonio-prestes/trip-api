# Trip API

API REST para gerenciamento de solicitações de viagem com autenticação de usuários e controle de acesso baseado em roles.

## 🚀 Sobre o Projeto

Esta API permite:
- **Autenticação de usuários** com tokens JWT (JSON Web Tokens)
- **Criação de solicitações de viagem** por usuários autenticados
- **Gerenciamento de status** das solicitações (somente administradores)
- **Filtros avançados** por status, destino e período de datas
- **Documentação Swagger** integrada

## 🛠️ Tecnologias

- **Laravel 11** - Framework PHP
- **MySQL 8.0** - Banco de dados
- **Docker & Docker Compose** - Containerização
- **JWT Auth** - Autenticação API com tokens JWT
- **PHPUnit** - Testes automatizados
- **Swagger/OpenAPI** - Documentação da API

## 📋 Pré-requisitos

- [Docker](https://www.docker.com/get-started) instalado
- [Docker Compose](https://docs.docker.com/compose/install/) instalado

## 🚀 Configuração do Ambiente

### Opções de Banco de Dados

O projeto suporta múltiplas configurações de banco de dados:

1. **MySQL com Docker** (padrão para desenvolvimento)
   - Configurado no arquivo `.env` padrão
   - Utiliza o container MySQL definido no docker-compose.yml

2. **SQLite para desenvolvimento local**
   - Configurado no arquivo `.env.local`
   - Utiliza o arquivo `database/database.sqlite`
   - Ideal para desenvolvimento sem Docker

3. **SQLite em memória para testes**
   - Configurado no arquivo `.env.testing` e `phpunit.xml`
   - Utiliza banco de dados em memória
   - Ideal para execução rápida de testes

Para alternar entre as configurações:
```bash
# Usar SQLite local (sem Docker)
cp .env.local .env

# Usar MySQL com Docker (padrão)
cp .env.example .env
```

### Opção 1: Configuração Automática (Recomendado)

1. **Clone o repositório:**
   ```bash
   git clone <url-do-repositorio>
   cd travel-api
   ```

2. **Execute o script de configuração:**
   ```bash
   ./up.sh
   ```

   O script irá:
   - Criar o arquivo `.env` baseado no `.env.example`
   - Subir os containers Docker
   - Instalar dependências do Composer
   - Executar migrations e seeders

3. **Acesse a aplicação:**
   - API: http://localhost:8000
   - Documentação Swagger: http://localhost:8000/api/documentation

### Opção 2: Configuração Manual

1. **Clone o repositório:**
   ```bash
   git clone <url-do-repositorio>
   cd travel-api
   ```

2. **Configure o ambiente:**
   ```bash
   cp .env.example .env
   ```

3. **Suba os containers:**
   ```bash
   docker-compose up -d
   ```

4. **Instale as dependências:**
   ```bash
   docker exec trip_laravel_app composer install
   ```

5. **Execute migrations e seeders:**
   ```bash
   docker exec trip_laravel_app php artisan migrate --seed
   ```

## 🧪 Executando Testes

Para executar os testes utilizando o banco de dados MySQL do Docker, utilize o seguinte comando:

```bash
# Todos os testes (SQLite em memória)
php artisan test
```

## 📚 Documentação da API

A documentação completa da API está disponível em:
- **Swagger UI:** http://localhost:8000/api/documentation
- **Postman Collection:** Importe o arquivo `Travel-API-Postman-Collection.json`

### Endpoints Principais

#### Autenticação
- `POST /api/register` - Registrar usuário
- `POST /api/login` - Login
- `GET /api/user` - Dados do usuário autenticado
- `POST /api/logout` - Logout

#### Solicitações de Viagem
- `POST /api/trip-requests` - Criar solicitação
- `GET /api/trip-requests` - Listar solicitações (com filtros)
- `GET /api/trip-requests/{id}` - Detalhes da solicitação
- `PATCH /api/trip-requests/{id}/status` - Atualizar status (admin)

### Filtros Disponíveis

```bash
# Por status
GET /api/trip-requests?status=aprovado

# Por destino
GET /api/trip-requests?destination=Paris

# Por período
GET /api/trip-requests?from=2024-08-01&to=2024-12-31

# Combinados
GET /api/trip-requests?status=solicitado&destination=Nova
```

## 👥 Roles e Permissões

### Usuário Comum (`user`)
- Criar solicitações de viagem
- Visualizar próprias solicitações
- Visualizar todas as solicitações (listagem)

### Administrador (`admin`)
- Todas as permissões do usuário comum
- Alterar status das solicitações
- Gerenciar todos os aspectos do sistema

## 💾 Estrutura do Banco

### Tabelas Principais

- **users** - Usuários do sistema
- **trip_requests** - Solicitações de viagem
- **trip_status** - Status das solicitações

### Status Disponíveis

| Status | Descrição | Cor |
|--------|-----------|-----|
| solicitado | Aguardando análise | #F59E0B |
| aprovado | Viagem aprovada | #10B981 |
| cancelado | Viagem cancelada | #EF4444 |

## 🛠️ Scripts Utilitários

### Configuração Rápida
```bash
./up.sh    # Configura e inicia todo o ambiente
./down.sh  # Para o ambiente (com opção de limpar dados)
```

## 🐛 Comandos Úteis

```bash
# Logs da aplicação
docker logs trip_laravel_app

# Acessar container do Laravel
docker exec -it trip_laravel_app bash

# Acessar MySQL
docker exec -it trip_mysql_db mysql -u laravel -psecret laravel

# Rebuild containers
docker-compose down && docker-compose up -d --build

# Limpar cache
docker exec trip_laravel_app php artisan cache:clear
docker exec trip_laravel_app php artisan config:clear

# Status dos containers
docker-compose ps

# Parar apenas um serviço
docker-compose stop laravel_app

# Ver uso de recursos
docker stats
```

## 🔄 Atualizando o Projeto

```bash
# Atualizar dependências
docker exec trip_laravel_app composer update

# Executar novas migrations
docker exec trip_laravel_app php artisan migrate

# Recriar banco (cuidado!)
docker exec trip_laravel_app php artisan migrate:fresh --seed
```

## 📁 Estrutura do Projeto

```
travel-api/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php
│   │   └── TripRequestController.php
│   └── Models/
│       ├── User.php
│       ├── TripRequest.php
│       └── TripStatus.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── routes/
│   └── api.php
├── tests/
│   └── Unit/
├── docker-compose.yml
├── up.sh
└── Travel-API-Postman-Collection.json
```
