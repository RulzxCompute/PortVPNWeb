<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sstp_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('port_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('username');
            $table->string('password');
            $table->string('server_address');
            $table->integer('server_port');
            $table->string('client_ip')->nullable();
            $table->text('config_script');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sstp_configs');
    }
};
