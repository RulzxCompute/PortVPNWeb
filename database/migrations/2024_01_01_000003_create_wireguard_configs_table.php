<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wireguard_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('port_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('private_key');
            $table->string('public_key');
            $table->string('preshared_key')->nullable();
            $table->string('client_ip');
            $table->string('server_public_key');
            $table->string('server_endpoint');
            $table->integer('server_port');
            $table->text('config_file');
            $table->string('dns');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wireguard_configs');
    }
};
