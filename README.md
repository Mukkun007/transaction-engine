# Transaction Engine

A production-grade internal transaction engine built with Symfony 7.4, demonstrating the architecture and engineering standards I apply in critical financial environments.

> Built as a public showcase of the backend methodology I used at [BFM (Banky Foiben'i Madagasikara)](https://www.banky-foibe.mg), the Central Bank of Madagascar, where I led the migration of the core intranet platform from Symfony 3.4 to Symfony 6.4.

---

## What this project demonstrates

This is not a tutorial project. It reflects the engineering decisions and constraints of a real financial backend:

- **Double-entry bookkeeping** — every transaction generates two ledger entries (debit + credit). Account balances are computed from entries, never stored as a mutable field.
- **Idempotency** — every write operation accepts an `Idempotency-Key` header. Replaying the same key returns the cached response without creating a duplicate transaction.
- **Audit trail** — every state change (account creation, transaction, reversal) is recorded in an append-only `audit_log` table.
- **Reversal** — completed transactions can be reversed. A reversal creates a new inverse transaction linked to the original; the original is never modified.
- **Outbox pattern** — transaction events are written to an `outbox_messages` table and dispatched asynchronously to external webhooks via a console worker.
- **API key authentication** — each API client authenticates via `X-API-Key`. Clients only operate on their own data.
- **Optimistic concurrency** — balance checks on withdrawals and transfers are protected against race conditions.

---

## Architecture

```
src/
├── Domain/          # Pure business logic — no Symfony dependencies
├── Application/     # Command/Handler pairs — one class per operation
├── Infrastructure/  # Doctrine repositories, security, HTTP client
├── Api/             # API Platform processors and DTOs
└── Shared/          # Clock, IdGenerator
```

The domain layer has zero framework dependencies. Business rules (balance calculation, reversal constraints, audit logging) live entirely in `Domain/` and `Application/`, making them unit-testable without bootstrapping Symfony.

---

## Stack

| Layer | Technology |
|---|---|
| Framework | Symfony 7.4 |
| API | API Platform 4 |
| Database | MySQL 8 |
| Cache / Queue | Redis 7 |
| Async | Symfony Messenger |
| Tests | PHPUnit 11 + Foundry + DAMA |
| Static analysis | PHPStan level 8 |
| Code style | PHP-CS-Fixer |
| Runtime | PHP 8.2 |

---

## Getting started

**Requirements:** Docker, PHP 8.2+, Composer

```bash
git clone https://github.com/Mukkun007/transaction-engine.git
cd transaction-engine
composer install
docker compose up -d
php bin/console doctrine:migrations:migrate
```

Create a test API client:

```bash
php bin/console doctrine:query:sql \
  "INSERT INTO api_clients (id, name, api_key, status, created_at, updated_at) \
   VALUES (UUID_TO_BIN(UUID()), 'My Client', 'my-api-key', 'active', NOW(), NOW())"
```

---

## API endpoints

All endpoints require `X-API-Key` header and `Content-Type: application/ld+json`.

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/accounts` | Create a new account |
| `POST` | `/api/transactions/deposit` | Credit an account |
| `POST` | `/api/transactions/withdraw` | Debit an account (balance check enforced) |
| `POST` | `/api/transactions/transfer` | Transfer between two accounts |
| `POST` | `/api/transactions/{id}/reverse` | Reverse a completed transaction |

### Example — deposit

```bash
curl -X POST https://localhost:8000/api/transactions/deposit \
  -H "Content-Type: application/ld+json" \
  -H "X-API-Key: my-api-key" \
  -d '{"accountId": "...", "amount": 10000, "currency": "EUR", "description": "Initial deposit"}'
```

> Amounts are expressed in the smallest currency unit (cents). `10000` = 100.00 EUR.

---

## Running tests

```bash
php bin/phpunit
```

13 tests, 30 assertions. Covers unit tests on domain entities and integration tests on all command handlers.

---

## Outbox worker

To process pending webhook events:

```bash
php bin/console app:outbox:process
```

Configure the target URL in `.env`:

```
WEBHOOK_URL=https://your-endpoint.com/webhooks
```

---

## About

Built by **Tahiana Lova** — Backend Developer specializing in Symfony migrations and financial backends.

- Currently: Software Engineer at Pulse (Axian Group, Madagascar)
- Flagship project: Symfony 3.4 → 6.4 migration of the BFM (Central Bank of Madagascar) intranet platform
- Available for freelance engagements → [portfolio-lova.vercel.app](https://portfolio-lova.vercel.app)
