=== Automation Rules ===
Contributors: codefarma
Donate link: http://www.codefarma.com/
Tags: rules, automation, programming
Requires at least: 4.7
Tested up to: 4.9.6
Requires PHP: 5.6
Stable tag: 1.0.4
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
 
= 1.0.4 =

### Added

- New field for custom events to categorize them
- Providers of ECA's are now tracked in rules exports
- Custom events and custom actions now have separate management screens
- Custom actions can have rules assigned to them which comprise their core functionality
- Added multisite support which moves rules administration to the network admin and allows rules to target specific sites
 
= 1.0.3 =

### Added

- Bundles can now have a menu item added to the WP Settings Menu
- Added ability to specify attachments in the email action

### Changed

- Removed foreign key references from exported rule data
- Adjusted verbage for exporting rules from 'Export ...' to 'Download ...'
- Removed the download option from the dashboard bundle panel

### Fixed

- Corrected active record class map generation output
 
= 1.0.2 =

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
 
= 1.0.1 =

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
 
= 1.0.0 =

* First official release

