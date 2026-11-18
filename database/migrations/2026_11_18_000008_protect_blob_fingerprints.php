<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::unprepared("CREATE TRIGGER file_blobs_fingerprint_immutable BEFORE UPDATE ON file_blobs
                WHEN NEW.sha256 != OLD.sha256 OR NEW.size_bytes != OLD.size_bytes
                BEGIN SELECT RAISE(ABORT, 'La huella del blob es inmutable'); END");
        } else {
            DB::unprepared("CREATE TRIGGER file_blobs_fingerprint_immutable BEFORE UPDATE ON file_blobs FOR EACH ROW
                BEGIN IF BINARY NEW.sha256 != BINARY OLD.sha256 OR NEW.size_bytes != OLD.size_bytes THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La huella del blob es inmutable'; END IF; END");
        }
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS file_blobs_fingerprint_immutable');
    }
};
