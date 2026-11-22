<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('staff');
redirect('staff/manage.php?module=equipment');
