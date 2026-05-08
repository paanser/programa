<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/helpers.php';
require_once __DIR__ . '/lib/auth.php';

$lang = get_current_lang();
logout_user();
header('Location: ' . url_with_lang('login.php', [], $lang));
exit;
