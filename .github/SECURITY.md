# Security Policy

## Supported Versions

The following versions of this project are currently being supported with security updates.

| Version | Supported          |
|---------|--------------------|
| 1.1.1   | :white_check_mark: |
| 2.0.0   | :white_check_mark: |

## Reporting a Vulnerability

If you believe you have found a security vulnerability, please do not create a public issue or post it publicly anywhere
else. You can responsibly disclose the problem directly via GitHubs security reporting feature via email to mail at
fabiantodt.at

We will review the vulnerability report and determine the best course of action within 48 hours (on workdays).
Some additional that can be of help to us:

- Your name and affiliation (if any).
- A description of the technical details of the vulnerability. It is very important to let us know how we can reproduce
  your findings.
- An explanation who can exploit this vulnerability, and what they gain when doing so -- write an attack scenario. This
  will help us evaluate your report quickly, especially if the issue is complex.
- Whether this vulnerability is public or known to third parties. If it is, please provide details.

If you believe that an existing (public) issue is security-related, please send an email to mail at fabiantodt.at. The
email should include the issue ID and a short description of why it should be handled according to this security policy.

## Disclosing a Vulnerability

Once an issue is reported, we uses the following disclosure process:

- Wherever possible, fixes are prepared for the latest supported versions.
- Patch releases are published for all fixed released versions and the advisory is published.
- Release notes and our CHANGELOG.md will include a `Security` section with a link to the advisory.

## Known Vulnerabilities

Past security advisories, if any, are listed below.

| Advisory Number | Type | Versions affected | Reported by | Additional Information                                                      |
|-----------------|------|:-----------------:|-------------|-----------------------------------------------------------------------------|
| -               | -    |      <1.1.1       | WPScan      | - The secret key could be exposed via REST API for passwort protected posts |
