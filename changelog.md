# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [1.1.4] - 2019-01-24

### Added

- Date/Time config widget presets
- Taxonomy config widget presets
- A creator field can now be specified for rule bundles


### Changed

- Token browser launch button re-styled
- Performance improvements via new table indexes
- Email action now supports token replacements
- Recurring action schedule is now visible on scheduled actions table
- Removed 'before_setup_theme' rule event since it is unhookable by rules
- Rules Engine link appears in network admin menu for multisite installs
- Downloaded rules files now have a suffix that matches the type of rule package
- Flushing the system logs table now just truncates the table instead of iterating and deleting

### Fixed 

- Bug causing rule conditions not to be re-organizable
- Bug getting bundle url with custom parameters via code
- Bug causing condition 'compare' mode not to save
- Bug causing table on rules debug tabs to not be pageable
- Bug with line endings being present on arrays entered through input widgets
- Bug with null rule_parent_id causing rules to not be visible in tables
- Bug causing post meta crud events to not be available for use by rules

## [1.1.3] - 2018-08-17

### Added

- Custom actions can now be scheduled manually
- Token browser now shows mapped array keys for event args

### Changed

- Removed manual config form option for custom action arguments that do not have a config widget
- Widened the hook priority support for rule deployments

### Fixed

- Auto detect provider for ecas registered by plugins that are network enabled


## [1.1.2] - 2018-08-01

### Added

- Array value search condition now has setting for case insensitivity
- New 'checkbox' config preset widget
- Any class that extends `MWP\Framework\Pattern\ActiveRecord` can now automagically register a full set of ECA's with rules via static method `registerRulesECAs()`
- Token selector now available on the action scheduling unique key form input
- Added support for storing references to known objects in the class map for later loading

### Changed

- Event argument selection is now always available in condition/action config, even if no event args can be matched to 2 levels

### Fixed

- Fixed scheduled action controller not conforming to proper database schema
- Fixed custom log entries not showing after editing default sort order on log
- Fixed non-working token replacement on action scheduling unique key
- Fixed non-working scheduled action unique key management
- Fixed scheduled actions not working when operating on stored objects
- Fixed custom filters not showing on custom events page


## [1.1.1] - 2018-07-17

### Changed

- Improved the capabilities of the string comparision core condition to handle checking against arrays

### Fixed

- Fixed broken show/hide form rows on post/comment update action config forms


## [1.1.0] - 2018-07-09

### Added

- Token browser added. Event argument selection and token replacements can now be browsed using a gui.
- Custom logs and custom log entries are now accessible through the token browser.
- Custom logs can now be customized to choose default sorting, alternate sorting, and searching columns.
- Log entries count now displays on the dashboard logs tab

### Changed

- Changed to work with MWP Framework 2.1.x


## [1.0.5] - 2018-06-20

### Added

- Full exception handling for PHP7
- Token mappings for current site properties
- Added a link to get to the system log in the admin menu

### Changed

- The redirect rules action is now safeguarded against redirecting when in the admin interface
- Downloads of rules now auto name the file according to the item being downloaded
- The view template for custom logs now display their field name instead of the database column name

### Fixed

- Error caused when using the token evaluator function in custom php actions/conditions
- Base compare setting for conditions was not saving to the rule


## [1.0.4] - 2018-06-13

### Added

- New field for custom events to categorize them
- Providers of ECA's are now tracked in rules exports
- Custom events and custom actions now have separate management screens
- Custom actions can have rules assigned to them which comprise their core functionality
- Added multisite support which moves rules administration to the network admin and allows rules to target specific sites


## [1.0.3] - 2018-06-01

### Added

- Bundles can now have a menu item added to the WP Settings Menu
- Added ability to specify attachments in the email action

### Changed

- Removed foreign key references from exported rule data
- Adjusted verbage for exporting rules from 'Export ...' to 'Download ...'
- Removed the download option from the dashboard bundle panel

### Fixed

- Corrected active record class map generation output


## [1.0.2] - 2018-05-30

### Added

- Improvements to user interface
- Menu item for custom logs to manage fields
- Menu item for custom logs to flush entries
- Started tracking the rules_apps table for future use
- Added an extension to the ActiveRecord class to add the class to the rules map `addToRulesMap()`

### Fixed

- Fixed broken uninstall routine
- Fixed various php notices
- Fixed database errors caused by non-present tables on initial install

### Changed

- Removed unused dependency tracking code
- Removed search box from rules controller


## [1.0.1] - 2018-05-14

### Added

- Event: Admin Initialized
- Event: Admin Header
- Event: Admin Footer
- Event: Login Attempt Failed
- Date/time now displays by default on log entries table
- Added internal api methods to get specific arguments from hooks, logs, and bundles
- Added a `$token_value` variable which contains a function that can be used to get token values inside rule configuration custom php code.
- Added quick enable/disable labels to bundles, rules, conditions, and actions
- Added custom log field visibility options
- Added custom log retention maintenance settings

### Changed

- "Log entry created" events now recieve the log and entry as arguments
- Added css styles to suppress notice and update messages on rules admin pages
- String database column size increased from 255 to 1028


### Fixed

- Completed the incomplete 'update filter value' action


## [1.0.0] - 2018-04-28

- First public release 


## [0.9.2] - 2018-02-28

### Added
- changelog.md
- Array value form config presets

### Changed
- Updated to use MWP Application Framework 2.x

### Fixed 
- Broken tests
- Missing calls to `saveValues()` callbacks on form configs

## [0.9.1] - 2018-01-29
- (changed) The meta data update action now has additional options for how to update existing meta data
- (added) Automated tests for meta data update action

## [0.9.0] - 2018-01-26
- Initial Release
- Initial ECA List

### Events

> **System**
> - Theme Is Being Setup (Before)
> - Theme Is Being Setup (After)
> - WordPress Is Being Initialized
> - WordPress Is Loaded
> - Page Template Is Being Loaded
> - WordPress Is Shutting Down
> - Document Title Is Being Filtered
> - Email Is Being Sent

> **Users**
> - User Has Been Created
> - User Profile Has Been Updated
> - User Is Being Deleted
> - User Has Logged In
> - User Is Logging Out
> - User Meta Has Been Added
> - User Meta Has Been Updated
> - User Meta Has Been Deleted

> **Content**
> - Post Title Is Filtered
> - Post Content Is Filtered
> - Post Excerpt Is Filtered
> - Post Meta Is Filtered
> - Post Attachment Is Filtered
> - Post Is Created Or Updated
> - Post Is Trashed
> - Post Is Un-Trashed
> - Post Is Being Deleted Permanently
> - Post Meta Has Been Added
> - Post Meta Has Been Updated
> - Post Meta Has Been Deleted
> - Post Taxonomy Terms Have Been Updated
> - Comment Is Posted
> - Comment Is Edited
> - Comment Status Has Been Changed
> - Comment Is Marked As Spam
> - Comment Is Un-Marked As Spam
> - Comment Text Is Filtered
> - Comment Is Trashed
> - Comment Is Un-Trashed
> - Comment Is Being Deleted Permanently
> - Comment Meta Has Been Added
> - Comment Meta Has Been Updated
> - Comment Meta Has Been Deleted
> - Taxonomy Term Is Added
> - Taxonomy Term Is Edited
> - Taxonomy Term Is Being Deleted
> - Taxonomy Term Meta Has Been Added
> - Taxonomy Term Meta Has Been Updated
> - Taxonomy Term Meta Has Been Deleted

### Conditions

> **System**
> - Check A Truth
> - Compare Numbers
> - Compare Strings
> - Inspect An Array
> - Inspect An Object
> - Compare Dates
> - Check Data Type
> - Check For A Scheduled Action
> - Execute Custom PHP Code


### Actions

> **System**
> - Send An Email
> - Modify The Filtered Value
> - Redirect To Page
> - Display Admin Notice
> - Unschedule An Action
> - Execute Custom PHP Code
> - Update Meta Data
> - Delete Meta Data

> **Content**
> - Create A Post
> - Update A Post
> - Trash/Delete A Post
> - Create A Comment
> - Update A Comment
> - Trash/Delete A Comment
