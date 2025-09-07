<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/auth.php';
if (auth_user()) {
  header('Location: /public/dashboard.php');
} else {
  header('Location: /public/login.php');
}
