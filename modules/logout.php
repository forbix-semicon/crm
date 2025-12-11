<?php
/**
 * Logout Module
 * Handles user logout
 */

session_start();
require_once 'auth.php';

logout();

header('Location: ../index.php');
exit;
