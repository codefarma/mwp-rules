<?php
return <<<'JSON'
{
    "type": "plugin",
    "pluginFramework": "mwp",
    "name": "MWP Rules",
    "description": "An automation rules engine for WordPress",
    "author": "Kevin Carwile",
    "author_url": "https:\/\/www.codefarma.com",
    "url": "https:\/\/www.codefarma.com/rules",
    "slug": "mwp-rules",
    "vendor": "Code Farma",
    "namespace": "MWP\\Rules",
    "version": "0.9.2",
    "tables": "rules_actions,rules_arguments,rules_conditions,rules_custom_actions,rules_custom_logs,rules_data,rules_logs,rules_log_arguments,rules_rules,rules_rulesets,rules_scheduled_actions"
}
JSON;
