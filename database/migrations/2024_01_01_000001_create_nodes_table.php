<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain');
            $table->string('ip_address');
            $table->string('location');
            $table->string('region');
            $table->integer('port_start')->default(1000);
            $table->integer('port_end')->default(10000);
            $table->integer('total_ports')->default(9000);
            $table->integer('used_ports')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('api_key');
            $table->boolean('ssl_enabled')->default(true);
            $table->integer('ping_ms')->nullable();
            $table->timestamp('last_ping_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};
