<?php
require_once __DIR__.'/../../includes/auth.php';
requireRole('staff');
redirect('staff/requests.php?module=extensions');
