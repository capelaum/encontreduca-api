<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resource_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('SET NULL')
                ->onUpdate('SET NULL');
            $table->foreignId('resource_id')
                ->constrained('resources')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->string('field');
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->timestamp('created_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resource_changes');
    }
};