# SimpleClients Specification

## Purpose And Scope

SimpleClients provides reusable PHP clients for external service APIs. The package should make provider access predictable without owning application workflow, orchestration, runtime policy, or consumer-specific behavior.

The core scope is:

- Authentication and credential input conventions.
- Endpoint and provider configuration.
- Request transport boundaries.
- Response and error normalization.
- Provider capability metadata where useful.
- Deterministic unit fixtures for client behavior.
- Reusable config, request, response, and capability contract objects for clients that need a generic integration boundary.

SimpleClients may grow when a provider need is general enough to fit these boundaries. New clients should not be tied to a single consuming project, local workflow, or private deployment shape.

## User Stories

- As a package consumer, I can use a provider client without rewriting auth, request, and response handling.
- As a maintainer, I can add a provider client with deterministic tests and no live credentials in the default suite.
- As a contributor, I can understand whether new behavior belongs in SimpleClients or in an application layer.
- As a reviewer, I can evaluate provider additions against package-level acceptance criteria.

## Acceptance Criteria

- Public clients keep small method surfaces with stable return shapes.
- Credentials are accepted through constructor/config input or environment fallback without committed secrets.
- Default tests run offline with fixtures or injectable doubles.
- Provider-specific behavior is isolated to the owning client or a small shared helper.
- Shared helpers stay lean and remove real duplication rather than introducing a broad framework.
- Upstream package constraints are documented when they shape implementation.
- Generic client contracts remain additive and do not force all direct clients through a common runtime path.

## External Integrations

SimpleClients includes direct clients or helpers for search, knowledge/content APIs, LLM providers, weather/location APIs, task-board APIs, OCR, transcription, video analysis, and AWS request signing.

DNS and email-provider clients may be added when their auth, config, actions, response shapes, and fixture strategy are reusable across applications. See `PROVIDER_EXTRACTION.md` for the extraction review gates.

Provider integrations should define:

- Required credentials and optional config keys.
- Request transport expectations.
- Success response shape.
- Error response shape.
- Test fixture strategy.

## Out Of Scope

- Application workflow orchestration.
- Runtime/session ownership.
- Consumer-specific environment variable names.
- Live integration tests in the default suite.
- Public GitHub issues or docs that frame work around a named consuming project.
