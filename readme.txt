=== Automation Rules ===
Contributors: codefarma
Donate link: http://www.codefarma.com/
Tags: rules, automation, programming
Requires at least: 4.7
Tested up to: 4.9.5
Requires PHP: 5.6
Stable tag: 1.0.0
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
 
= 1.0.1 =

### Added

- Event: Admin Initialized
- Event: Admin Header
- Event: Admin Footer
- Event: Login Attempt Failed
- Date/time now displays by default on log entries table
- Added internal api methods to get specific arguments from hooks, logs, and bundles
- Added a `$token_value` variable which contains a function that can be used to get token values inside rule configuration custom php code.

### Changed

- "Log entry created" events now recieve the log and entry as arguments
- Added css styles to suppress notice and update messages on rules admin pages

### Fixed

- Completed the incomplete 'update filter value' action
 
= 1.0.0 =

* First official release

