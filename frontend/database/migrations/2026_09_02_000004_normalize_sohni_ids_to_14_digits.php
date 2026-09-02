<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $used = [];

        DB::table('users')
            ->whereNotNull('sohni_id')
            ->pluck('id', 'sohni_id')
            ->each(function ($userId, $oldId) use (&$used): void {
                $digits = preg_replace('/\D/', '', (string) $oldId);
                $newId = null;

                if (strlen($digits) === 14 && ! isset($used[$digits])) {
                    $newId = $digits;
                } else {
                    do {
                        $newId = (string) random_int(10000000000000, 99999999999999);
                    } while (isset($used[$newId]) || DB::table('users')->where('sohni_id', $newId)->exists());
                }

                $used[$newId] = true;
                DB::table('users')->where('id', $userId)->update(['sohni_id' => $newId]);
            });
    }

    public function down(): void
    {
        // Numeric IDs cannot be safely restored to their former formats.
    }
};
