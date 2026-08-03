# Contributing to Akiba V2

Thanks for considering a contribution to Akiba V2. This guide explains how to open issues, submit pull requests, and keep the repository consistent.

## Language

English is the default language for public repository content:

- README and documentation
- Issue and pull request titles
- Issue and pull request summaries
- Commit messages
- Labels and milestones
- Release notes

The core team is fully Brazilian, so Portuguese is fine for quick internal discussion inside comments when it makes collaboration faster. Keep the title, summary, acceptance criteria, and final decision in English so the work remains easy to search and understand later.

## Reporting Bugs

Use the **Bug Report** template when you find unexpected behavior.

A good bug report includes:

1. A clear title, such as `Fix post loading error on the home page`.
2. A detailed description of what is happening.
3. Exact steps to reproduce the issue.
4. The expected behavior.
5. The actual behavior.
6. Environment details, such as browser, operating system, PHP/Laravel version, and Node.js version when relevant.

Example:

```markdown
Title: Fix podcast category filtering error

Description:
Filtering podcasts by category returns a 500 error.

Steps to reproduce:
1. Go to /podcasts.
2. Click the category filter.
3. Select "Drama".
4. Observe the error.

Expected behavior:
Only podcasts from the Drama category should be displayed.

Actual behavior:
The page returns a 500 Internal Server Error.
```

## Suggesting Improvements

Use the **Feature Request** template to suggest new features or improvements.

Include:

1. A clear title, such as `Add dark theme support`.
2. A short description of the feature.
3. The goal or problem being solved.
4. Expected benefits.
5. Additional context, such as screenshots, mockups, examples, or references.

## Creating Issues

Choose the template that best matches the work:

1. **Bug Report** - unexpected behavior or errors.
2. **Feature Request** - new features or improvements.
3. **Task** - general development or maintenance work.
4. **Refactor** - code quality, architecture, or performance improvements.
5. **Documentation** - documentation additions or updates.
6. **Blank issue** - only when no template fits.

## Issue Standards

Use short, descriptive titles:

- `Fix post loading error on the home page`
- `Add dark theme support`
- `Refactor post validation rules`
- `Document stream metadata endpoint`

When writing an issue manually, use clear sections:

```markdown
## Description
[What is the issue or request?]

## Goal
[Why does this matter? What problem does it solve?]

## Tasks
- [ ] Task 1
- [ ] Task 2
- [ ] Task 3

## Additional Notes
[Extra context, links, or references.]
```

Use labels consistently:

- `backend` - PHP, Laravel, or API changes
- `frontend` - Svelte, JavaScript, or CSS changes
- `database` - database, migration, or seeding changes
- `documentation` - documentation additions or improvements
- `enhancement` - new feature or improvement
- `bug` - bug report
- `refactor` - code refactoring
- `help-wanted` - looking for contributor help
- `wontfix` - will not be fixed
- `duplicate` - duplicate of another issue

## Pull Requests

When opening a pull request:

1. Link the related issue with `Closes #123` when applicable.
2. Describe what changed and why.
3. Add or update tests when relevant.
4. Update documentation when needed.
5. Follow the project's code standards.
6. Fill in the PR checklist.

Planning documentation lives in `docs/planejamento`. Update it when a pull request changes behavior, permissions, routes, modules, operational flows, business rules, architecture, or development process. If documentation is not needed, explain why in the pull request.

Example:

```markdown
## Goal
Closes #123 - Add category filtering to podcasts.

## What Changed?
- Added the category filter component.
- Added filtering logic to the podcast controller.
- Added coverage for category filtering.

## How to Test
1. Run php artisan test.
2. Open /podcasts.
3. Filter podcasts by category.

## Checklist
- [x] Tests pass locally
- [x] Documentation updated
- [x] Tested locally
```

## Code Conventions

### Backend

- Follow PSR-12.
- Use English names for variables, methods, classes, and files.
- Use type hints.
- Validate inputs with form requests where appropriate.
- Keep controllers slim and move business logic into actions or services.

```php
class PostController extends Controller
{
    public function __construct(private PostService $service) {}

    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $this->service->create($request->validated());

        return response()->json($post, 201);
    }
}
```

### Frontend

- Use PascalCase for components.
- Use camelCase for variables and functions.
- Prefer `const` over `let` when values do not change.
- Keep components small and reusable.
- Keep UI copy in English unless the product requirement says otherwise.

```svelte
<script>
  export let podcast;

  const truncate = (value, length) =>
    value.length > length ? `${value.slice(0, length)}...` : value;
</script>

<article class="podcast-card">
  <h2>{podcast.title}</h2>
  <p>{truncate(podcast.description, 100)}</p>
</article>
```

## Commits

Use clear commit messages in English:

```bash
git commit -m "feat: add podcast category filter"
git commit -m "fix: resolve category filter server error"
git commit -m "refactor: extract duplicated controller logic"
git commit -m "docs: update API documentation"
```

Avoid vague messages:

```bash
git commit -m "fix"
git commit -m "updating stuff"
git commit -m "asdfgh"
```

## Getting Help

- Open a discussion for questions and broader ideas.
- Check the documentation before opening a new issue.
- Search existing issues and pull requests to avoid duplicates.

Thanks for helping improve Akiba V2.
