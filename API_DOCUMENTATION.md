# Sustenta Food API - Documentação detalhada 

## Índice
1. [Introdução](#introdução)
2. [Autenticação](#autenticação)
3. [Informações técnicas](#informações-técnicas)
   - [Versionamento](#versionamento)
   - [Rate limiting e tokens](#rate-limiting-e-tokens)
        - [Rate limiting](#rate-limiting)
        - [Verificar expiração tokens](#tokens-e-expiração-laravel-sanctum)
4. [Endpoints](#endpoints)
   - [Usuários](#usuários)
        - [Cadastrar usuário](#criar-usuário)
        - [Listar usuários](#listar-usuários)
        - [Detalhar usuário](#detalhes-do-usuário)
        - [Atualizar usuário](#atualizar-usuário)
        - [Deletar usuário](#deletar-usuário)
   - [Autenticação](#Autenticação-de-login)
        - [Fazer login](#login)
        - [Fazer logout](#logout)
   - [Cardápio](#cardápio)
        - [Cadastrar prato](#adicionar-prato)
        - [Listar pratos](#listar-cardápio)
        - [Detalhar prato](#detalhes-do-prato)
        - [Atualizar prato](#atualizar-prato)
        - [Deletar prato](#remover-prato)
   - [Reservas](#reservas)
        - [Cadastrar reserva](#criar-reserva-usuário-logado)
        - [Cadastrar reserva (não logado)](#criar-reserva-usuário-não-logado)
        - [Listar reservas](#listar-reservas)
        - [Detalhar reserva](#detalhes-da-reserva)
        - [Atualizar reserva](#atualizar-reserva)
        - [Deletar reserva](#deletar-reserva)
   - [Favoritos](#favoritos)
        - [Favoritar um prato](#adcionar-favorito)
        - [Desfavoritar um prato](#remover-dos-favoritos)
        - [Ver pratos favoritados pelo usuário](#listar-favoritos-do-usuário)
        - [Ver pratos mais favoritados](#pratos-mais-favoritados)
   - [Relatórios](#relatórios)
        - [Reservas do dia](#reservas-por-dia)
        - [Reservas da semana](#reservas-por-semana)
        - [Reservas do mês](#reservas-por-mês)
   - [Verificação de Email](#verificação-de-email)
        - [Enviar e-mail de confirmação de reserva](#confirmar-reserva)
        - [Enviar e-mail de confirmação de conta](#verificar-email)
        - [Reenviar e-mail de confirmação de conta](#reenviar-verificação)

## Introdução

Esta API foi desenvolvida como parte do Trabalho de Conclusão de Curso (TCC) para o curso Técnico Desenvolvimento de Sistemas. O objetivo dessa API é fornecer uma interface moderna e segura para gerenciar as funcionalidades de um sistema de restaurante com foco no gerenciamento de reservas, incluindo:

- Gerenciamento de usuários
- Reservas de mesas
- Cardápio e pratos
- Pratos favoritos
- Relatórios e gráficos administrativos

A API foi desenvolvida com o framework **Laravel 12** e utiliza autenticação via **Laravel sanctum**

## Autenticação

A API utiliza autenticação baseada em tokens via **Bearer Token (Laravel Sanctum)**. Para acessar rotas protegidas/privadas, é necessário incluir o token no header de todas as requisições:
**Exemplo**
```
Authorization: Bearer {seu-token}
```
Algumas rotas só estarão disponíveis para acesso quem tiver o 'role' declarado como 'admin'

## Códigos de resposta

Listagem de códigos/status utilizados na API:

| Status | Significado 
| 200    |  OK         
| 201    |  Criado     
| 401    |  Não autorizado     
| 404    |  Não encontrado     
| 422    |  Conteúdo não processável     
| 500    |  Erro interno     

## Informações Técnicas

### Versionamento
- Versão atual: v1
- Path: /api/v1/
- Breaking changes serão introduzidos em novas versões

### Rate Limiting e Tokens
- Ainda em desenvolvimento

#### Rate Limiting
- **Limite Padrão:** 60 requisições por minuto

#### Tokens e Expiração (Laravel Sanctum)
- **Tempo de Expiração:** 24 horas
- **Configuração de Expiração:** Definida em `config/sanctum.php`
```php
'expiration' => 60 * 24, // 24 horas em minutos
```

**Verificar Expiração do Token:**
- Verifique o campo `last_used_at` na tabela `personal_access_tokens`
- Tokens não utilizados pelo período configurado são considerados expirados
- Use o comando de limpeza para remover tokens expirados:
```bash
php artisan sanctum:prune-expired --hours=24
```

## Endpoints

### Usuários

#### Criar Usuário
- **Método:** `POST`
- **Endpoint:** `/api/users`
- **Autenticação:** Público
- **Descrição:** Cria um novo usuário no sistema.
- **Regras:**
    - Envia e-mail para confirmação de conta.

# Exemplos:

**Body (JSON):**
```json
{
  "name": "João da Silva",
  "email": "joao@email.com",
  "phone": "11999999999",
  "password": "senha123"
}
```

**Retorno em caso de sucesso(201)**
```json
{
  "message": "Usuário cadastrado com sucesso",
  "user": {
    "id": 1,
    "name": "João da Silva",
    "email": "joao@email.com",
    "phone": "11999999999",
    "role": "user"
  }
}
```

**Retorno em caso de erro(400)**
```json
{
  "message": "Usuário não cadastrado",
  "errors": {
    "exception": "Mensagem do erro"
  }
}
```

#### Listar Usuários
- **Método:** `GET`
- **Endpoint:** `/api/users`
- **Autenticação:** Privado (somente usuários autenticados)
- **Descrição:** Retorna todos os usuários cadastrados em ordem pelo ID de forma decrescente

# Exemplos:

**Resposta em caso de sucesso(200)**
```json
[
  {
    "id": 1,
    "name": "João da Silva",
    "email": "joao@email.com",
    "phone": "11999999999",
    "role": "user"
  },
  {
    "id": 2,
    "name": "Flávia Oliveira",
    "email": "flavinha@gmail.com",
    "phone": "11988888888",
    "role": "user"
  }
]
```

#### Detalhes do Usuário
- **Método:** `GET`
- **Endpoint:** `api/users/{id}`
- **Autenticação:** Privado (somente usuários autenticados)
- **Descrição:** Retorna informações de um usuário específico
- **Parâmetros URL:** id (string, obrigatório) - id do usuário a ser detalhado

# Exemplos

**Resposta em caso de sucesso(201)**
```json
{
  "id": 1,
  "name": "João da Silva",
  "email": "joao@email.com",
  "phone": "11999999999",
  "role": "user"
}
```

**Resposta em caso de erro(404)**
```json
{
  "message": "Usuário não encontrado"
}
```

#### Atualizar Usuário
- **Método:** `PUT`
- **Endpoint:** `/api/users/{id}`
- **Autenticação:** Privado
- **Descrição:** Atualiza o nome e telefone de um usuário
- **Parâmetros URL:** id (string, obrigatório) - id do usuário a ser atualizado

# Exemplos

**Body (JSON)**
```json
{
  "name": "João Atualizado",
  "phone": "11988888888"
}
```

**Resposta em caso de sucesso (200)**
```json
{
  "message": "Informações alteradas com sucesso",
  "user": {
    "id": 1,
    "name": "João Atualizado",
    "email": "joao@email.com",
    "phone": "11988888888"
  }
}
```

**Resposta em caso de erro (404)**
```json
{
    "message": "Usuário não encontrado"
}
```

**Resposta em caso de erro (422)**
```json
{
  "message": "Erro ao alterar informações",
  "error": [
    "Nome é obrigatório",
    "Insira um telefone válido"
  ]
}
```

#### Deletar Usuário
- **Método:** `DELETE`
- **Endpoint:** `/api/users/{id}`
- **Autenticação:** Privado
- **Descrição:** Remove um usuário do sistema
- **Parâmetros URL:** id (string, obrigatório) - id do usuário a ser deletado

# Exemplos

**Resposta em caso de sucesso (200)**
```json
{
  "message": "Usuário deletado com sucesso"
}
```

**Resposta em caso de erro (404)**
```json
{
  "message": "Usuário não encontrado"
}
```

### Autenticação de login

#### Login
- **Endpoint:** `/api/login`
- **Autenticação:** Público
- **Descrição:** Realiza autenticação e retorna token
- **Regras:** 
    - Somente usuários com e-mail verificado podem acessar.

# Exemplos

**Body (JSON)**
```json
{
  "email": "joao@email.com",
  "password": "senha123"
}
```

**Resposta em caso de sucesso (200)**
```json
{
    "message": "Login realizado com sucesso!",
    "token": "token",
    "token_type": "bearer",
    "user": {
        "id": 1,
        "name": "João da Silva",
        "email": "joao@email.com",
        "phone": "11999999999",
        "role": "user",
    },
}
```

**Resposta em caso de erro (401)**
```json
{
    "message": "E-mail ou senha inválidos"
}
```

**Resposta em caso de erro (403)**
```json
{
    "message": "Por favor, faça a confirmação do e-mail"
}
```

#### Logout
- **Método:** `DELETE`
- **Endpoint** `/api/logout`
- **Autenticação:** Privado
- **Descrição:** Realiza o logout do usuário e invalida o token atual

# Exemplo

**Resposta em caso de sucesso (200)**
```json
{
	"message": "Logout realizado com sucesso"
}
```

### Cardápio

#### Listar Cardápio
- **Método:** `GET`
- **Endpoint:** `/api/cardapio`
- **Autenticação:** Privado
- **Descrição:** Retorna todos os pratos disponíveis e uma lista paginada
de pratos cadastrados. Aceita parâmetros de busca.
- **Parâmetros adicionais:** `search` termo para buscar. `filter` campo para filtrar (Nome, Descrição, Categoria)

# Exemplo requisição com filtros
- **Endpoint:** `/api/cardapio?search=Lasanha&filter=Nome`

**Resposta requisição com filtro (200)**
```json
{
  "paginate": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "nome": "Lasanha",
        "descricao": "Lasanha à bolonhesa",
        "imagem": "pratos/lasanha-662f4aa2d16c1.jpg",
        "categoria": "Prato principal"
      },
    ],
    "first_page_url": "/api/cardapio?page=1",
    "from": null,
    "last_page": 1,
    "last_page_url": "/api/cardapio?page=1",
    "links": [
        {
            "url": null,
            "label": "&laquo; Previous",
            "active": false
        },
        {
            "url": "/api/cardapio?page=1",
            "label": "1",
            "active": true
        },
        {
            "url": null,
            "label": "Next &raquo;",
            "active": false
        }
    ],
    "next_page_url": null,
    "path": "/api/cardapio",
    "per_page": 5,
    "prev_page_url": null,
    "to": null,
    "total": 0
  },
  "pratos": [
    {
      "id": 1,
      "nome": "Lasanha",
      "descricao": "Lasanha à bolonhesa",
      "imagem": "pratos/lasanha-662f4aa2d16c1.jpg",
      "categoria": "Prato principal"
    },
  ]
}
```

#### Adicionar Prato
- **Método:** `POST`
- **Endpoint** `/api/cardapio`
- **Autenticação** Privado (Requer role de admin)
- **Descrição:** Adiciona novo prato ao cardápio

# Exemplos

**Body (form-data ou JSON com multipart)**
- Imagem é opcional

```json
{
	"nome": "Lasanha",
	"descricao": "Lasanha à bolonhesa",
	"categoria": "Prato principal",
    "imagem": "pratos/lasanha-662f4aa2d16c1.jpg",
}
```

**Resposta em caso de sucesso (201)**
```json
{
	"message": "Prato adcionado com sucesso!",
	"prato": {
      "id": 1,
      "nome": "Lasanha",
      "descricao": "Lasanha à bolonhesa",
      "imagem": "pratos/lasanha-662f4aa2d16c1.jpg",
      "categoria": "Prato principal"
    },
}
```

**Resposta em caso de erro (422)**
```json
{
	"message": "Erro de validação",
	"errors": [
		"Nome do prato é obrigatório",
		"Descrição do prato é obrigatório",
		"Selecione uma categoria"
	]
}
```

**Resposta em caso de erro (500)**
```json
{
    "message": "Erro ao cadastrar prato",
    "errors": "Mensagem de erro"
}
```

#### Detalhes do Prato
- **Método:** `GET`
- **Endpoint:** `/api/cardapio/{id}`
- **Autenticação:** Privado (Requer role de admin)
- **Descrição:** Retorna detalhes de um prato específico
- **Parâmetros URL:** id (string, obrigatório) - id do prato a ser detalhado

# Exemplo

**Resposta em caso de sucesso (200)**
```json
{
  "id": 1,
  "nome": "Lasanha",
  "descricao": "Lasanha à bolonhesa",
  "categoria": "Massas",
  "imagem": "pratos/lasanha-662f4aa2d16c1.jpg"
}
```

**Resposta em caso de erro (404)**
```json
{
  "message": "Prato não encontrado!"
}
```

#### Atualizar Prato
- **Método:** `PUT`
- **Endpoint:** `/api/cardapio/{id}`
- **Autenticação:** Privado (Requer role admin)
- **Descrição:** Atualiza informações de um prato
- **Parâmetros URL:** id (string, obrigatório) - id do prato a ser atualizado do cardápio

# Exemplo 

**Body (form-data ou JSON com multipart)**
- Imagem é opcional

```json
{
  "nome": "Lasanha",
  "descricao": "Lasanha com molho branco",
  "categoria": "Massas",
  "imagem": "pratos/lasanha-nova-662f4bb2d16c1.jpg"
}
```

**Resposta em caso de sucesso (200)**
```json
{
  "message": "Prato alterado com sucesso!",
  "prato": {
    "id": 1,
    "nome": "Lasanha",
    "descricao": "Lasanha com molho branco",
    "categoria": "Massas",
    "imagem": "pratos/lasanha-nova-662f4bb2d16c1.jpg"
  }
}
```

**Resposta em caso de erro (400)**
```json
{
  "message": "Ocorreu um erro ao alterar reserva",
  "error": "Mensagem de erro"
}
```

**Resposta em caso de erro (422)**
```json
{
	"message": "Erro de validação",
	"errors": [
		"Nome do prato é obrigatório",
		"Descrição do prato é obrigatório",
		"Selecione uma categoria"
	]
}
```

#### Remover Prato
- **Método:** `DELETE`
- **Endpoint:** `/api/cardapio/{id}`
- **Autenticação:** Privado (Requer role admin)
- **Descrição:** Remove um prato do cardápio
- **Parâmetros URL:** id (string, obrigatório) -  id do prato a ser removido do cardápio

# Exemplo

**Resposta em caso de sucesso (200)**
```json
{
  "message": "Prato deletado com sucesso!",
  "prato": {
    "id": 1,
    "nome": "Lasanha",
    "descricao": "Lasanha à bolonhesa",
    "categoria": "Massas",
    "imagem": "pratos/lasanha-662f4aa2d16c1.jpg"
  }
}
```

**Resposta em caso de erro (404)**
```json
{
    "message": "Prato não encontrado!"
}
```

**Resposta em caso de erro (400)**
```json
{
  "message": "Ocorreu um erro ao deletar prato",
  "error": "Mensagem de erro"
}
```

### Reservas

#### Listar Reservas
- **Método:** `GET`
- **Endpoint:** `/api/reservas`
- **Autenticação:** Privado
- **Descrição:** Retorna todas as reservas e uma lista paginada. Aceita parâmetros de busca. Se 'user_id' for enviado no endpoint, filtra por usuário.
- **Parâmetros adicionais:** `search` termo para buscar. `filter` campo para filtrar (ID, Nome, Quantidade, Dia reserva, Hora reserva)

# Exemplo

**Resposta de sucesso (200)**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "data": "2025-06-15",
      "hora": "19:00",
      "quantidade_cadeiras": 4,
      "name": "João",
      "email": "joao@email.com",
      "phone": "11999999999",
      "status": "pendente"
    }
  ],  
}
```

#### Criar Reserva (Usuário Logado)
- **Método:** `POST`
- **Endpoint:** `/api/reservas`
- **Autenticação:** Privado
- **Descrição:** Cria uma nova reserva
- **Regras:** 
    - Máximo de 4 reservas ativas por usuário.
    - Até 12 pessoas por reserva (acima disso exige contato direto com o estabelecimento)
    - Verifica disponibilidade automática.
    - Envia e-mail para confirmação.

# Exemplos

**Body (JSON)**
```json
{
    "data": "2025-06-15",
    "hora": "19:00",
    "quantidade_cadeiras": 4,
}
```

**Resposta em caso de sucesso (201)**
```json
{
  "message": "Reserva feita com sucesso!",
  "reserva": {
    "id": 1,
    "user_id": 3,
    "data": "2025-06-15",
    "hora": "19:00",
    "quantidade_cadeiras": 4,
    "name": "João",
    "email": "joao@email.com",
    "phone": "11999999999",
    "status": "pendente"
  }
}
```

**Resposta em caso de erro de disponibilidade (400)**
```json
{
    "message": "Reserva indisponível para esse horário"
}
```

**Resposta em caso de erro de limite de reservas (400)**
```json
{
    "message": "Somente 4 reservas por usuário"
}
```

**Resposta em caso de erro de limite de pessoas (400)**
```json
{
    "message": "Reservas acima de 12 pessoas devem ser feitas diretamente com o restaurante."
}
```

#### Criar Reserva (Usuário Não Logado)
- **Método:** `POST`
- **Endpoint:** `/api/reservas/notLoggedUser`
- **Autenticação:** Público
- **Descrição:** Permite criar reserva sem estar autenticado
- **Regras:** 
    - Máximo de 4 reservas ativas por usuário.
    - Até 12 pessoas por reserva (acima disso exige contato direto com o estabelecimento)
    - Verifica disponibilidade automática.
    - Envia e-mail para confirmação.

# Exemplo

**Body (JSON)**
```json
{
    "data": "2025-06-16",
    "hora": "20:00",
    "quantidade_cadeiras": 2,
    "name": "Jorge",
    "email": "jorginho@gmail.com",
    "phone": "11909900990"
}
```

**Resposta em caso de sucesso (201)**
```json
{
  "message": "Reserva feita com sucesso!",
  "reserva": {
    "id": 2,
    "user_id": null,
    "data": "2025-06-16",
    "hora": "20:00",
    "quantidade_cadeiras": 2,
    "name": "Jorge",
    "email": "jorginho@gmail.com",
    "phone": "119909900990",
    "status": "pendente"
  }
}
```

**Resposta em caso de erro de disponibilidade (400)**
```json
{
    "message": "Reserva indisponível para esse horário"
}
```

**Resposta em caso de erro de limite de reservas (400)**
```json
{
    "message": "Somente 4 reservas por usuário"
}
```

**Resposta em caso de erro de limite de pessoas (400)**
```json
{
    "message": "Reservas acima de 12 pessoas devem ser feitas diretamente com o restaurante."
}
```

#### Detalhes da Reserva
- **Método:** `GET`
- **Endpoint:** `/api/reservas/{id}`
- **Autenticação:** Privado
- **Descrição:** Retorna detalhes de uma reserva específica
- **Parâmetros URL:** id (string, obrigatório) - id da reserva a ser detalhada

# Exemplo

**Resposta em caso de sucesso (200)**
```json
{
  "id": 1,
  "user_id": 3,
  "data": "2025-06-15",
  "hora": "19:00",
  "quantidade_cadeiras": 4,
  "user": {
        "id": 3, 
        "name": "João",
        "email": "joao@email.com",
        "phone": "11999999999",
        "role": "user"
    },
  "name": "João",
  "email": "joao@email.com",
  "phone": "11999999999",
  "status": "pendente"
}
```

**Resposta em caso de erro (404)**
```json
{
    "message": "Reserva não encontrada"
}
```

#### Atualizar Reserva
- **Método:** `PUT`
- **Endpoint:** `/api/reservas/{id}`
- **Autenticação:** Privado
- **Descrição:** Atualiza uma reserva existente
- **Regras:**
    - Apenas reservas não confirmadas podem ser editadas
    - Se usuário for admin, ele pode alterar qualquer reserva
    - Usuário comum altera somente as próprias reservas
    - Máximo de 4 reservas ativas por usuário.
    - Até 12 pessoas por reserva (acima disso exige contato direto com o estabelecimento)
    - Verifica disponibilidade automática.
    - Envia e-mail para confirmação.
- **Parâmetros URL:** id (string, obrigatório) - id da reserva a ser atualizada

# Exemplo

**Body (JSON)**
```json
{
    "data": "2025-06-20",
    "hora": "20:00",
    "quantidade_cadeiras": 4
}
```

**Resposta em caso de sucesso (200)**
- Reservas alteradas pelo administrador irá exibir "Reserva alterada pelo administrador!"

```json
{
  "message": "Informações alteradas com sucesso",
  "reserva": {
    "id": 1,
    "user_id": 3,
    "data": "2025-06-20",
    "hora": "20:00",
    "quantidade_cadeiras": 4
  }
}
```

**Resposta em caso de erro (404)**
```json
{
    "message": "Reserva não encontrada"
}
```

**Resposta em caso de erro (401)**
```json
{
    "message": "Ocorreu um erro ao alterar reserva"
}
```

#### Cancelar Reserva
- **Método:** `DELETE`
- **Endpoint:** `/api/reservas/{id}/cancelar`
- **Autenticação:** Privado
- **Descrição:** Cancela uma reserva existente
- **Regras:** 
    - Administrador pode cancelar qualquer reserva confirmada
    - Somente reservas confirmadas podem ser canceladas
    - Envia e-mail para o usuário
    - Se for cancelada por administrador, motivo será incluso ao e-mail
- **Parâmetros URL:** id (string, obrigatório) - id da reserva a ser cancelada

# Exemplo 
**Body (JSON) - Exclusivo para administradores**
- Reservas excluídas por um administrador terá que ter um motivo para cancelamento, tal motivo será exibido no e-mail confirmando cancelamento.

```json
{
    "motivo_cancelamento": "Restaurante irá estar fechado para reforma"
}
```

**Resposta em caso de sucesso (200) - Exclusivo para administradores**
```json
{ 
    "message": "Reserva cancelada pelo administrador!"
}
```

**Resposta em caso de erro (404)**
```json
{
    "message": "Reserva não encontrada"
}
```

**Resposta em caso de sucesso (200)**
```json
{
    "message": "Reserva cancelada com sucesso"
}
```

**Resposta em caso de erro (500)**
```json
{
    "message": "Ocorreu um erro ao fazer cancelamento da reserva",
    "error": "Mensagem de erro" 
}
```

#### Deletar Reserva
- **Método:** `DELETE`
- **Endpoint:** `/api/reservas/{id}`
- **Autenticação:** Privado
- **Descrição:** Remove uma reserva do sistema
- **Regras:** 
    - Usuário só pode deletar reservas não confirmadas
    - Usuário só pode deletar suas próprias reservas
    - Administrador pode deletar qualquer reserva
- **Parâmetros URL:** id (string, obrigatório) - id da reserva a ser deletada

# Exemplo

**Resposta em caso de sucesso (200)**
```json
{
    "message": "Reserva excluída com sucesso",
    "user": "user_id"
}
```

**Resposta em caso de erro (404)**
```json
{
    "message": "Reserva não encontrada"
}
```
**Resposta em caso de erro (400)**
```json
{
  "message": "Erro ao excluir reserva",
  "error": "Mensagem de erro"
}
```

### Favoritos

#### Adcionar Favorito
- **Método:** `POST`
- **Endpoint:** `/api/favoritos/{pratoId}`
- **Autenticação:** Privado
- **Descrição:** Adiciona um prato aos favoritos
- **Parâmetros URL:** pratoId (string, obrigatório) -  ID do prato a ser adicionado aos favoritos

# Exemplo 

**Resposta em caso de sucesso (200)**

```json
{
    "message": "Prato adcionado aos favoritos"
}
``` 

**Resposta em caso de erro (500)**
```json
{
    "message": "Erro ao adicionar prato aos favoritos",
    "error": "Mensagem de erro"
}
```

#### Remover dos Favoritos
- **Método:** `DELETE`
- **Endpoint:** `/api/favoritos/{pratoId}`
- **Autenticação:** Privado
- **Descrição:** Remove um prato dos favoritos
- **Parâmetros URL:** pratoId (string, obrigatório) -  ID do prato a ser removido dos favoritos

# Exemplo

**Resposta em caso de sucesso (200)**
```json
{
    "message": "Prato removido dos favoritos"
}
```

**Resposta em caso de erro (500)**
```json
{
    "message": "Erro ao remover prato dos favoritos",
    "error": "Mensagem de erro"
}
```

#### Listar Favoritos do Usuário
- **Método:** `GET`
- **Endpoint:** `/api/favoritos`
- **Autenticação:** Privado
- **Descrição:** Lista todos os pratos favoritados pelo usuário

# Exemplo

**Resposta em caso de sucesso (200)**
```json
{
  "favorites": [
    {
      "id": 1,
      "user_id": 5,
      "prato_id": 12,
      "prato": {
        "id": 12,
        "nome": "Pizza Margherita",
        "descricao": "Mussarela, tomate e manjericão",
        "imagem": "pratos/pizzaMargherita.jpg"
      }
    },
    {
      "id": 2,
      "user_id": 5,
      "prato_id": 13,
      "prato": {
        "id": 13,
        "nome": "Pizza Calabresa",
        "descricao": "Calabresa fatiada, cebola e oregáno",
        "imagem": "pratos/pizzaCalabresa.jpg"
      }
    },
  ]
}
```

**Resposta em caso de erro (500)**
```json
{
    "message": "Erro ao buscar favoritos",
    "error": "Mensagem de erro"
}
```

#### Pratos Mais Favoritados
- **Método:** `GET`
- **Endpoint:** `/api/favoritos/favoritados`
- **Autenticação:** Público
- **Descrição:** Lista os pratos mais favoritados pelos usuários e a quantidade de favoritos que o prato possui

# Exemplo 

**Resposta em caso de sucesso (200)**
```json
{
  "most_favorited": [
    {
      "prato": {
        "id": 12,
        "nome": "Pizza Margherita",
        "descricao": "Mussarela, tomate e manjericão",
        "imagem": "pratos/pizza.jpg",
        "categoria": "Prato principal"
      },
      "total_favoritos": 8
    },
    {
      "id": 2,
      "user_id": 5,
      "prato_id": 13,
      "prato": {
        "id": 13,
        "nome": "Pizza Calabresa",
        "descricao": "Calabresa fatiada, cebola e oregáno",
        "imagem": "pratos/pizzaCalabresa.jpg",
        "categoria": "Prato principal"
      },
      "total_favoritos": 12
    },
  ]
}
```

**Resposta em caso de erro (500)**
```json
{
    "message": "Erro ao buscar pratos mais favoritados",
    "error": "Mensagem de erro"
}
```

### Relatórios

#### Reservas por Dia
- **Método:** `GET`
- **Endpoint:** `/api/relatorios/reservas/dia`
- **Autenticação:** Privado (Requer role de admin)
- **Descrição:** Retorna relatório de reservas para o dia atual, com filtros e paginação

# Exemplo 

**Resposta em caso de sucesso (200)**
```json
{
  "total": 10,
  "confirmadas": 6,
  "pendentes": 3,
  "canceladas": 1,
  "reservas": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user_id": 2,
        "data": "2025-06-03",
        "hora": "12:00",
        "quantidade_cadeiras": 4,
        "name": "João Silva",
        "email": "joao@example.com",
        "status": "confirmada"
      }
    ],
    ...
  }
}
```

**Resposta em caso de erro (500)**
```json
{
    "message": "Erro ao buscar reservas do dia",
    "error": "Mensagem de erro"
}
```

#### Reservas por Semana
- **Endpoint:** `/api/relatorios/reservas/semana`
- **Autenticação:** Privado (Requer role de admin)
- **Descrição:** Retorna relatório filtrado pela semana, incluindo um resumo por cada dia da semana e com paginação

# Exemplo 

**Resposta em caso de sucesso (200)**
```json
{
  "total": 30,
  "confirmadas": 18,
  "pendentes": 9,
  "canceladas": 3,
  "dias": {
    "Segunda": 5,
    "Terça": 7,
  },
  "reservas": {
    "current_page": 1,
    "data": [
      {
        "id": 5,
        "user_id": 7,
        "data": "2025-06-05",
        "hora": "18:00",
        "quantidade_cadeiras": 2,
        "name": "Maria Oliveira",
        "email": "maria@example.com",
        "status": "pendente"
      }
    ],
    ...
  }
}
```

**Resposta em caso de erro (500)**
```json
{
    "message": "Erro ao buscar reservas da semana",
    "error": "Mensagem de erro"
}
```

#### Reservas por Mês
- **Método:** `GET`
- **Endpoint:** `/api/relatorios/reservas/mes`
- **Autenticação:** Privado (Requer role de admin)
- **Descrição:** Retorna relatório de reservas do mês, incluindo um resumo por semana e com paginação

# Exemplo

**Resposta em caso de sucesso (200)**
```json
{
  "total": 100,
  "confirmadas": 60,
  "pendentes": 30,
  "canceladas": 10,
  "semanas": {
    "Semana 1": 20,
    "Semana 2": 25,
    ...
  },
  "reservas": {
    "current_page": 1,
    "data": [
      {
        "id": 21,
        "user_id": 3,
        "data": "2025-06-10",
        "hora": "13:00",
        "quantidade_cadeiras": 4,
        "name": "Carlos Mendes",
        "email": "carlos@example.com",
        "status": "cancelada"
      }
    ],
    ...
  }
}
```

**Resposta em caso de erro (500)**
```json
{
    "message": "Erro ao buscar reservas do mês",
    "error": "Mensagem de erro"
}
```

### Verificação de Email

#### Confirmar Reserva
- **Método:** `GET`
- **Endpoint:** `/api/confirmar-reserva/{token}`
- **Autenticação:** Privado
- **Descrição:** Confirma uma reserva através do link enviado por email
- **Parâmetros URL:** token (string, obrigatório) - Token de confirmação gerado no momento de criação da reserva
- **Resposta:** Redireciona para página no frontend (APP_URL_FRONTEND/confirmar-reserva)

#### Verificar Email
- **Método:** `GET`
- **Endpoint:** `/api/email/verify/{id}/{hash}`
- **Autenticação:** Público (link assinado)
- **Descrição:** Verifica o email de um usuário a partir de um link de verificação
- **Parâmetros URL:** 
    - id (string, obrigatório) - ID do usuário
    - hash (string, obrigatório) - Hash SHA-1 do e-mail do usuário
- **Resposta:** Redireciona para página no frontend (APP_URL_FRONTEND/verify)

#### Reenviar Verificação
- **Método:** `POST`
- **Endpoint:** `/api/email/verification-notification`
- **Autenticação:**
- **Descrição:** Reenvia o e-mail com o link de verificação

# Exemplo

**Resposta em caso de sucesso (200)**
```json
{
  "message": "Link de verificação enviado novamente!"
}
```

**Resposta em caso de e-mail já verificado**
```json
{
  "message": "Seu e-mail já foi verificado."
}
```
