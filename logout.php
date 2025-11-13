<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth/auth.php';

logoutUser();
setFlashMessage('You have been logged out successfully.', 'success');
redirect('/index.php');
?>
