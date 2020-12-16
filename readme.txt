=== Automation Rules ===
Contributors: codefarma
Donate link: http://www.codefarma.com/
Tags: rules, automation, programming
Requires at least: 4.7
Tested up to: 5.6
Requires PHP: 5.6
Stable tag: 1.2.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automation allows you to build new features on the fly and automate plugins using simple "rules".
 
== Description ==
 
Automation allows you to build new features on the fly and automate plugins using simple "rules".

Use "rules" to assign what happens on your site when certain events occur. An individual rule consists of an event, some conditions to check, and one or more actions to take. For example:

**Rule 1.**

> `Event:` **When a user logs in...**  
> `Action:` **Increment a login count for the user.**

**Rule 2.**

> `Event:` **When a user logs in...**  
> `Condition:` **If their login counter has reached a certain threshold...**  
> `Action:` **Promote the user to a new member role.**

Multiple rules can work in coordination with each other to create workflows and custom features.

A rule is the building block for creating automations, and automations are a path to create new custom site features by connecting various "automation ready" plugins and their behaviors in new ways.

The possibilities of what you can automate is only limited by your imagination (or the number of "automation ready" plugins you have installed on your site). If you want to automate features of a plugin that is not yet automation ready, that's not a problem because an expansion pack can easily be created to make it automation ready.

## Automation Bundles

Multiple "rules" can be grouped together into "automation bundles", which is a convenient way to package and share your creations with other site owners. These "automation bundles" allow anybody to create customized behaviors between automation ready plugins, and distribute those in a way that allow others to use them with minimal configuration.
 
== Installation ==
 
1. Install the plugin.
2. Click the "Rules Engine" link in the WP Admin.
3. Start a new rule by clicking the "Start A Rule" button.
 
== Frequently Asked Questions ==
 

== Screenshots ==
 

== Changelog ==

1.2.3
=====

### Fixed

- Updated MWP Framework to 1.2.11 for compat with latest Woo


1.1.4
=====

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


1.1.3
=====

### Added

- Custom actions can now be scheduled manually
- Token browser now shows mapped array keys for event args

### Changed

- Removed manual config form option for custom action arguments that do not have a config widget
- Widened the hook priority support for rule deployments

### Fixed

- Auto detect provider for ecas registered by plugins that are network enabled


1.1.2
=====

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


1.1.1
=====

### Changed

- Improved the capabilities of the string comparision core condition to handle checking against arrays

### Fixed

- Fixed broken show/hide form rows on post/comment update action config forms


1.1.0
=====

### Added

- Token browser added. Event argument selection and token replacements can now be browsed using a gui.
- Custom logs and custom log entries are now accessible through the token browser.
- Custom logs can now be customized to choose default sorting, alternate sorting, and searching columns.
- Log entries count now displays on the dashboard logs tab

### Changed

- Changed to work with MWP Framework 2.1.x

1.0.5
=====

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

1.0.4
=====

### Added

- New field for custom events to categorize them
- Providers of ECA's are now tracked in rules exports
- Custom events and custom actions now have separate management screens
- Custom actions can have rules assigned to them which comprise their core functionality
- Added multisite support which moves rules administration to the network admin and allows rules to target specific sites
 
1.0.3
=====

### Added

- Bundles can now have a menu item added to the WP Settings Menu
- Added ability to specify attachments in the email action

### Changed

- Removed foreign key references from exported rule data
- Adjusted verbage for exporting rules from 'Export ...' to 'Download ...'
- Removed the download option from the dashboard bundle panel

### Fixed

- Corrected active record class map generation output
 
1.0.2
=====

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
 
1.0.1
=====

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
 
1.0.0
=====

* First official release

