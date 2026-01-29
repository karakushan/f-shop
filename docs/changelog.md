# Changelog

All notable changes to the F-Shop plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.5.0] - 2026-01-29

### Added
- **Stock Status Management System** - Comprehensive product availability management
  - New `FS_Stock_Status` class for managing stock statuses
  - Four default statuses: In Stock, Out of Stock, On Order, Expected
  - API functions for status management (`fs_get_stock_status`, `fs_set_stock_status`, etc.)
  - Admin interface for setting product stock statuses
  - Integration with existing `fs_in_stock()` function for backward compatibility
- **Extensibility Framework**
  - `fs_stock_statuses` filter for adding custom statuses
  - `fs_get_stock_status` filter for modifying status retrieval
  - `fs_set_stock_status` filter for customizing status setting
  - `fs_stock_status_class` filter for CSS class customization
- **Documentation System**
  - Complete MkDocs documentation with Material theme
  - GitHub Actions workflow for automatic documentation deployment
  - Comprehensive guides for users and developers
  - API reference and code examples

### Changed
- Enhanced `fs_in_stock()` function to use new stock status system
- Improved product availability checking logic
- Updated admin product editing interface
- Refactored stock-related functions for better organization

### Fixed
- Resolved issues with stock status display in various contexts
- Fixed edge cases in stock quantity calculations
- Improved error handling in stock status operations

## [1.4.1] - 2025-12-15

### Added
- Minor feature enhancements
- Additional payment gateway integrations

### Fixed
- Bug fixes for cart functionality
- Performance improvements
- Security patches

## [1.4.0] - 2025-11-01

### Added
- New payment methods support
- Enhanced shipping options
- Improved admin interface

### Changed
- Updated database schema
- Modified API endpoints
- Enhanced security measures

### Removed
- Deprecated legacy functions
- Obsolete template files

## [1.3.5] - 2025-09-20

### Fixed
- Critical security vulnerability patch
- Cart calculation errors
- Checkout process improvements

## [1.3.4] - 2025-08-10

### Added
- Performance optimizations
- New shortcode options
- Enhanced mobile responsiveness

### Fixed
- Various bug fixes
- Compatibility improvements
- Minor UI adjustments

## [1.3.3] - 2025-07-05

### Added
- Multi-currency support
- Tax calculation improvements
- New email templates

### Changed
- Updated dependencies
- Improved code quality
- Enhanced documentation

## [1.3.2] - 2025-06-01

### Fixed
- Database migration issues
- User permission problems
- Template rendering bugs

## [1.3.1] - 2025-05-15

### Added
- New widget options
- Enhanced reporting features
- Additional customization options

### Fixed
- Minor bug fixes
- Performance improvements
- Compatibility updates

## [1.3.0] - 2025-04-01

### Added
- Major feature release
- New product types support
- Advanced inventory management
- Enhanced analytics dashboard

### Changed
- Complete UI redesign
- Improved user experience
- Updated code architecture

## [1.2.8] - 2025-03-10

### Fixed
- Security patches
- Performance optimizations
- Bug fixes

## [1.2.7] - 2025-02-20

### Added
- New integration options
- Enhanced customization features

### Fixed
- Various minor issues
- Compatibility fixes

## [1.2.6] - 2025-01-25

### Fixed
- Critical bug fixes
- Security updates
- Performance improvements

## [1.2.5] - 2024-12-15

### Added
- New template options
- Enhanced mobile support

### Fixed
- Minor bug fixes
- UI improvements

## [1.2.4] - 2024-11-10

### Fixed
- Security vulnerability patches
- Performance optimizations
- Compatibility fixes

## [1.2.3] - 2024-10-20

### Added
- New shortcode features
- Enhanced admin capabilities

### Fixed
- Various bug fixes
- Minor improvements

## [1.2.2] - 2024-09-25

### Fixed
- Critical security updates
- Bug fixes
- Performance improvements

## [1.2.1] - 2024-08-30

### Added
- Minor feature additions
- Enhancement improvements

### Fixed
- Bug fixes
- Minor issues resolved

## [1.2.0] - 2024-08-01

### Added
- Major feature release
- New functionality
- Enhanced capabilities

### Changed
- Significant improvements
- Updated features

### Fixed
- Various issues resolved
- Performance enhancements

## [1.1.5] - 2024-07-15

### Fixed
- Bug fixes
- Security patches
- Performance improvements

## [1.1.4] - 2024-06-20

### Added
- New features
- Enhancements

### Fixed
- Bug resolutions
- Minor fixes

## [1.1.3] - 2024-05-25

### Fixed
- Security updates
- Bug fixes
- Performance optimizations

## [1.1.2] - 2024-04-30

### Added
- Feature enhancements
- Improvements

### Fixed
- Issue resolutions
- Minor fixes

## [1.1.1] - 2024-04-01

### Fixed
- Critical bug fixes
- Security patches
- Performance improvements

## [1.1.0] - 2024-03-15

### Added
- Major feature additions
- New functionality
- Enhanced capabilities

### Changed
- Significant improvements
- Updates and modifications

## [1.0.0] - 2024-01-01

### Added
- Initial release
- Core functionality
- Basic features

---

## Versioning Scheme

We use Semantic Versioning (SemVer) for version numbering:

- **MAJOR** version when we make incompatible API changes
- **MINOR** version when we add functionality in a backwards compatible manner
- **PATCH** version when we make backwards compatible bug fixes

## Release Process

1. Update changelog with changes since last release
2. Bump version number according to SemVer
3. Create release tag
4. Package and distribute
5. Update documentation
6. Announce release

## Reporting Issues

Please report issues and feature requests on our [GitHub Issues](https://github.com/karakushan/f-shop/issues) page.

## Contributing

We welcome contributions! Please see our [Developer Guide](developer-guide.md) for information on how to contribute to the project.