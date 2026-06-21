# SimpleClients Architecture

## Package Layout

- `src/SimpleClients/` contains public provider clients.
- `src/SimpleClients/Cloud/HttpClient.php` provides an injectable HTTP boundary for newer provider clients.
- `src/SimpleClients/Aws/SigV4.php` signs AWS-style requests.
- `src/SimpleClients/Vision`, `src/SimpleClients/Speech`, and `src/SimpleClients/Video` contain provider-family clients with shared response expectations.
- `tests/` mirrors the public client surfaces with offline fixtures and test doubles.

## Design Principles

- Prefer DevElation helpers and service patterns when they keep code concise.
- Keep provider clients direct and easy to inspect.
- Use constructor injection or small transport adapters for testability.
- Normalize provider errors into predictable arrays or method-specific return values.
- Avoid adding abstraction until it removes meaningful duplication across clients.

## Client Boundary

A SimpleClients client should own:

- Credential/config normalization.
- Endpoint and query/body construction.
- Transport call dispatch.
- Provider response parsing.
- Error response normalization.

A SimpleClients client should not own:

- Business workflows.
- Consumer-specific naming.
- Long-running orchestration.
- Runtime/session state outside the provider request.

## Upstream Dependencies

DevElation is the primary Blue Fission dependency and shapes helper use, service conventions, and value checks. Automata may shape intelligence-provider integration when a client wraps or delegates to Automata connector surfaces.

Third-party provider SDKs should be avoided unless they materially reduce risk or match an established local dependency. Prefer simple HTTP transports and fixture-backed tests where practical.

## Testing Strategy

The default suite is offline and deterministic. Tests should use fixtures, stubs, or injected transports rather than live provider calls. Optional live checks belong outside the default suite and must document required environment variables.

Provider-family extraction notes live in `PROVIDER_EXTRACTION.md`. That document should be updated before adding DNS, registrar, or hosted-mail clients so implementation remains package-owned and workflow-neutral.
