<?php
return <<<'JSON'
{
    "type": "plugin",
    "pluginFramework": "mwp",
    "name": "MWP Rules",
    "description": "An automation rules engine for WordPress",
    "author": "Kevin Carwile",
    "author_url": "https:\/\/www.codefarma.com",
    "url": "https:\/\/www.codefarma.com\/rules",
    "slug": "mwp-rules",
    "vendor": "Code Farma",
    "namespace": "MWP\\Rules",
    "version": "1.0.0",
    "tables": "rules_rules,rules_conditions,rules_actions,rules_arguments,rules_hooks,rules_logs,rules_bundles,rules_scheduled_actions"
}
JSON;
