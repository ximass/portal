# Engfrig Portal - Controle de produção

Este projeto é um portal de gerenciamento desenvolvido especificamente para a **Engfrig (Engenharia de Frigoríficos)**, focado no controle de produção de peças e máquinas. Ele oferece uma solução robusta para o acompanhamento completo do fluxo industrial, desde o orçamento até a entrega final.

## 🚀 Sobre o projeto

O Engfrig Portal visa otimizar os processos internos da engenharia, proporcionando uma visão clara e detalhada da linha de produção e facilitando a gestão administrativa através de ferramentas integradas.

### Principais funcionalidades

- 🔐 **Controle de permissões**: Gestão de acesso baseada em níveis de usuário.
- ⚙️ **Produção**: Acompanhamento em tempo real da fabricação de peças e montagem de máquinas.
- 📋 **Pedidos**: Gerenciamento de ordens de serviço e fluxos de trabalho.
- 💰 **Orçamentos**: Criação e controle de cotações para clientes.
- 📊 **Relatórios**: Geração de relatórios gerenciais e de produção (incluindo exportação em PDF).

---

## 🏗️ Arquitetura

O sistema utiliza uma arquitetura desacoplada, separando a lógica de negócio da interface do usuário para maior escalabilidade e manutenção.

- **Backend (API)**: Uma API RESTful robusta que lida com a persistência de dados e regras de negócio.
- **Frontend (Client)**: Uma SPA reativa e moderna para uma experiência de usuário fluida.

---

## 🛠️ Tecnologias utilizadas

### Backend
- **PHP 8.2+**
- **Laravel 12**
- **Laravel Sanctum**: Autenticação via tokens.
- **Dompdf**: Geração de documentos PDF.
- **Intervention Image**: Manipulação de imagens.

### Frontend
- **Vue.js 3** (Composition API)
- **TypeScript**
- **Vite**: Build tool extremamente rápido.
- **Vuetify 3**: Framework de componentes UI baseado em Material Design.
- **ApexCharts**: Visualização de dados e dashboards dinâmicos.
- **Axios**: Comunicação com a API.

---

## ⚙️ Configuração local

O projeto está dividido em duas pastas principais: `api` e `front`.

### Backend (Laravel)
1. Acesse a pasta `api`.
2. Execute `composer install`.
3. Configure o arquivo `.env`.
4. Execute as migrations: `php artisan migrate`.
5. Inicie o servidor: `php artisan serve`.

### Frontend (Vue)
1. Acesse a pasta `front`.
2. Execute `npm install`.
3. Inicie o ambiente de desenvolvimento: `npm run dev`.

---

Desenvolvido para **Engfrig**.
