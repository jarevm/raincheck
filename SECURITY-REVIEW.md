# Security Review

## Scope

This review covered the PHP website's SQL injection and related input-handling,
authentication, upload, session, XSS, and CSRF risks. The admin access code was
excluded as requested.

## Summary

One medium-severity issue was identified: missing CSRF protection for
administrator state-changing actions. No SQL injection finding was reported
within the review scope; this is not a guarantee that the application is free
of SQL injection or other vulnerabilities.

The admin actions are gated by an administrator check, which helps prevent
unauthorized users from invoking them directly. However, authorization alone
does not establish that a request originated from the administrator's own
intent.

## Findings

| # | Severity | File | Lines | Vulnerability | Confidence |
|---|----------|------|-------|---------------|------------|
| 1 | MEDIUM | `admin.php`, `config.php` | `admin.php:6-20, 34-39`; `config.php:3` | Administrator actions that verify, hide, or delete reports/users accept POST requests without a CSRF token or origin check. A malicious site could potentially cause a signed-in administrator's browser to submit one of these requests. The session cookie's SameSite policy is not explicitly configured. | 8/10 |

### Recommendation

Generate a per-session CSRF token, include it in each state-changing admin form,
and verify it before processing the action. Also configure the session cookie
with an appropriate SameSite policy. Keep the existing administrator
authorization check; CSRF protection complements it rather than replacing it.
