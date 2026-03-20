<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$migrations = [
    '0001_01_01_000000_create_users_table',
    '0001_01_01_000001_create_cache_table',
    '0001_01_01_000002_create_jobs_table',
    '2026_02_09_021450_create_pets_table',
    '2026_02_14_230545_create_vaccine_table',
    '2026_02_15_015418_create_appointments_table',
    '2026_02_15_051713_create_activity_logs_table',
    '2026_02_15_064403_create_user_requests_table',
    '2026_02_18_130614_add_soft_deletes_to_activity_logs',
    '2026_02_18_134936_add_role_to_activity_logs_table',
    '2026_02_18_153138_create_vaccine_inventories_table',
    '2026_02_18_163637_add_profile_image_to_users_table',
    '2026_02_26_144953_add_rejection_reason_to_appointments_table',
    '2026_02_26_170308_add_vaccine_name_to_appointments_table',
    '2026_02_26_203057_add_appointment_id_to_vaccinations_table',
];

echo "Faking migrations...\n";

foreach ($migrations as $m) {
    DB::table('migrations')->updateOrInsert(
        ['migration' => $m],
        ['batch' => 1]
    );
    echo "Faked: $m\n";
}

echo "DONE!\n";
