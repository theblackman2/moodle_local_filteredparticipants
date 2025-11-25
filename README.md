# Filtered Participants Plugin for Moodle

A Moodle local plugin that displays a list of course participants filtered by their assigned roles, with a custom user profile view.

## Features

- **Role-based filtering**: Display users by specific role IDs (including custom roles)
- **Context-aware role search**: Finds roles assigned at course, category, or system level
- **Custom user profile page**: Clean interface showing user info and blog access without course context
- **Pagination**: Results are paginated (50 users per page) for better performance
- **Caching**: Implements Moodle cache API to reduce database queries on repeated requests
- **Role validation**: Ensures requested role IDs exist in the system
- **Clean UI**: Displays user information (full name, email, username) in a styled table
- **Capability-based access**: Requires `local/filteredparticipants:view` capability
- **Multilingual**: Supports English and French

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

## Custom User Profile

When clicking on a user's name, you'll see a custom profile page with:

- **User picture and details**: Email, username, first/last access, location
- **View blog**: Direct link to user's blog entries (without course context requirement)
- **Full profile**: Link to Moodle's complete user profile
- **Send message**: Quick link to message the user (if messaging is enabled)
- **Back to participants**: Easy navigation back to the filtered list

## Capabilities

- **local/filteredparticipants:view** - View filtered participants (granted to managers and editing teachers by default)
- **local/filteredparticipants:manage** - Manage filtered participants (reserved for future features, granted to managers only)

## How Role Filtering Works

The plugin searches for role assignments in:
- **Course context** (roles assigned directly to the course)
- **Category context** (roles assigned to the course's category - applies to all courses in that category)
- **System context** (global role assignments - apply to entire site)

This means custom roles assigned at any level will be found correctly!

## Performance

- **Caching**: User lists are cached per course/role/page combination
- **Pagination**: Limits queries to 50 records per page
- **Optimized queries**: Uses indexed JOIN queries on `role_assignments` and `user` tables
- **Smart context search**: Efficiently searches across context hierarchy

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

## Troubleshooting

### No users displayed for custom roles

If custom roles don't show users:
1. Verify the role IDs exist: Site Administration → Users → Permissions → Define roles
2. Check role assignments are active for users in the course or parent contexts
3. Enable debugging (Site Administration → Development → Debugging) to see diagnostic messages
4. Ensure users with those roles are not deleted (`u.deleted = 0` in query)

### Empty page

If you see an empty page:
1. Clear all caches
2. Check you have the `local/filteredparticipants:view` capability
3. Verify the course ID and role IDs in the URL are correct
4. Check if role assignments exist at course, category, or system level

## License

GNU GPL v3 or later

## Author

Created 2025

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.

