<?php
/* Shared Site member-account sidebar. */

global $USER_LOGIN;

$__active = isset($ACCOUNT_NAV_ACTIVE) ? $ACCOUNT_NAV_ACTIVE : 'profile';
$__email  = function_exists('strip') ? strip($USER_LOGIN['email'] ?? '') : htmlspecialchars((string)($USER_LOGIN['email'] ?? ''));
$__stat   = (int) ($USER_LOGIN['stat'] ?? 0);

$__isL1 = function_exists('site_is_l1') && site_is_l1($USER_LOGIN);
$__l2   = function_exists('site_l2_categories') ? site_l2_categories($USER_LOGIN) : [];

$__roleLabel = function_exists('site_member_role_sidebar_label')
    ? site_member_role_sidebar_label($USER_LOGIN)
    : 'חבר רגיל';
$__roleLevel = 0;
if (function_exists('site_member_primary_role')) {
    $__primaryRole = site_member_primary_role($USER_LOGIN);
    if ($__primaryRole) {
        $__roleLevel = (int) ($__primaryRole['level'] ?? 0);
    }
}

$__nav = [
    'profile'  => [',members',          'fa-user-pen',  'עריכת פרופיל'],
    'business' => [',members,business', 'fa-briefcase', 'ניהול עסקים'],
];
?>
<aside class="dashboard-sidebar">
    <div class="sidebar-header">
        <div class="status-toggle">
            <div>
                <span class="status-label">החשבון פעיל</span>
                <small class="status-note">הפרופיל פעיל במערכת</small>
            </div>
            <label class="status-switch" aria-label="מצב חשבון">
                <input type="checkbox" <?= $__stat > 0 ? 'checked' : '' ?> disabled />
                <span class="status-slider"></span>
            </label>
        </div>
        <p class="account-role-level<?= $__roleLevel > 0 ? ' account-role-level--l' . $__roleLevel : ' account-role-level--none' ?>" aria-label="רמת משתמש במערכת">
            <?= htmlspecialchars($__roleLabel, ENT_QUOTES, 'UTF-8') ?>
        </p>
        <h2>ניהול חשבון משתמש</h2>
        <p><?= $__email ?></p>
    </div>

    <ul class="dashboard-nav">
        <?php foreach ($__nav as $__key => $__item): ?>
            <li class="<?= $__active === $__key ? 'active' : '' ?>">
                <a href="<?= $__item[0] ?>"><i class="fa-solid <?= $__item[1] ?>"></i> <?= $__item[2] ?></a>
            </li>
        <?php endforeach; ?>
        <?php if ($__isL1): ?>
        <li class="<?= $__active === 'domain' ? 'active' : '' ?>">
            <a href=",members,domain"><i class="fa-solid fa-globe"></i> ניהול תחום</a>
        </li>
        <?php endif; ?>
        <?php if (!empty($__l2)): ?>
        <li class="<?= $__active === 'category' ? 'active' : '' ?>">
            <a href=",members,category"><i class="fa-solid fa-sitemap"></i> ניהול קטגוריה</a>
        </li>
        <?php endif; ?>
        <li>
            <a href=",members,logout"><i class="fa-solid fa-right-from-bracket"></i> התנתקות</a>
        </li>
    </ul>
</aside>
