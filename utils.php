<?php
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $now = time();
    $diff = $now - $timestamp;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . 'm';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . 'h';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . 'd';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . 'w';
    } elseif ($diff < 31536000) {
        $months = floor($diff / 2592000);
        return $months . 'mo';
    } else {
        $years = floor($diff / 31536000);
        return $years . 'y';
    }
}
