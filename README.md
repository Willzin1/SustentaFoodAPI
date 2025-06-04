# Sustenta Food API

API REST desenvolvida em Laravel 12 para gerenciamento de restaurante, incluindo sistema de reservas, cardápio e gerenciamento de usuários.

## Sobre
Sistema desenvolvido como TCC do curso Técnico em Desenvolvimento de Sistemas, oferecendo endpoints para:
- Autenticação de usuários
- Gerenciamento de reservas
- Controle de cardápio
- Sistema de favoritos
- Relatórios administrativos
- Verificação de email

## Requisitos
- PHP 8.3+
- Laravel 12
- MySQL
- Composer

## Instalação

1. Clone o repositório
```bash
git clone [https://github.com/Willzin1/ApiLaravel]
```

2. Instale as dependências
```bash
composer install
```

3. Configure o ambiente
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure o .env com o banco de dados, e-mail para enviar as notificações e a URL do projeto do frontend

5. Configure o caminho para as imagems
```bash
php artisan storage:link
```

6. Execute as migrações
```bash
php artisan migrate
```

## Estrutura da API

### Endpoints Principais

1. **Autenticação** (`/api/login`) ou (`/api/logout`)
   - Login/Logout

2. **Usuários** (`/api/users`)
   - CRUD de usuários
   - Gerenciamento de perfis

3. **Cardápio** (`/api/cardapio`)
   - CRUD de pratos
   - Categorização
   - Sistema de favoritos

4. **Reservas** (`/api/reservas`)
   - Criação/gestão de reservas
   - Verificação de disponibilidade
   - Confirmações por email

5. **Relatórios** (`/api/relatorios`)
   - Relatórios diários
   - Relatórios semanais
   - Relatórios mensais

## Segurança
- Autenticação via Laravel Sanctum
- Rate limiting: 60 requisições/minuto
- Proteção contra CSRF
- Validação de roles (admin/user)

## Documentação
Documentação completa disponível em [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

## Suporte
Para suporte, envie um email para [william.mendonca34@gmail.com]
