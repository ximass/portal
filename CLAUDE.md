# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Engfrig Portal — a production management system for manufacturing (budgeting, orders, production tracking, delivery). Monorepo with two independent applications:

- **`/api`** — Laravel 12 REST API (PHP 8.2+, PostgreSQL, Sanctum auth)
- **`/front`** — Vue 3 SPA (TypeScript, Vuetify 3, Vite)


### Backend Linting/Testing
```bash
cd api && ./vendor/bin/pint      # Laravel Pint (code style)
cd api && ./vendor/bin/phpunit   # Run tests
```

## Architecture

### Authentication & Permissions
- Sanctum token-based auth. Token stored in `localStorage['authToken']`, sent as Bearer header via axios interceptor (`front/src/plugins/axios.ts`).
- CSRF cookie fetched from `/sanctum/csrf-cookie` before login.
- On 401 response: token cleared, redirect to login.
- **Permission model**: Admin users (`user.admin`) bypass all checks. Regular users belong to **Groups**, groups have **Permissions**. Custom middleware `CheckOrderPermission` enforces order-type-specific access.
- Frontend permission checking via `useAuth()` composable (`hasPermission()`, `canEditOrder()`, etc.). Route guards use `meta.requiresAuth` and `meta.requiresAdmin`.

### Frontend Patterns
- **Composition API** throughout (no Options API).
- **No state management library** (no Pinia/Vuex) — state managed via composables and component-local refs.
- **Composables** (`front/src/composables/`): `auth.ts` (login/logout), `useAuth.ts` (permissions), `useToast.ts`, `useConfirm.ts`, `useOrderCalculations.ts`, `misc.ts` (formatting for phone, CNPJ, CPF, dates).
- **Types** in `front/src/types/types.ts` — all interfaces live here.
- **Path alias**: `@` maps to `front/src/`.
- Order values (weights, taxes, prices) are calculated client-side.
- Frontend must be responsive and mobile-friendly (Vuetify's grid system, breakpoints, mobile-first design).

### Backend Patterns
- RESTful controllers in `app/Http/Controllers/`.
- Services in `app/Services/` (OrderService, PdfToWebpService, ErrorLogService).
- PDF generation via Dompdf (`PdfController`).
- **Order types**: `pre_order` (quotation) vs `order` (confirmed). Each has distinct statuses and permissions.
- Key models: `User`, `Order`, `Set`, `SetPart`, `Group`, `Permission`, `Customer`, `Material`, `Sheet`, `Bar`, `Component`, `Process`.

### Database
- PostgreSQL (default connection).
- Users ↔ Groups (many-to-many), Groups ↔ Permissions (many-to-many).
- Orders → Sets → SetParts hierarchy.
- NCM codes on materials/components carry IPI tax rates; Customer → State for ICMS tax.

## Code Style Conventions
- Use **sentence case** (not title case) on labels, buttons, links.
- Always use **Vuetify components** — pick the most appropriate component for the context.
- Frontend formatting: Prettier with single quotes, semicolons, 2-space indent, no Vue script/style indentation.
- Backend formatting: Laravel Pint (4-space PHP indentation).
