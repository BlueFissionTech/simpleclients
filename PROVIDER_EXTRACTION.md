# Provider Extraction Review

SimpleClients can grow DNS and email provider clients when the behavior is reusable across applications and stable enough to test without live accounts. Extraction should keep this package focused on provider access, not operational workflows.

## Review Result

DNS and email-provider APIs are reasonable future SimpleClients candidates when they expose portable provider actions:

- DNS zone and record listing.
- DNS record create, update, delete, and verification.
- Registrar DNS reads where the provider exposes stable API access.
- Mail domain, mailbox, alias, routing, and DNS-readiness reads.
- Mail-provider account actions that can be expressed without application workflow state.

Workflow sequencing, migration policy, retry schedules, tenant ownership, and cutover decisions should remain outside this package.

## Candidate Client Boundaries

Cloud DNS client:

- Auth: API token or key/secret pair.
- Config: `base_url`, `account_id`, `zone_id`, optional `timeout`.
- Actions: `zones()`, `records()`, `createRecord()`, `updateRecord()`, `deleteRecord()`.
- Responses: normalized arrays with provider id, name, type, value, ttl, priority, proxied/routing flags when available, and raw provider payload.

Registrar DNS client:

- Auth: API key plus provider-required username, client IP, or token fields.
- Config: `base_url`, `customer_id`, `domain`.
- Actions: `records()`, `setRecords()`, `deleteRecord()` where the upstream API supports it.
- Responses: normalized record arrays plus provider status and raw payload.

Hosted mail client:

- Auth: API token, API key, or mailbox admin token.
- Config: `base_url`, `domain`, optional `account_id`.
- Actions: `domains()`, `mailboxes()`, `aliases()`, `routes()`, `domainStatus()`.
- Responses: normalized domain, mailbox, alias, and routing arrays with raw provider payload.

## Common Config Signature

Provider clients should accept constructor config and optional transport injection:

```php
new ProviderClient([
    'api_key' => '...',
    'token' => '...',
    'base_url' => 'https://provider.example',
    'account_id' => 'account',
    'domain' => 'example.com',
], $http);
```

Environment fallback may exist for convenience, but package code should not require application-specific environment variable names.

## Acceptance Gates

Before adding a provider client:

- The provider actions are generic and useful outside one workflow.
- Required auth and config fields are documented with portable names.
- Default tests use fixtures or injected transports, not live services.
- Success and error response shapes are normalized and stable.
- Provider-specific quirks stay inside the owning client.
- Any workflow migration remains a thin application layer over SimpleClients.

## Migration Notes

When moving mature provider access into SimpleClients:

- Keep application command names, workflow state, and operator policy outside this package.
- Introduce SimpleClients clients as parallel adapters first, then switch callers once fixtures match.
- Preserve raw provider payloads under a `raw` key where practical so callers can audit provider-specific fields.
- Keep destructive provider actions explicit and previewable in the caller; this package should expose the client capability, not decide whether an operation should run.
