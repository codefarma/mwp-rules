<?php
return <<<'JSON'
{
    "framework_version": "2.0.0",
    "framework_bundled": true,
    "tables": [
        {
            "name": "rules_rules",
            "columns": {
                "rule_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "rule_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "rule_title": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
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
                    "decimals": null,
                    "default": "1",
                    "length": 1,
                    "name": "rule_enabled",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_parent_id": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 20,
                    "name": "rule_parent_id",
                    "type": "MEDIUMINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_event_type": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": "",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "rule_data",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_priority": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
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
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": "and",
                    "length": 16,
                    "name": "rule_base_compare",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_debug": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 1,
                    "name": "rule_debug",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_feature_id": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "rule_feature_id",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_enable_recursion": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
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
                    "decimals": null,
                    "default": "1",
                    "length": 11,
                    "name": "rule_recursion_limit",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "rule_imported_time": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "rule_imported_time",
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
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "condition_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "condition_title": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
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
                    "decimals": null,
                    "default": "",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "condition_data",
                    "type": "MEDIUMTEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "condition_enabled": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "decimals": null,
                    "default": "0",
                    "length": 1,
                    "name": "condition_not",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "condition_footprint": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 56,
                    "name": "condition_footprint",
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
                        "condition_id"
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
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "action_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "action_title": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
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
                    "decimals": null,
                    "default": "",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "action_data",
                    "type": "MEDIUMTEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_description": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 1028,
                    "name": "action_schedule_key",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_footprint": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 56,
                    "name": "action_footprint",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "action_else": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 1,
                    "name": "action_else",
                    "type": "TINYINT",
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
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "argument_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "argument_title": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "argument_data",
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
                        "argument_id"
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
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "hook_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "hook_title": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": "",
                    "length": 1028,
                    "name": "hook_hook",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                }
            }
        },
        {
            "name": "rules_features",
            "columns": {
                "feature_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "feature_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "feature_title": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "feature_title",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "feature_weight": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "feature_weight",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "feature_enabled": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "1",
                    "length": 1,
                    "name": "feature_enabled",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "feature_description": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": "",
                    "length": 1028,
                    "name": "feature_description",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "feature_creator": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "feature_creator",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "feature_created_time": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "feature_created_time",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "feature_imported_time": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "feature_imported_time",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "feature_app_id": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 20,
                    "name": "feature_app_id",
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
                        "feature_id"
                    ]
                }
            }
        },
        {
            "name": "rules_scheduled_actions",
            "columns": {
                "schedule_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "decimals": null,
                    "default": "",
                    "length": 11,
                    "name": "schedule_action_id",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "schedule_queued": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "schedule_queued",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "schedule_thread": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                }
            }
        }
    ]
}
JSON;
