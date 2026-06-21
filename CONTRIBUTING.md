# Contributing to NeuroSpend AI

First off, thank you for considering contributing to NeuroSpend AI! It's people like you that make this privacy-respecting personal finance command center a great tool for everyone.

## Getting Started

1. **Fork the repository** on GitHub.
2. **Clone your fork** locally: `git clone https://github.com/your-username/neurospend-ai.git`
3. **Set up the local environment** following the Quick Start instructions in the `README.md`.
4. **Create a new branch** for your feature or bugfix: `git checkout -b feature/your-feature-name`

## How to Contribute

### Reporting Bugs
If you find a bug, please create an issue on GitHub. Include:
- A clear and descriptive title
- Steps to reproduce the issue
- Expected vs. actual behavior
- Your operating system and environment details

### Suggesting Enhancements
We love new ideas! When proposing a feature, please explain:
- The problem your feature solves
- How you envision the feature working
- Any potential alternatives you've considered

### Submitting Pull Requests
1. Ensure your code aligns with our existing coding standards (Laravel PSR-12).
2. Write tests for any new functionality when applicable.
3. Keep your commits atomic and write descriptive commit messages.
4. Push your branch to your fork and submit a Pull Request to the `main` branch.

## Development Guidelines

- **Privacy First**: Remember that NeuroSpend AI is fundamentally built on local, zero-transmission architecture. No feature should introduce external API calls or tracking unless strictly optional and opt-in (which goes against the core philosophy).
- **Determinism**: The NOVA Intelligence engine relies on strict, predictable rules. Avoid adding non-deterministic logic without heavy consideration.
- **Styling**: We use TailwindCSS for styling. Please stick to utility classes and the defined design system.

## Code of Conduct

Please note that this project is released with a Contributor Code of Conduct. By participating in this project you agree to abide by its terms. Ensure interactions remain respectful and constructive.
