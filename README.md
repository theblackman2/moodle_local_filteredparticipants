# Filtered Participants Plugin for Moodle

A Moodle local plugin that displays a list of course participants filtered by their assigned roles.

## Features

- **Role-based filtering**: Display users by specific role IDs (e.g., students, teachers)
- **Pagination**: Results are paginated (50 users per page) for better performance
- **Caching**: Implements Moodle cache API to reduce database queries on repeated requests
- **Role validation**: Ensures requested role IDs are valid within the course context
- **Clean UI**: Displays user information (full name, email, username) in a sortable table
- **Capability-based access**: Requires `local/filteredparticipants:view` capability

## Requirements

- Moodle 3.9 or higher (requires >= 2020061500)

## Installation

1. Copy the plugin directory to `/path/to/moodle/local/filteredparticipants`
2. Visit Site Administration → Notifications to complete the installation
3. Clear all caches via Site Administration → Development → Purge all caches

## Usage

### URL Format

```
/local/filteredparticipants/index.php?id=COURSEID&roles=ROLEID1,ROLEID2
```

### Parameters

- **id** (required): The course ID
- **roles** (required): Comma-separated list of role IDs to filter by
- **page** (optional): Page number for pagination (default: 0)

### Example

To view all students (role ID 5) and teaching assistants (role ID 4) in course ID 10:

```
/local/filteredparticipants/index.php?id=10&roles=5,4
```

### Finding Role IDs

To find role IDs in your Moodle installation:
1. Go to Site Administration → Users → Permissions → Define roles
2. Click on a role to view/edit it
3. The role ID is in the URL: `/admin/roles/define.php?action=view&roleid=X`

## Capabilities

- **local/filteredparticipants:view** - View filtered participants (granted to managers and editing teachers by default)
- **local/filteredparticipants:manage** - Manage filtered participants (reserved for future features, granted to managers only)

## Performance

- **Caching**: User lists are cached per course/role/page combination
- **Pagination**: Limits queries to 50 records per page
- **Optimized queries**: Uses indexed JOIN queries on `role_assignments` and `user` tables

## Testing

The plugin includes PHPUnit tests for:
- Role filtering functionality
- Pagination logic
- Cache operations

To run tests:

```bash
cd /path/to/moodle
php vendor/bin/phpunit --testsuite local_filteredparticipants
```

Or run all tests for the plugin:

```bash
php admin/tool/phpunit/cli/util.php --buildcomponentconfigs
vendor/bin/phpunit --group local_filteredparticipants
```

## Development

### Clearing Cache

After making changes, purge the plugin cache:

```php
// In Moodle admin/cli/purge_caches.php or via UI
cache_helper::purge_by_definition('local_filteredparticipants', 'userlist');
```

### Code Standards

This plugin follows [Moodle coding style guidelines](https://moodledev.io/general/development/policies/codingstyle).

## License

GNU GPL v3 or later

## Author

Created 2025

## Changelog

### Version 2025112500
- Initial release with role filtering
- Added pagination support (50 users per page)
- Implemented caching using Moodle cache API
- Role validation against course context
- Improved error handling with Moodle-style error pages
- PHPUnit test coverage
