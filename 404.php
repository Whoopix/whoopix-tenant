<?php

declare(strict_types=1);

http_response_code(404);

$lang = $_GET['lang'] ?? 'he';

if ($lang === 'en') {
    $TEMP_PAGE_TITLE   = 'Oops! Page Not Found (404)';
    $TEMP_INPAGE_TITLE = 'Oops! Page Not Found (404)';
} else {
    $TEMP_PAGE_TITLE   = 'אופס! הגעת לדף שאינו נמצא (404)';
    $TEMP_INPAGE_TITLE = 'אופס! הגעת לדף שאינו נמצא (404)';
}

$PAGE_CONTENT = '<% BLOCK_12 %>';

$TEMP_TEMPLATE = 2;