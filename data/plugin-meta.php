<?php
return <<<'JSON'
{
    "type": "plugin",
    "pluginFramework": "mwp",
    "themeFramework": "none",
    "parentTheme": "bizznis",
    "name": "MWP Rules",
    "description": "A rules engine for wordpress",
    "author": "Kevin Carwile",
    "author_url": "http:\/\/millermedia.io",
    "url": "",
    "slug": "mwp-rules",
    "vendor": "Miller Media",
    "namespace": "MWP\\Rules",
    "version": "0.9.1",
    "tables": "rules_actions,rules_arguments,rules_conditions,rules_custom_actions,rules_custom_logs,rules_data,rules_logs,rules_log_arguments,rules_rules,rules_rulesets,rules_scheduled_actions"
}
JSON;
