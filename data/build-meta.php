<?php
return <<<'JSON'
{
    "tables": [
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
                "argument_name": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "argument_name",
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
                "argument_custom_class": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "argument_custom_class",
                    "type": "VARCHAR",
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
            "name": "rules_custom_actions",
            "columns": {
                "custom_action_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "custom_action_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "custom_action_title": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "custom_action_title",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_action_weight": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "custom_action_weight",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_action_description": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 2048,
                    "name": "custom_action_description",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_action_key": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "custom_action_key",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_action_enable_api": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 1,
                    "name": "custom_action_enable_api",
                    "type": "TINYINT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "custom_action_api_methods": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 32,
                    "name": "custom_action_api_methods",
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
                        "custom_action_id"
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
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "custom_log_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "custom_log_title": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
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
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 128,
                    "name": "custom_log_lang_message",
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
                        "custom_log_id"
                    ]
                }
            }
        },
        {
            "name": "rules_data",
            "columns": {
                "data_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "data_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "data_class": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "data_class",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "data_name": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "data_name",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "data_type": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": "",
                    "length": 56,
                    "name": "data_type",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "data_type_class": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "data_type_class",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "data_column_name": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": "",
                    "length": 56,
                    "name": "data_column_name",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "data_weight": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 5,
                    "name": "data_weight",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "data_tab": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 56,
                    "name": "data_tab",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "data_use_mode": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": "internal",
                    "length": 56,
                    "name": "data_use_mode",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "data_required": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 1,
                    "name": "data_required",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "data_description": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 1028,
                    "name": "data_description",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "data_key": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": "",
                    "length": 56,
                    "name": "data_key",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "data_text_mode": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "1",
                    "length": 1,
                    "name": "data_text_mode",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "data_configuration": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "data_configuration",
                    "type": "MEDIUMTEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "data_setting_key": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 128,
                    "name": "data_setting_key",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "data_value_default": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "data_value_default",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "data_value_options": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 0,
                    "name": "data_value_options",
                    "type": "TEXT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "data_display_mode": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": "automatic",
                    "length": 56,
                    "name": "data_display_mode",
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
                        "data_id"
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
            "name": "rules_log_arguments",
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
                "argument_name": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "argument_name",
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
                "argument_custom_class": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "argument_custom_class",
                    "type": "VARCHAR",
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
                "rule_ruleset_id": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "rule_ruleset_id",
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
            "name": "rules_rulesets",
            "columns": {
                "ruleset_id": {
                    "allow_null": false,
                    "auto_increment": true,
                    "binary": false,
                    "decimals": null,
                    "default": null,
                    "length": 20,
                    "name": "ruleset_id",
                    "type": "BIGINT",
                    "unsigned": true,
                    "values": [],
                    "zerofill": false
                },
                "ruleset_title": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": "",
                    "length": 255,
                    "name": "ruleset_title",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "ruleset_weight": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "ruleset_weight",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "ruleset_enabled": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "1",
                    "length": 1,
                    "name": "ruleset_enabled",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "ruleset_description": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": "",
                    "length": 1028,
                    "name": "ruleset_description",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "ruleset_creator": {
                    "allow_null": true,
                    "auto_increment": false,
                    "binary": false,
                    "collation": "utf8mb4_unicode_ci",
                    "decimals": null,
                    "default": null,
                    "length": 255,
                    "name": "ruleset_creator",
                    "type": "VARCHAR",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "ruleset_created_time": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "ruleset_created_time",
                    "type": "INT",
                    "unsigned": false,
                    "values": [],
                    "zerofill": false
                },
                "ruleset_imported_time": {
                    "allow_null": false,
                    "auto_increment": false,
                    "binary": false,
                    "decimals": null,
                    "default": "0",
                    "length": 11,
                    "name": "ruleset_imported_time",
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
                        "ruleset_id"
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
