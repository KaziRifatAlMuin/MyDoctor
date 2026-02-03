# Git Workflow Guide

This guide defines naming conventions, branching conventions, and the step-by-step commands to create branches, commit changes, update branches, and merge branches.

## Naming Conventions

### Branch Names
Use lowercase with hyphens and a type prefix:

```
<type>/<short-description>
```

Allowed types:
- `feature/` – new features
- `fix/` – bug fixes
- `chore/` – maintenance/infra tasks
- `docs/` – documentation only
- `refactor/` – code refactors
- `test/` – adding/updating tests

Examples:
- `feature/patient-profile-form`
- `fix/login-redirect`
- `docs/api-readme`

### Commit Messages
Use the Conventional Commits format:

```
<type>(optional-scope): <short summary>
```

Allowed types: `feat`, `fix`, `docs`, `chore`, `refactor`, `test`, `perf`, `build`, `ci`

Examples:
- `feat(auth): add password reset flow`
- `fix(api): handle empty response`
- `docs: update setup guide`

### File/Folder Names
- Use `kebab-case` for folders and files where possible.
- Use `PascalCase` for PHP classes.
- Keep names short and descriptive.

## Branching Convention

- `main` is always stable and production-ready.
- New work happens in short-lived branches.
- Branch from `main` unless instructed otherwise.
- Keep branches small and focused to a single task.

## Step-by-Step Git Commands

### 1) Create a New Branch

```
git checkout main
git pull origin main
git checkout -b feature/short-description
```

### 2) Make Changes and Commit

```
git status
git add .
git commit -m "feat(scope): short summary"
```

### 3) Update Your Branch With Latest `main`

**Option A: Merge `main` into your branch**

```
git checkout main
git pull origin main
git checkout feature/short-description
git merge main
```

**Option B: Rebase onto `main` (preferred for clean history)**

```
git checkout main
git pull origin main
git checkout feature/short-description
git rebase main
```

If there are conflicts, resolve them, then:

```
git add .
git rebase --continue
```

### 4) Push Your Branch

```
git push -u origin feature/short-description
```

### 5) Merge a Branch Into `main`

**If using Pull Requests (recommended):**
1. Open a PR from `feature/short-description` into `main`.
2. Ensure CI checks pass and code review is completed.
3. Merge using "Squash and merge" unless instructed otherwise.

**If merging locally:**

```
git checkout main
git pull origin main
git merge feature/short-description
git push origin main
```

### 6) Delete a Branch After Merge

```
git branch -d feature/short-description
git push origin --delete feature/short-description
```

## Optional: Hotfix Branch

For urgent production fixes:

```
git checkout main
git pull origin main
git checkout -b fix/short-description
```

Then follow the commit, update, and merge steps above.
