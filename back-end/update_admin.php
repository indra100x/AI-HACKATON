<?php
require 'vendor/autoload.php';
$app = require_once('bootstrap/app.php');
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

DB::table('users')->where('email', 'hichemboutouatou@gmail.com')->update(['role' => 'admin']);
echo "User role updated to admin\n";
