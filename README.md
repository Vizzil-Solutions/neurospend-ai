# NeuroSpend AI

![Version 1.0.0](https://img.shields.io/badge/version-1.0.0-0C447C?style=flat-square)
![Laravel 12](https://img.shields.io/badge/Laravel-12-FAECE7?style=flat-square&logo=laravel&logoColor=993C1D)
![PHP 8.2+](https://img.shields.io/badge/PHP-8.2+-E6F1FB?style=flat-square&logo=php&logoColor=0C447C)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-E6F2FC?style=flat-square&logo=postgresql&logoColor=336791)
![Privacy-First](https://img.shields.io/badge/Privacy--First-EEEDFE?style=flat-square)
![MIT License](https://img.shields.io/badge/License-MIT-FAEEDA?style=flat-square)

A privacy-respecting personal finance command center. Calculates a deterministic daily spending allowance, simulates debt payoff strategies, and delivers actionable diagnostics — entirely on your device, with no financial data ever leaving your machine.

---

## Core Modules

### 📅 Survival Command
Runway optimizer. Distributes liquid cash across remaining cycle days after reserving upcoming bills — producing a hard daily spending ceiling.

### 📈 Debt Command
Interactive Snowball and Avalanche simulators. Plots full payoff timelines and total interest saved with month-by-month amortization.

### 🧠 NOVA Intelligence
Local rule-based diagnostics engine. Flags toxic debt, category drain, and unsafe DTI ratios — no cloud API, no hallucinations, fully deterministic.

> **Zero-transmission architecture.** NOVA's heuristics engine (`LocalAIEngine.php`) evaluates all diagnostics in-process against local data. No financial ledger, balance, or transaction is transmitted to any external service. Advice is generated from a structured rules dataset sourced from Warren & Tyagi, Kahneman & Tversky, Dave Ramsey, Thaler & Sunstein, and standard underwriting thresholds (Fannie Mae, FHA).

---

## Tech Stack

**Backend**
- Server: Laravel 11 (App Router)
- Database: PostgreSQL
- Security: Session-based auth
- Views: Blade templates

**Frontend**
- Styling: TailwindCSS
- Charts: Chart.js
- Icons: Lucide icons
- UI: Responsive dashboard

---

## Key Files
- **Core engine**: `app/Services/LocalAIEngine.php`
- **Aggregations**: `app/Services/FinanceService.php`
- **Routing**: `routes/web.php`
- **Layout**: `resources/views/layouts/app.blade.php`
- **Intelligence**: `nova_intelligence_data.json`

---

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/Vizzil-Solutions/neurospend-ai/tags).

**Current Version:** `v1.0.0`
- Initial stable release featuring the complete suite of Survival Command, Debt Command, and NOVA Intelligence.

This project adheres to the [Conventional Commits](https://www.conventionalcommits.org/) specification. We use [Standard Version](https://github.com/conventional-changelog/standard-version) for automated changelog generation and version bumping.

---

## Quick Start

```bash
$ git clone https://github.com/Vizzil-Solutions/neurospend-ai.git
$ cd neurospend-ai/laravel && composer install
$ cp .env.example .env && php artisan key:generate
$ php artisan migrate && npm install && npm run build
$ php artisan serve
```

---

Built on behavioral economics research. Requires PHP 8.2+, Composer, and Node. No external AI API keys required — NOVA runs entirely offline.
