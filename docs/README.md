# F-Shop Documentation

This directory contains the documentation for the F-Shop WordPress plugin.

## Documentation Structure

```
docs/
├── index.md                    # Main documentation homepage
├── installation.md             # Installation guide (to be created)
├── developer-guide.md          # Developer guide (to be created)
├── changelog.md               # Changelog (to be created)
└── stock-status/              # Stock Status System documentation
    ├── overview.md            # System overview
    ├── getting-started.md     # Getting started guide
    ├── api-reference.md       # API reference
    ├── hooks-filters.md       # Hooks and filters documentation
    └── custom-statuses.md     # Custom statuses guide
```

## Building Documentation Locally

To build and preview the documentation locally:

1. **Install MkDocs and Material theme:**
   ```bash
   pip install mkdocs mkdocs-material
   ```

2. **Serve documentation locally:**
   ```bash
   mkdocs serve
   ```

3. **Open your browser to:** http://localhost:8000

## Deployment

Documentation is automatically deployed to GitHub Pages when changes are pushed to the main branch.

The deployment workflow is configured in `.github/workflows/deploy-docs.yml`.

## Contributing to Documentation

1. Make changes to the Markdown files in the `docs/` directory
2. Test locally with `mkdocs serve`
3. Commit and push changes to the main branch
4. Documentation will be automatically deployed

## Documentation Style Guide

- Use clear, concise language
- Include code examples where relevant
- Use proper Markdown formatting
- Maintain consistent terminology
- Update navigation in `mkdocs.yml` when adding new pages

## Need Help?

- Check the [official documentation](https://karakushan.github.io/f-shop/)
- Open an issue on GitHub
- Contact the development team