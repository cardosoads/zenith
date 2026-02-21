# 📊 Análise Completa do Projeto Zenith

**Data da Análise:** 16 de Fevereiro de 2026  
**Versão do Laravel:** 12.0  
**Versão do PHP:** 8.2+  
**Framework Frontend:** Vue.js 3 + Inertia.js

---

## 🎯 Visão Geral do Projeto

**Zenith** é uma plataforma SaaS de **agendamento e gestão de serviços** para prestadores de serviços (providers). O sistema permite que profissionais criem agendas personalizadas, gerenciem serviços, aceitem reservas de clientes e processem pagamentos.

### Características Principais:
- ✅ Sistema multi-agenda para prestadores de serviços
- ✅ Widget embarcável para sites externos
- ✅ Sistema de assinaturas (planos)
- ✅ Integração com pagamentos (PIX)
- ✅ Gestão de disponibilidade e regras de agendamento
- ✅ Sistema de permissões (admin/provider)
- ✅ Interface moderna com Vue.js e Tailwind CSS

---

## 🏗️ Arquitetura do Sistema

### Stack Tecnológico

#### Backend
- **Framework:** Laravel 12.0
- **Autenticação:** Laravel Sanctum
- **Permissões:** Spatie Laravel Permission
- **Frontend Bridge:** Inertia.js 2.0
- **Banco de Dados:** SQLite (configurável para MySQL/PostgreSQL)
- **Queue System:** Database driver

#### Frontend
- **Framework:** Vue.js 3.4
- **Routing:** Inertia.js + Ziggy
- **Estilização:** Tailwind CSS 3.2
- **Ícones:** Lucide Vue Next
- **Build Tool:** Vite 7.0

#### Ferramentas de Desenvolvimento
- **Laravel Breeze:** Scaffolding de autenticação
- **Laravel Pail:** Visualização de logs
- **Laravel Boost:** Otimizações de performance
- **Pest PHP:** Framework de testes
- **Concurrently:** Execução paralela de processos

---

## 📁 Estrutura de Diretórios

```
zenith/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   └── ProviderManagementController.php
│   │   │   ├── Auth/
│   │   │   ├── BookingController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── PublicWidgetController.php
│   │   │   └── ... (15+ controllers)
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── ProviderProfile.php
│   │   ├── ProviderAgenda.php
│   │   ├── Service.php
│   │   ├── Booking.php
│   │   ├── Plan.php
│   │   └── ... (13 models)
│   ├── Services/
│   ├── Contracts/
│   └── Enums/
│       ├── BookingStatus.php
│       ├── BookingSource.php
│       ├── PaymentStatus.php
│       ├── ProviderStatus.php
│       └── SubscriptionStatus.php
├── database/
│   ├── migrations/ (21 migrations)
│   ├── factories/
│   └── seeders/
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   ├── Layouts/
│   │   ├── Pages/
│   │   └── app.js
│   ├── css/
│   └── views/
├── routes/
│   ├── web.php
│   ├── auth.php
│   ├── console.php
│   └── ai.php
└── public/
```

---

## 🗄️ Modelo de Dados

### Entidades Principais

#### 1. **User** (Usuário)
- Autenticação básica
- Sistema de roles (admin/provider)
- Relacionamento 1:1 com ProviderProfile

#### 2. **ProviderProfile** (Perfil do Prestador)
- `slug`: Identificador único na URL
- `display_name`: Nome de exibição
- `timezone`: Fuso horário
- `status`: Status do provedor (enum)
- `billing_status`: Status de cobrança
- `cancellation_cutoff_hours`: Horas limite para cancelamento
- **Campos PIX:**
  - `pix_key`: Chave PIX
  - `pix_key_type`: Tipo da chave
  - `pix_holder_name`: Nome do titular
  - `pix_holder_document`: Documento do titular

**Relacionamentos:**
- `hasMany` ProviderAgenda
- `hasMany` Service
- `hasMany` Booking
- `hasMany` AvailabilityRule
- `hasMany` Unavailability
- `hasMany` ProviderSubscription

#### 3. **ProviderAgenda** (Agenda do Prestador)
- Sistema multi-agenda: um prestador pode ter várias agendas
- Cada agenda tem seu próprio slug e configurações
- Permite segmentação de serviços por agenda

**Relacionamentos:**
- `belongsTo` ProviderProfile
- `hasMany` Service
- `hasMany` Booking

#### 4. **Service** (Serviço)
- `name`: Nome do serviço
- `description`: Descrição
- `duration_minutes`: Duração em minutos
- `break_minutes`: Tempo de pausa após o serviço
- `price_cents`: Preço em centavos
- `is_active`: Status ativo/inativo

**Relacionamentos:**
- `belongsTo` ProviderProfile
- `belongsTo` ProviderAgenda
- `hasMany` Booking

#### 5. **Booking** (Reserva/Agendamento)
- **Dados do Cliente:**
  - `customer_name`
  - `customer_email`
  - `customer_phone`
  - `customer_notes`
- **Dados do Agendamento:**
  - `starts_at`: Data/hora de início
  - `ends_at`: Data/hora de término
  - `timezone`: Fuso horário
  - `status`: Status (enum: pending, confirmed, cancelled, completed)
  - `source`: Origem (enum: widget, manual)

**Relacionamentos:**
- `belongsTo` ProviderProfile
- `belongsTo` ProviderAgenda
- `belongsTo` Service
- `hasMany` BookingCustomerField
- `hasMany` BookingAttachment
- `hasOne` BookingPayment

#### 6. **Plan** (Plano de Assinatura)
- `name`: Nome do plano
- `slug`: Identificador único
- `price_cents`: Preço em centavos
- `billing_cycle`: Ciclo de cobrança
- `description`: Descrição
- `is_active`: Status ativo/inativo

**Relacionamentos:**
- `hasMany` ProviderSubscription

#### 7. **ProviderSubscription** (Assinatura do Prestador)
- Vincula prestadores aos planos
- Controla status de assinatura

#### 8. **AvailabilityRule** (Regra de Disponibilidade)
- Define horários disponíveis para agendamento
- Permite configuração por dia da semana

#### 9. **Unavailability** (Indisponibilidade)
- Bloqueios de horários específicos
- Férias, feriados, compromissos pessoais

#### 10. **BookingPayment** (Pagamento de Reserva)
- Registra pagamentos de reservas
- Status de pagamento (enum)

#### 11. **BookingAttachment** (Anexo de Reserva)
- Permite upload de arquivos relacionados a reservas

#### 12. **WidgetConfig** (Configuração do Widget)
- Configurações de personalização do widget embarcável

---

## 🔐 Sistema de Autenticação e Permissões

### Autenticação
- **Laravel Breeze** com Inertia.js
- **Laravel Sanctum** para API tokens
- Verificação de email

### Sistema de Roles
Utiliza **Spatie Laravel Permission**:

1. **Admin**
   - Acesso total ao sistema
   - Gerenciamento de prestadores
   - Ativação/suspensão de contas

2. **Provider** (Prestador)
   - Gerenciamento de agendas
   - Gerenciamento de serviços
   - Visualização de reservas
   - Configurações de pagamento

### Middleware Personalizado
- `provider.active`: Verifica se o prestador está ativo

---

## 🛣️ Rotas Principais

### Rotas Públicas
```php
GET  /                          # Landing page
GET  /embed/widget.js           # Script do widget embarcável
GET  /w/{provider}/{agenda}     # Widget público
POST /webhooks/billing/{provider}  # Webhook de cobrança
POST /webhooks/pix/{provider}      # Webhook PIX
```

### Rotas Autenticadas
```php
GET  /dashboard                 # Dashboard principal
GET  /onboarding                # Processo de onboarding
POST /onboarding/checkout       # Checkout de assinatura
POST /onboarding/confirm        # Confirmação de assinatura

# Recursos (CRUD)
/services                       # Gerenciamento de serviços
/availability-rules             # Regras de disponibilidade
/bookings                       # Gerenciamento de reservas

# Agendas
GET  /provider/agendas          # Listar agendas
POST /provider/agendas          # Criar agenda
PUT  /provider/agendas/{id}     # Atualizar agenda
DELETE /provider/agendas/{id}   # Deletar agenda
POST /provider/agendas/{id}/publish  # Publicar agenda

# Configurações
GET  /provider/payment-settings # Configurações de pagamento
PATCH /provider/payment-settings # Atualizar configurações
```

### Rotas Admin
```php
GET  /admin/providers           # Listar prestadores
POST /admin/providers/{id}/activate   # Ativar prestador
POST /admin/providers/{id}/suspend    # Suspender prestador
```

---

## 🎨 Frontend (Vue.js + Inertia.js)

### Estrutura de Componentes
```
resources/js/
├── Components/      # Componentes reutilizáveis
├── Layouts/         # Layouts da aplicação
├── Pages/           # Páginas (rotas Inertia)
├── app.js           # Bootstrap da aplicação
└── utils/           # Utilitários
```

### Principais Páginas
- **Landing.vue**: Página inicial pública
- **Dashboard.vue**: Dashboard do prestador
- **Onboarding**: Processo de cadastro e assinatura
- **Services**: Gerenciamento de serviços
- **Bookings**: Gerenciamento de reservas
- **Profile**: Perfil do usuário

---

## 💳 Sistema de Pagamentos

### Integração PIX
- Campos de configuração no `ProviderProfile`
- Webhooks para notificações de pagamento
- `PixWebhookController` para processar callbacks

### Sistema de Assinaturas
- Planos configuráveis
- Ciclos de cobrança
- Status de assinatura
- Webhooks de cobrança (`BillingWebhookController`)

---

## 🔧 Funcionalidades Principais

### 1. Widget Embarcável
- Script JavaScript para incorporação em sites externos
- Rota: `/embed/widget.js`
- Controller: `EmbedController` e `PublicWidgetController`
- Funcionalidades:
  - Exibição de serviços
  - Verificação de disponibilidade
  - Preview de reserva
  - Confirmação de reserva
  - Página de pagamento

### 2. Sistema de Agendamento
- Múltiplas agendas por prestador
- Regras de disponibilidade personalizadas
- Bloqueios de horários (unavailabilities)
- Tempo de pausa entre serviços
- Limite de cancelamento configurável

### 3. Gestão de Reservas
- Status: pending, confirmed, cancelled, completed
- Origem: widget ou manual
- Campos customizados do cliente
- Anexos de arquivos
- Notas do cliente
- Sistema de pagamentos

### 4. Onboarding
- Processo guiado de cadastro
- Seleção de plano
- Checkout integrado
- Confirmação de assinatura

### 5. Dashboard
- Visão geral de reservas
- Estatísticas
- Ações rápidas

---

## 🧪 Testes

### Framework de Testes
- **Pest PHP 4.3**: Framework de testes moderno
- **Pest Plugin Laravel**: Integração com Laravel
- **Mockery**: Mocking de objetos

### Estrutura
```
tests/
├── Feature/     # Testes de integração
└── Unit/        # Testes unitários
```

---

## 📦 Dependências Principais

### Backend (Composer)
```json
{
  "laravel/framework": "^12.0",
  "inertiajs/inertia-laravel": "^2.0",
  "laravel/sanctum": "^4.0",
  "spatie/laravel-permission": "^7.1",
  "tightenco/ziggy": "^2.0"
}
```

### Frontend (NPM)
```json
{
  "@inertiajs/vue3": "^2.0.0",
  "vue": "^3.4.0",
  "tailwindcss": "^3.2.1",
  "vite": "^7.0.7",
  "lucide-vue-next": "^0.564.0"
}
```

---

## 🚀 Scripts de Desenvolvimento

### Composer Scripts
```bash
composer setup      # Instalação completa do projeto
composer dev        # Inicia todos os serviços de desenvolvimento
composer test       # Executa testes
```

### NPM Scripts
```bash
npm run dev         # Servidor de desenvolvimento Vite
npm run build       # Build de produção
```

### Script de Desenvolvimento Completo
O comando `composer dev` inicia simultaneamente:
1. **PHP Server** (`php artisan serve`)
2. **Queue Worker** (`php artisan queue:listen`)
3. **Log Viewer** (`php artisan pail`)
4. **Vite Dev Server** (`npm run dev`)

---

## 🔒 Segurança

### Implementações
- ✅ Autenticação com Sanctum
- ✅ Verificação de email
- ✅ Sistema de permissões (Spatie)
- ✅ Middleware de proteção de rotas
- ✅ CSRF protection
- ✅ Validação de dados
- ✅ Hashing de senhas (bcrypt)

---

## 📊 Enums do Sistema

### BookingStatus
- `pending`: Pendente
- `confirmed`: Confirmado
- `cancelled`: Cancelado
- `completed`: Completado

### BookingSource
- `widget`: Reserva via widget
- `manual`: Reserva manual

### PaymentStatus
- Status de pagamentos

### ProviderStatus
- Status do prestador

### SubscriptionStatus
- Status de assinaturas

---

## 🎯 Fluxos Principais

### Fluxo de Onboarding
1. Usuário se registra
2. Acessa página de onboarding
3. Seleciona plano
4. Realiza checkout
5. Confirma assinatura
6. Acesso liberado ao sistema

### Fluxo de Agendamento (Widget)
1. Cliente acessa widget no site do prestador
2. Visualiza serviços disponíveis
3. Seleciona serviço e horário
4. Preenche dados pessoais
5. Preview da reserva
6. Confirmação
7. Pagamento (se aplicável)

### Fluxo de Gestão de Serviços
1. Prestador cria agenda
2. Configura regras de disponibilidade
3. Adiciona serviços
4. Publica agenda
5. Compartilha widget

---

## 🔄 Integrações

### Webhooks
- **Billing Webhook**: Notificações de cobrança de assinaturas
- **PIX Webhook**: Notificações de pagamentos PIX

### APIs Externas
- Sistema de pagamentos (PIX)
- Possível integração com gateways de pagamento

---

## 📈 Métricas e Observabilidade

### Logging
- **Laravel Pail**: Visualização de logs em tempo real
- **Log Stack**: Configurável via `.env`
- **Log Level**: Debug por padrão

### Queue System
- Driver: Database
- Permite processamento assíncrono de tarefas

---

## 🛠️ Configuração do Ambiente

### Variáveis de Ambiente (.env)
```env
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log
```

### Banco de Dados
- **Padrão**: SQLite (desenvolvimento)
- **Produção**: MySQL/PostgreSQL recomendado

---

## 📝 Observações e Recomendações

### Pontos Fortes
✅ Arquitetura bem estruturada  
✅ Uso de tecnologias modernas (Laravel 12, Vue 3)  
✅ Sistema de permissões robusto  
✅ Widget embarcável para fácil integração  
✅ Multi-agenda flexível  
✅ Sistema de testes configurado  

### Áreas de Melhoria
⚠️ **Documentação**: Adicionar mais documentação inline  
⚠️ **Testes**: Implementar testes automatizados  
⚠️ **API**: Considerar criar API REST para mobile  
⚠️ **Notificações**: Implementar sistema de notificações (email/SMS)  
⚠️ **Relatórios**: Adicionar dashboard com analytics  
⚠️ **Backup**: Implementar sistema de backup automático  

### Próximos Passos Sugeridos
1. Implementar seeders com dados de exemplo
2. Criar testes automatizados (Feature e Unit)
3. Documentar API endpoints
4. Implementar sistema de notificações
5. Adicionar dashboard analytics
6. Implementar sistema de avaliações/reviews
7. Adicionar suporte a múltiplos idiomas (i18n)
8. Implementar sistema de cupons/descontos

---

## 🎓 Conclusão

O projeto **Zenith** é uma plataforma SaaS bem arquitetada para gestão de agendamentos, com foco em prestadores de serviços. Utiliza tecnologias modernas e segue boas práticas do Laravel. O sistema de multi-agendas e widget embarcável são diferenciais importantes que facilitam a adoção por parte dos prestadores.

A estrutura está preparada para escalar e adicionar novas funcionalidades conforme necessário. O uso de Inertia.js proporciona uma experiência de SPA sem a complexidade de manter APIs separadas.

---

**Análise realizada por:** Antigravity AI  
**Data:** 16/02/2026  
**Versão do Documento:** 1.0
