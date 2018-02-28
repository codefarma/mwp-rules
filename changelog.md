# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
