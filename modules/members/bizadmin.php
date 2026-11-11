<?php
/**
 * Legacy route — redirects to the role-appropriate panel.
 * L1 → domain | L2 → category | L3 → business
 */
if (!is_array($USER_LOGIN)) {
    include 'login.php';
} else {
    if (function_exists('site_is_l1') && site_is_l1($USER_LOGIN)) {
        header('Location: ,members,domain');
    } elseif (function_exists('site_l2_categories') && !empty(site_l2_categories($USER_LOGIN))) {
        header('Location: ,members,category');
    } else {
        header('Location: ,members,business');
    }
    exit;
}
