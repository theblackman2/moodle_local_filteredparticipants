# Changelog

All notable changes to the Filtered Participants plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-11-25

### Added
- Custom user profile page (`user_profile.php`) with:
  - User picture and basic information display
  - Direct link to user blog without course context requirement
  - Link to full Moodle user profile
  - Send message button (when messaging is enabled)
  - Back to participants list navigation
- Language strings for custom profile page features
- French translations for all new features

### Changed
- User name links now point to custom profile page instead of default Moodle profile
- Version bumped to v1.1.0 (feature release)

## [1.0.3] - 2025-11-25

### Fixed
- **Critical**: Role filtering now searches in parent contexts (system, category) not just course context
  - Custom roles assigned at category or system level are now found correctly
  - Uses `$context->get_parent_contexts(true)` to include all relevant contexts
  - Fixes issue where category-assigned custom roles showed empty results

### Changed
- SQL queries updated to use `IN` clause for multiple context IDs
- Cache key remains per-course to maintain performance

## [1.0.2] - 2025-11-25

### Fixed
- Role validation now checks role existence in database instead of assignability
  - Removed `get_assignable_roles()` check that filtered out roles based on permissions
  - Now uses direct database query on `mdl_role` table
  - Custom roles work regardless of current user's assignment permissions

### Changed
- Error message changed from "no users" to "no roles selected" for invalid role IDs

## [1.0.1] - 2025-11-25

### Added
- French (fr) language pack with complete translations
- Language strings for all plugin features

### Changed
- Code formatting improvements (PSR-2 style indentation)

## [1.0.0] - 2025-11-25

### Added
- Initial release with comprehensive feature set
- Role-based filtering of course participants
- Pagination (50 users per page) with Moodle's paging bar
- Caching using Moodle Cache API for improved performance
- Additional capability: `local/filteredparticipants:manage`
- PHPUnit test suite covering:
  - Role filtering functionality
  - Pagination logic
  - Cache operations
- Comprehensive README documentation
- Error handling using Moodle's `print_error()` function
- User-friendly notifications via `$OUTPUT->notification()`

### Security
- Capability checking via `require_capability()`
- Parameter sanitization using `required_param()` and `optional_param()`
- SQL injection protection with `get_in_or_equal()`
- Output escaping with `s()` function

### Performance
- Application-level caching with static acceleration
- Optimized SQL queries with proper indexing
- Pagination to limit memory usage on large result sets

---

## Version History Summary

- **v1.1.0**: Custom user profile page with blog access
- **v1.0.3**: Parent context role search (critical fix for custom roles)
- **v1.0.2**: Database-based role validation (fixes custom role filtering)
- **v1.0.1**: French language support
- **v1.0.0**: Initial feature-complete release

## Upgrade Notes

### From 1.0.x to 1.1.0
- No database changes required
- Clear cache after upgrade
- New language strings added (auto-loaded)
- New file: `user_profile.php` provides custom profile view

### From any version
After upgrading:
1. Visit Site Administration → Notifications
2. Clear all caches: Site Administration → Development → Purge all caches
3. Test with various role types (custom and default)
