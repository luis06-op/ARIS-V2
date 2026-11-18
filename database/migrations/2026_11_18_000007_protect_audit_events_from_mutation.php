<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        if (! in_array($driver, ['sqlite', 'mysql'], true)) {
            throw new RuntimeException('La protección de auditoría requiere SQLite o MySQL.');
        }
        foreach (['UPDATE', 'DELETE'] as $operation) {
            $name = 'audit_events_no_'.strtolower($operation);
            $body = $driver === 'sqlite'
                ? "BEGIN SELECT RAISE(ABORT, 'La auditoría es de solo inserción'); END"
                : "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La auditoría es de solo inserción'";
            DB::unprepared("CREATE TRIGGER {$name} BEFORE {$operation} ON audit_events FOR EACH ROW {$body}");
        }
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS audit_events_no_update');
        DB::unprepared('DROP TRIGGER IF EXISTS audit_events_no_delete');
    }
};
