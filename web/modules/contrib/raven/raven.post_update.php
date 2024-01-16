<?php

/**
 * @file
 * Post update functions for Raven module.
 */

/**
 * Add current_user and request_stack as optional service arguments.
 */
function raven_post_update_new_service_arguments(): void {
}

/**
 * Add new service arguments for the request subscriber.
 */
function raven_post_update_request_subscriber_service_arguments(): void {
}

/**
 * Move database logging from stack middleware to request subscriber.
 */
function raven_post_update_disable_middleware(): void {
}
