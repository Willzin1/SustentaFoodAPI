# Sustenta Food API - Laravel 12

Este é um projeto **API RESTful** desenvolvido com **Laravel 12**, responsável por fornecer os dados e funcionalidades para o sistema de gerenciamento de um restaurante. Esta API é consumida por um sistema frontend/admin separado.

---

## Tecnologias Utilizadas

- PHP 8.3+
- Laravel 12
- mySQL
- Docker
- Laravel Sanctum (Autenticação via Token)

---

## Estrutura da API

A API está organizada com os seguintes módulos principais:

- **Autenticação**
  - Registro de usuários (administradores ou clientes)
  - Login e logout com token
- **Cardápio**
  - Listagem de categorias e itens do cardápio
  - Cadastro, edição e exclusão de pratos (para administrador)
- **Reservas**
  - Criação de reservas
  - Verificação de disponibilidade de mesas
  - Envio de e-mail de confirmação de reserva
  - Listagem e gerenciamento de reservas (para administrador)
- **Usuários**
  - Consulta de dados do usuário autenticado
  - Edição e exclusão de usuário.
  - Envio de e-mail de confirmação de criação de conta

---

##  Autenticação

A API utiliza o **Laravel Sanctum** para autenticação com tokens. Após o login, o cliente receberá um token para autenticar as próximas requisições.

**Cabeçalho necessário nas requisições autenticadas:**

```http
Authorization: Bearer {TOKEN}
