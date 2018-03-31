<?php
return <<<'JSON'
{
    "events": {
        "woocommerce_order_status_completed": {
            "type": "action",
            "definition": {
                "title": "Woocommerce Order Completed",
                "description": "Triggered when a woocommerce order is marked as completed.",
                "arguments": {
                    "order_id": {
                        "argtype": "int",
                        "class": "WP_Order",
                        "label": "Order ID",
                        "description": "The order id"
                    },
                    "user_id": {
                        "argtype": "int",
                        "class": "WP_User",
                        "label": "User ID",
                        "description": "The user id"
                    }
                }
            }
        },
        "rules\/action\/5abed20bc9eeb": {
            "type": "action",
            "definition": {
                "title": "Test",
                "description": null,
                "arguments": {
                    "name": {
                        "argtype": "string",
                        "class": null,
                        "label": "Name",
                        "description": null
                    }
                }
            }
        }
    },
    "actions": []
}
JSON;
