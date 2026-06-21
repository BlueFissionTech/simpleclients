# SimpleClients Test Guide

## Baseline Commands

Run Composer validation:

```powershell
composer validate --no-check-publish --strict
```

Run the default unit suite:

```powershell
vendor\bin\phpunit.bat --do-not-cache-result
```

Run PHP syntax checks when source files change:

```powershell
Get-ChildItem -Path src,tests -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

## Default Test Policy

The default test suite must not require live provider credentials or network access. Use fixtures and injectable test doubles for request/response behavior.

## Optional Integration Checks

Optional live checks may be added for provider-specific validation, but they must remain opt-in. Required credentials should be passed through environment variables or runtime configuration and must not be committed.

Suggested naming style for optional credentials:

- `SIMPLECLIENTS_<PROVIDER>_API_KEY`
- `SIMPLECLIENTS_<PROVIDER>_TOKEN`
- `SIMPLECLIENTS_<PROVIDER>_ENDPOINT`

Document any provider-specific variables alongside the optional test command that uses them.
