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
- Configurações gerais para administração

## Requisitos
- PHP 8.3+
- Laravel 12
- MySQL
- Composer

## Instalação

1. Clone o repositório
```bash
git clone https://github.com/Willzin1/SustentaFoodAPI
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

4. Configure o .env com o banco de dados, e-mail para enviar as notificações e a URL do projeto onde irá rodar o frontend

5. Configure o caminho para as imagens
```bash
php artisan storage:link
```
6. Execute as migrações
```bash
php artisan migrate
```

## Como funciona o sistema?

O Sustenta Food API foi projetado para facilitar o gerenciamento de reservas, cardápio e usuários em restaurantes. Veja abaixo um fluxo básico de uso do sistema:

1. **Cadastro e Login**
   - O usuário se cadastra via `/api/users` e recebe um e-mail para confirmação da conta.
   - Após confirmar o e-mail, faz login via `/api/login` e recebe um token de autenticação.

2. **Gerenciamento de Perfil**
   - Usuários autenticados podem atualizar seus dados ou excluir a conta.
   - O sistema valida permissões de acordo com o tipo de usuário (admin/user).

3. **Reservas**
   - Usuários autenticados ou visitantes podem criar reservas.
   - O sistema verifica automaticamente a disponibilidade de horário e limita a 4 reservas ativas por usuário.
   - Reservas para mais de 12 pessoas só podem ser feitas diretamente com o restaurante.
   - Um e-mail de confirmação é enviado ao usuário, que deve confirmar a reserva pelo link recebido.
   - Reservas podem ser alteradas ou canceladas, respeitando regras de status e permissões.

4. **Cardápio e Favoritos**
   - Usuários podem visualizar o cardápio, filtrar e buscar pratos.
   - Usuários autenticados podem favoritar ou desfavoritar pratos.
   - É possível consultar os pratos mais favoritados do sistema.

5. **Relatórios Administrativos**
   - Usuários com perfil admin podem acessar relatórios de reservas por dia, semana ou mês, com filtros e paginação.

6. **Segurança**
   - Todas as rotas sensíveis exigem autenticação via Laravel Sanctum.
   - Limite de 60 requisições por minuto por usuário.
   - Proteção contra CSRF e validação de roles.
   - Tokens expiram em 24 horas.

7. **Upload de Imagens**
   - Pratos do cardápio podem ter imagens associadas, salvas na pasta `storage/app/public/pratos`.
   - O comando `php artisan storage:link` deve ser executado após a instalação para criar o link simbólico das imagens.

8. **Verificação de E-mail**
   - Usuários só podem acessar funcionalidades protegidas após confirmar o e-mail.
   - É possível reenviar o e-mail de verificação caso necessário.

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

6. **Configurações gerais** (`/api/settings`)
    - Informações sobre o administrador
    - Pausar/retormar criação de novas reservas
    - Alterar capacidade máxima

## Documentação
Para acessar a documentação completa e detalhada, acesse [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

## Suporte
Para suporte, envie um email para [william.mendonca34@gmail.com]
