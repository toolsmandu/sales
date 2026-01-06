<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('chatbot_entries')) {
            DB::statement('ALTER TABLE chatbot_entries MODIFY question LONGTEXT');
            DB::statement('ALTER TABLE chatbot_entries MODIFY answer LONGTEXT');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('chatbot_entries')) {
            DB::statement('ALTER TABLE chatbot_entries MODIFY question TEXT');
            DB::statement('ALTER TABLE chatbot_entries MODIFY answer TEXT');
        }
    }
};
