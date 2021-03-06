<?php
return <<<'JSON'
{
    "framework_version": "2.2.8",
    "framework_bundled": true,
    "tables": [
        {
            "name": "rules_rules",
            "columns": {
                "rule_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "rule_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "rule_uuid": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 25,
                    "name": "rule_uuid",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_title": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "rule_title",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_weight": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "rule_weight",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_enabled": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "1",
                    "length": 1,
                    "name": "rule_enabled",
                    "type": "TINYINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_parent_id": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 20,
                    "name": "rule_parent_id",
                    "type": "BIGINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_event_type": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 15,
                    "name": "rule_event_type",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_event_hook": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "rule_event_hook",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_data": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "rule_data",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_event_provider": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "rule_event_provider",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_priority": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "10",
                    "length": 11,
                    "name": "rule_priority",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_base_compare": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "and",
                    "length": 0,
                    "name": "rule_base_compare",
                    "type": "ENUM",
                    "unsigned": false,
                    "values": [
                        "and",
                        "or"
                    ],
                    "zerofill": false
                },
                "rule_debug": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 1,
                    "name": "rule_debug",
                    "type": "TINYINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_enable_recursion": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 1,
                    "name": "rule_enable_recursion",
                    "type": "TINYINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_recursion_limit": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "1",
                    "length": 11,
                    "name": "rule_recursion_limit",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_imported": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "rule_imported",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_bundle_id": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "rule_bundle_id",
                    "type": "BIGINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_custom_internal": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 1,
                    "name": "rule_custom_internal",
                    "type": "TINYINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_sites": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 2048,
                    "name": "rule_sites",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_documentation": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "rule_documentation",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_create_date": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 11,
                    "name": "rule_create_date",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_system_user": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 25,
                    "name": "rule_system_user",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_description": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "rule_description",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_modified_date": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 11,
                    "name": "rule_modified_date",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                }
            },
            "indexes": {
                "PRIMARY": {
                    "type": "primary",
                    "name": "PRIMARY",
                    "length": [
                        null
                    ],
                    "columns": [
                        "rule_id"
                    ]
                },
                "rule_uuid": {
                    "type": "key",
                    "name": "rule_uuid",
                    "length": [
                        null
                    ],
                    "columns": [
                        "rule_uuid"
                    ]
                },
                "rule_bundle_id": {
                    "type": "key",
                    "name": "rule_bundle_id",
                    "length": [
                        null
                    ],
                    "columns": [
                        "rule_bundle_id"
                    ]
                },
                "rule_parent_id": {
                    "type": "key",
                    "name": "rule_parent_id",
                    "length": [
                        null
                    ],
                    "columns": [
                        "rule_parent_id"
                    ]
                },
                "rule_event": {
                    "type": "key",
                    "name": "rule_event",
                    "length": [
                        191,
                        null
                    ],
                    "columns": [
                        "rule_event_hook",
                        "rule_event_type"
                    ]
                },
                "rule_custom_internal": {
                    "type": "key",
                    "name": "rule_custom_internal",
                    "length": [
                        null
                    ],
                    "columns": [
                        "rule_custom_internal"
                    ]
                },
                "rule_enabled": {
                    "type": "key",
                    "name": "rule_enabled",
                    "length": [
                        null
                    ],
                    "columns": [
                        "rule_enabled"
                    ]
                }
            }
        },
        {
            "name": "rules_conditions",
            "columns": {
                "condition_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "condition_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "condition_uuid": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 25,
                    "name": "condition_uuid",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "condition_title": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "condition_title",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "condition_weight": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "condition_weight",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "condition_parent_id": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "condition_parent_id",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "condition_rule_id": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "condition_rule_id",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "condition_key": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "condition_key",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "condition_data": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "condition_data",
                    "type": "MEDIUMTEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "condition_provider": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "condition_provider",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "condition_enabled": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "1",
                    "length": 1,
                    "name": "condition_enabled",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "condition_group_compare": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "and",
                    "length": 16,
                    "name": "condition_group_compare",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "condition_not": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 1,
                    "name": "condition_not",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "condition_imported": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "condition_imported",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                }
            },
            "indexes": {
                "PRIMARY": {
                    "type": "primary",
                    "name": "PRIMARY",
                    "length": [
                        null
                    ],
                    "columns": [
                        "condition_id"
                    ]
                },
                "condition_uuid": {
                    "type": "key",
                    "name": "condition_uuid",
                    "length": [
                        null
                    ],
                    "columns": [
                        "condition_uuid"
                    ]
                },
                "condition_rule_id": {
                    "type": "key",
                    "name": "condition_rule_id",
                    "length": [
                        null
                    ],
                    "columns": [
                        "condition_rule_id"
                    ]
                },
                "condition_parent_id": {
                    "type": "key",
                    "name": "condition_parent_id",
                    "length": [
                        null
                    ],
                    "columns": [
                        "condition_parent_id"
                    ]
                }
            }
        },
        {
            "name": "rules_actions",
            "columns": {
                "action_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "action_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "action_uuid": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 25,
                    "name": "action_uuid",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_title": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "action_title",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_weight": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "action_weight",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_rule_id": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "action_rule_id",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_key": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "action_key",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_data": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "action_data",
                    "type": "MEDIUMTEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_provider": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "action_provider",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_description": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "action_description",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_enabled": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "1",
                    "length": 1,
                    "name": "action_enabled",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_schedule_mode": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 1,
                    "name": "action_schedule_mode",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_schedule_minutes": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 4,
                    "name": "action_schedule_minutes",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_schedule_hours": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 4,
                    "name": "action_schedule_hours",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_schedule_days": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 4,
                    "name": "action_schedule_days",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_schedule_months": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 4,
                    "name": "action_schedule_months",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_schedule_date": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "action_schedule_date",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_schedule_customcode": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "action_schedule_customcode",
                    "type": "MEDIUMTEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_schedule_key": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 1028,
                    "name": "action_schedule_key",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_else": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 1,
                    "name": "action_else",
                    "type": "TINYINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_imported": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "action_imported",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                }
            },
            "indexes": {
                "PRIMARY": {
                    "type": "primary",
                    "name": "PRIMARY",
                    "length": [
                        null
                    ],
                    "columns": [
                        "action_id"
                    ]
                },
                "action_uuid": {
                    "type": "key",
                    "name": "action_uuid",
                    "length": [
                        null
                    ],
                    "columns": [
                        "action_uuid"
                    ]
                },
                "action_rule_id": {
                    "type": "key",
                    "name": "action_rule_id",
                    "length": [
                        null
                    ],
                    "columns": [
                        "action_rule_id"
                    ]
                }
            }
        },
        {
            "name": "rules_arguments",
            "columns": {
                "argument_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "argument_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "argument_uuid": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 25,
                    "name": "argument_uuid",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "argument_title": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "argument_title",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "argument_type": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 56,
                    "name": "argument_type",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "argument_class": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "argument_class",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "argument_required": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "1",
                    "length": 1,
                    "name": "argument_required",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "argument_weight": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "argument_weight",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "argument_description": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 1028,
                    "name": "argument_description",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "argument_varname": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 56,
                    "name": "argument_varname",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "argument_parent_id": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "argument_parent_id",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "argument_parent_type": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 56,
                    "name": "argument_parent_type",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "argument_widget": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 56,
                    "name": "argument_widget",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "argument_data": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "argument_data",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "argument_imported": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "argument_imported",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                }
            },
            "indexes": {
                "PRIMARY": {
                    "type": "primary",
                    "name": "PRIMARY",
                    "length": [
                        null
                    ],
                    "columns": [
                        "argument_id"
                    ]
                },
                "argument_parent": {
                    "type": "key",
                    "name": "argument_parent",
                    "length": [
                        null,
                        null
                    ],
                    "columns": [
                        "argument_parent_id",
                        "argument_parent_type"
                    ]
                }
            }
        },
        {
            "name": "rules_hooks",
            "columns": {
                "hook_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "hook_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "hook_uuid": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 25,
                    "name": "hook_uuid",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_title": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "hook_title",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_weight": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "hook_weight",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_description": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 2048,
                    "name": "hook_description",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_key": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "hook_key",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_enable_api": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 1,
                    "name": "hook_enable_api",
                    "type": "TINYINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_api_methods": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 32,
                    "name": "hook_api_methods",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_type": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 12,
                    "name": "hook_type",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_hook": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 1028,
                    "name": "hook_hook",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_category": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "hook_category",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "hook_imported": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "hook_imported",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                }
            },
            "indexes": {
                "PRIMARY": {
                    "type": "primary",
                    "name": "PRIMARY",
                    "length": [
                        null
                    ],
                    "columns": [
                        "hook_id"
                    ]
                },
                "custom_action_type": {
                    "type": "key",
                    "name": "custom_action_type",
                    "length": [
                        null
                    ],
                    "columns": [
                        "hook_type"
                    ]
                },
                "custom_action_hook": {
                    "type": "key",
                    "name": "custom_action_hook",
                    "length": [
                        191
                    ],
                    "columns": [
                        "hook_hook"
                    ]
                }
            }
        },
        {
            "name": "rules_logs",
            "columns": {
                "id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "event_type": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "event_type",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "event_hook": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "event_hook",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "result": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "result",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "message": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "message",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "time": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "time",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "thread": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "thread",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_id": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "rule_id",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "op_id": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "op_id",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "type": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 56,
                    "name": "type",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "parent": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "parent",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_parent": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "rule_parent",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "error": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 4,
                    "name": "error",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                }
            },
            "indexes": {
                "PRIMARY": {
                    "type": "primary",
                    "name": "PRIMARY",
                    "length": [
                        null
                    ],
                    "columns": [
                        "id"
                    ]
                },
                "thread": {
                    "type": "key",
                    "name": "thread",
                    "length": [
                        191
                    ],
                    "columns": [
                        "thread"
                    ]
                },
                "rule_id": {
                    "type": "key",
                    "name": "rule_id",
                    "length": [
                        null
                    ],
                    "columns": [
                        "rule_id"
                    ]
                },
                "time": {
                    "type": "key",
                    "name": "time",
                    "length": [
                        null
                    ],
                    "columns": [
                        "time"
                    ]
                }
            }
        },
        {
            "name": "rules_apps",
            "columns": {
                "app_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "app_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "app_uuid": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 25,
                    "name": "app_uuid",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "app_title": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "app_title",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "app_weight": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "app_weight",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "app_enabled": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "1",
                    "length": 1,
                    "name": "app_enabled",
                    "type": "TINYINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "app_description": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "app_description",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "app_creator": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "app_creator",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "app_imported": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "app_imported",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "app_version": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "0.0.0",
                    "length": 56,
                    "name": "app_version",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "app_data": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "app_data",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "app_sites": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 2048,
                    "name": "app_sites",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "app_documentation": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "app_documentation",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                }
            },
            "indexes": {
                "PRIMARY": {
                    "type": "primary",
                    "name": "PRIMARY",
                    "length": [
                        null
                    ],
                    "columns": [
                        "app_id"
                    ]
                },
                "app_uuid": {
                    "type": "key",
                    "name": "app_uuid",
                    "length": [
                        null
                    ],
                    "columns": [
                        "app_uuid"
                    ]
                }
            }
        },
        {
            "name": "rules_bundles",
            "columns": {
                "bundle_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "bundle_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "bundle_uuid": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 25,
                    "name": "bundle_uuid",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "bundle_title": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "bundle_title",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "bundle_weight": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "bundle_weight",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "bundle_enabled": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "1",
                    "length": 1,
                    "name": "bundle_enabled",
                    "type": "TINYINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "bundle_description": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "bundle_description",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "bundle_imported": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "bundle_imported",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "bundle_app_id": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 20,
                    "name": "bundle_app_id",
                    "type": "BIGINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "bundle_add_menu": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 1,
                    "name": "bundle_add_menu",
                    "type": "TINYINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "bundle_data": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "bundle_data",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "bundle_sites": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 2048,
                    "name": "bundle_sites",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "bundle_creator": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "bundle_creator",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "bundle_documentation": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "bundle_documentation",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                }
            },
            "indexes": {
                "PRIMARY": {
                    "type": "primary",
                    "name": "PRIMARY",
                    "length": [
                        null
                    ],
                    "columns": [
                        "bundle_id"
                    ]
                },
                "bundle_uuid": {
                    "type": "key",
                    "name": "bundle_uuid",
                    "length": [
                        null
                    ],
                    "columns": [
                        "bundle_uuid"
                    ]
                },
                "bundle_app_id": {
                    "type": "key",
                    "name": "bundle_app_id",
                    "length": [
                        null
                    ],
                    "columns": [
                        "bundle_app_id"
                    ]
                }
            }
        },
        {
            "name": "rules_custom_logs",
            "columns": {
                "custom_log_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "custom_log_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_uuid": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 25,
                    "name": "custom_log_uuid",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_title": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 56,
                    "name": "custom_log_title",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_weight": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 5,
                    "name": "custom_log_weight",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_description": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "custom_log_description",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_enabled": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "1",
                    "length": 1,
                    "name": "custom_log_enabled",
                    "type": "TINYINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_key": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "custom_log_key",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_class": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "",
                    "length": 256,
                    "name": "custom_log_class",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_max_logs": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "custom_log_max_logs",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_entity_max": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "custom_log_entity_max",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_max_age": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "custom_log_max_age",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_limit": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "25",
                    "length": 5,
                    "name": "custom_log_limit",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_display_empty": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 1,
                    "name": "custom_log_display_empty",
                    "type": "TINYINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_sortby": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "id",
                    "length": 256,
                    "name": "custom_log_sortby",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_sortdir": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "desc",
                    "length": 4,
                    "name": "custom_log_sortdir",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_display_time": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "1",
                    "length": 1,
                    "name": "custom_log_display_time",
                    "type": "TINYINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_lang_time": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 56,
                    "name": "custom_log_lang_time",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_lang_message": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 128,
                    "name": "custom_log_lang_message",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_imported": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "custom_log_imported",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_data": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "custom_log_data",
                    "type": "MEDIUMTEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                }
            },
            "indexes": {
                "PRIMARY": {
                    "type": "primary",
                    "name": "PRIMARY",
                    "length": [
                        null
                    ],
                    "columns": [
                        "custom_log_id"
                    ]
                },
                "custom_log_uuid": {
                    "type": "key",
                    "name": "custom_log_uuid",
                    "length": [
                        null
                    ],
                    "columns": [
                        "custom_log_uuid"
                    ]
                }
            }
        }
    ],
    "ms_tables": [
        {
            "name": "rules_scheduled_actions",
            "columns": {
                "schedule_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "schedule_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "schedule_time": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "schedule_time",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "schedule_data": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "schedule_data",
                    "type": "MEDIUMTEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "schedule_unique_key": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 2056,
                    "name": "schedule_unique_key",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "schedule_action_id": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "schedule_action_id",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "schedule_running": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "schedule_running",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "schedule_thread": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "schedule_thread",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "schedule_parent_thread": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "schedule_parent_thread",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "schedule_created": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "schedule_created",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "schedule_custom_id": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_520_ci",
                    "comment": "",
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "schedule_custom_id",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                }
            },
            "indexes": {
                "PRIMARY": {
                    "type": "primary",
                    "name": "PRIMARY",
                    "length": [
                        null
                    ],
                    "columns": [
                        "schedule_id"
                    ]
                },
                "schedule_time": {
                    "type": "key",
                    "name": "schedule_time",
                    "length": [
                        null
                    ],
                    "columns": [
                        "schedule_time"
                    ]
                },
                "schedule_unique_key": {
                    "type": "key",
                    "name": "schedule_unique_key",
                    "length": [
                        191
                    ],
                    "columns": [
                        "schedule_unique_key"
                    ]
                }
            }
        }
    ]
}
JSON;
