# Trip API - Coleção do Postman

Esta coleção do Postman contém todas as requisições necessárias para interagir com a Trip API, um sistema de gerenciamento de solicitações de viagem.

## Como Importar a Coleção

1.  Abra o Postman.
2.  Clique em **Import** no canto superior esquerdo.
3.  Selecione o arquivo `Travel-API-Postman-Collection.json`.
4.  A coleção será importada com todas as requisições e pastas.

## Configuração de Variáveis de Ambiente

A coleção utiliza variáveis para facilitar os testes. Configure-as em um ambiente do Postman para uma melhor experiência.

### Variáveis Principais:

*   `base_url`: A URL base da sua API. O padrão é `http://localhost:8000`.
*   `auth_token`: O token de autenticação JWT (Bearer Token). **Esta variável é preenchida automaticamente pela requisição de Login.**

## Fluxo de Uso Recomendado

1.  **Registrar um Usuário:**
    *   Use a requisição **Autenticação > Registrar Usuário** para criar uma nova conta.

2.  **Fazer Login:**
    *   Use a requisição **Autenticação > Login** com as credenciais do usuário criado.
    *   O script de teste desta requisição salvará automaticamente o `access_token` na variável de ambiente `auth_token`.

3.  **Acessar Rotas Protegidas:**
    *   As outras requisições (como criar uma solicitação de viagem) já estão configuradas para usar o `{{auth_token}}`. Elas funcionarão automaticamente após o login.

4.  **Atualizar o Token (Opcional):**
    *   Se o token expirar, você pode usar a requisição **Autenticação > Refresh Token** para obter um novo token sem precisar fazer login novamente.

## Estrutura da Coleção

A coleção está organizada nas seguintes pastas:

```
Trip API Collection/
├── Autenticação/
│   ├── Registrar Usuário
│   ├── Login
│   ├── Refresh Token
│   ├── Obter Dados do Usuário
│   └── Logout
└── Solicitações de Viagem/
    ├── Criar Solicitação de Viagem
    ├── Listar Todas as Solicitações
    ├── Obter Solicitação Específica
    └── Aprovar Solicitação (Admin)
```

## Endpoints Disponíveis

Aqui está a lista de todos os endpoints disponíveis na coleção.

### Autenticação

*   `POST /api/register` - **Registrar Usuário**
    *   Registra um novo usuário no sistema.
    *   Corpo: `{ "name": "...", "email": "...", "password": "...", "password_confirmation": "..." }`

*   `POST /api/login` - **Login**
    *   Autentica um usuário e retorna um token de acesso.
    *   Corpo: `{ "email": "...", "password": "..." }`

*   `POST /api/refresh` - **Refresh Token**
    *   Atualiza um token de autenticação expirado. Requer um token válido (mesmo que expirado) no header de autorização.

*   `GET /api/user` - **Obter Dados do Usuário**
    *   Retorna os dados do usuário atualmente autenticado.

*   `POST /api/logout` - **Logout**
    *   Invalida o token de autenticação do usuário.

### Solicitações de Viagem

*   `POST /api/trip-requests` - **Criar Solicitação de Viagem**
    *   Cria uma nova solicitação de viagem para o usuário autenticado.
    *   Corpo: `{ "destination": "...", "departure_date": "YYYY-MM-DD", "return_date": "YYYY-MM-DD" }`

*   `GET /api/trip-requests` - **Listar Todas as Solicitações**
    *   Lista as solicitações de viagem. Administradores veem todas, usuários veem apenas as suas.
    *   Suporta filtros via query params: `?status=...`, `?destination=...`, `?from=...`, `?to=...`

*   `GET /api/trip-requests/{id}` - **Obter Solicitação Específica**
    *   Busca os detalhes de uma única solicitação de viagem pelo seu ID.

*   `PATCH /api/trip-requests/{id}/status` - **Aprovar Solicitação (Admin)**
    *   Permite que um administrador altere o status de uma solicitação.
    *   Corpo: `{ "status": "aprovado" }` ou `{ "status": "cancelado" }`

## Dicas

*   **Autenticação Automática:** Não é necessário copiar e colar o token. Apenas execute a requisição de **Login** e o token será configurado para todas as outras requisições.
*   **Filtros:** A requisição "Listar Todas as Solicitações" pode ser customizada adicionando parâmetros na URL para testar as funcionalidades de filtro da API.