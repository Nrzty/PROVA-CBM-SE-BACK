# Sistema de Gestão de Ocorrências — Prova Técnica

Backend em **Laravel** para receber eventos de um sistema externo, consolidar ocorrências, controlar ciclo de vida, registrar auditoria e processar operações de forma assíncrona.

> Nota de compatibilidade: o `composer.lock` atual exige **PHP >= 8.4** (ex.: Symfony 8). Por isso o Dockerfile usa PHP 8.4.

---

## 1) Como rodar backend e frontend

### Docker (recomendado)  fluxo mais simples

> Voc pode escolher a porta do Nginx com `APP_PORT` (padro 8000).

```bash
# subir a stack
# (opcional) export APP_PORT=8010

docker compose up -d --build

# bootstrap (primeira vez)
docker compose exec app sh -lc "[ -f .env ] || cp .env.example .env; php artisan key:generate; php artisan migrate --force"
```

Acesse:
- API: `http://localhost:${APP_PORT:-8000}`

> Observao: o container do app garante `composer install` automaticamente no startup se `vendor/` no existir (clone limpo).

---

## Rodando com Docker (detalhes)

Bootstrap em um comando:

```bash
docker compose exec app sh -lc "[ -f .env ] || cp .env.example .env; php artisan key:generate; php artisan migrate --force"
```

Ver logs:

```bash
docker compose logs --tail=200 --no-color
```

### Versão do PHP

A imagem Docker usa **PHP 8.4** porque o `composer.lock` atual possui dependências (ex.: Symfony 8) que exigem `php >= 8.4`.

---

## 2) Desenho de arquitetura

Fluxo principal (alto nível):

1. **Sistema Externo** chama `POST /api/integrations/occurrences` com `Idempotency-Key`.
2. API registra o comando em **EventInbox** (`pending`).
3. API retorna **202 Accepted** com `commandId`.
4. Um **Job** processa o EventInbox de forma assíncrona:
   - cria/atualiza `Occurrence`
   - registra `AuditLog`
   - marca EventInbox como `processed` ou `failed`

Componentes:
- **HTTP API (Laravel)**: validações, autenticação por `X-API-Key`, criação de comandos.
- **Banco (Postgres)**: Occurrence, Dispatch, EventInbox, AuditLog, jobs/failed_jobs.
- **Fila (Database Queue)**: execução assíncrona de jobs.
- **Cache (Redis)**: acelera leituras (ex: listagem de ocorrências).
- **Observabilidade**: Correlation ID via header + logs estruturados.

---

## 3) Estratégia de integração externa

Endpoint: `POST /api/integrations/occurrences`

- Exige header `Idempotency-Key`.
- O payload recebido é persistido em `EventInbox`.
- O processamento é **assíncrono** (Job), e a resposta é **202**.
- Caso chegue o mesmo evento novamente, a idempotência garante que não ocorra efeito duplicado.

Por que async?
- Evita timeout de integrações externas.
- Permite retry/backoff em caso de falhas transitórias.
- Dá rastreabilidade (eventos ficam registrados).

---

## 4) Estratégia de idempotência

A idempotência é implementada por meio do **EventInbox**:

- O sistema persiste a `idempotency_key`, `type`, `source` e `payload`.
- Uma constraint de unicidade impede duplicação por chave/tipo/origem.

Regras:
- **Mesma key + mesmo type + mesma source + mesmo payload** ⇒ retorna como duplicado (não executa efeito novamente).
- **Mesma key + mesmo type + mesma source + payload diferente** ⇒ conflito (proteção contra reuso indevido da chave).

Armazenamento e tempo:
- A chave fica no banco na tabela `event_inboxes` enquanto o registro existir.
- Em produção, pode-se implementar retenção por TTL (ex: job de limpeza após N dias), mantendo rastreabilidade necessária.

---

## 5) Estratégia de concorrência

A estratégia combina:

1) **Idempotência no banco**
- A unicidade em `EventInbox` evita que dois requests simultâneos gerem dois comandos iguais.

2) **Validação de transição de status**
- Mudanças de ciclo de vida (start/resolve/cancel) devem validar a transição antes de persistir.
- Em caso de transição inválida, o Job falha e o EventInbox fica `failed` (evitando efeito incorreto).

3) (Recomendação evolutiva) Locks / transações
- Para cenários de alta concorrência, pode-se usar `SELECT ... FOR UPDATE` na ocorrência durante a transição.

---

## 6) Pontos de falha e recuperação

Falhas comuns e tratamento:

- **Falha transitória (DB/Redis/timeout)**:
  - Jobs possuem `tries` e `backoff`.
  - Falhas são registradas em log.

- **Falha definitiva no processamento**:
  - EventInbox é marcado como `FAILED` com mensagem de erro.
  - O job pode parar em `failed_jobs` (Laravel), permitindo inspeção.

- **Reprocessamento (DLQ / re-drive)**:
  - Usar `failed_jobs` como “dead letter store”.
  - Reprocessar com `php artisan queue:retry <id>`.

---

## 7) O que ficou de fora

Itens típicos que podem ficar para evolução:
- UI completa em React (apenas o mínimo do desafio (Não sou forte no Front-End) ).
- Autenticação robusta (API keys por usuário).
- Rate limiting por consumidor.
- Observabilidade mais avançada (OpenTelemetry/Tracing distribuído).

---

## 8) Como o sistema poderia evoluir na corporação

Sugestões pragmáticas:
- **Modelo de permissões** (RBAC), auditoria com trilha de usuário real.
- **Integrações adicionais** (ex: geocodificação, cadastro de viaturas/equipes, SLA).
- **Processamento orientado a eventos** com filas dedicadas por tipo.
- **DLQ formal** (fila `dlq`) + rotinas de re-drive automatizadas.
- **Observabilidade completa**: logs estruturados padronizados + métricas + tracing.
- **Escalabilidade**: workers horizontais, particionamento por unidade/região.
