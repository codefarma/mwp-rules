<?php
return <<<'JSON'
{
    "type": "plugin",
    "pluginFramework": "mwp",
    "name": "Automation Rules",
    "description": "Automate new features and processes for any WordPress site through the use of simple \"rules\".",
    "author": "Code Farma",
    "author_url": "https:\/\/www.codefarma.com",
    "url": "https:\/\/www.codefarma.com\/rules",
    "slug": "mwp-rules",
    "vendor": "Code Farma",
    "namespace": "MWP\\Rules",
    "version": "1.0.1",
    "tables": "rules_rules,rules_conditions,rules_actions,rules_arguments,rules_hooks,rules_logs,rules_bundles,rules_scheduled_actions,rules_custom_logs"
}
JSON;
