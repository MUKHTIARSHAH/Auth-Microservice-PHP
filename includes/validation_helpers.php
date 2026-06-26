<?php
/**
 * Input validation helper functions
 */

function validate_required_field($value, $field_name) {
    if (empty($value) && $value !== '0' && $value !== 0) {
        return ucfirst($field_name) . ' is required';
    }
    return null;
}

function validate_email_format($email_address) {
    if (empty($email_address)) {
        return false;
    }
    return filter_var($email_address, FILTER_VALIDATE_EMAIL) !== false;
} 