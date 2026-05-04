# Contributing

Thanks for considering a contribution.

## Local setup

```bash
git clone https://github.com/anilkumarthakur60/laravel-exception-response.git
cd laravel-exception-response
composer install
```

## Quality gates

Every change must pass:

```bash
composer test          # PHPUnit
composer analyse       # PHPStan level 10 (with larastan)
composer format:check  # Pint (Laravel preset + project rules)
```

To auto-fix style: `composer format`.

## Pull request checklist

- [ ] One topic per PR (rename + feature in the same PR makes review hard)
- [ ] Tests added or updated alongside the change
- [ ] `composer test analyse format:check` all green locally
- [ ] `CHANGELOG.md` updated under `## [Unreleased]`
- [ ] Public API changes documented in `README.md`
- [ ] Breaking changes additionally documented in `UPGRADE.md`

## Branch & commit conventions

- Target the `main` branch for PRs.
- Keep commit messages descriptive (imperative mood, ~70 char subject).
- Squash-merge is the default; the PR title becomes the commit subject.

## Reporting bugs

Use the [bug report template](.github/ISSUE_TEMPLATE/bug_report.yml). Include the PHP and Laravel versions and a minimal reproduction.

## Security

Please do **not** open public issues for security problems. See [SECURITY.md](SECURITY.md).
