<?php

if (!function_exists('get_post_status')) {
    function get_post_status() : array {
        return [
            'draft',
            'publish'
        ];
    }
}
