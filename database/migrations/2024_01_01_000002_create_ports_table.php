<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('node_id')->constrained()->onDelete('cascade');
            $table->integer('public_port');
            $table->integer('local_port');
            $table->string('protocol'); // tcp, udp, both
            $table->string('vpn_type'); // wireguard, sstp, both
            $table->boolean('ssh_enabled')->default(false);
            $table->integer('ssh_port')->nullable();
            $table->string('status')->default('active'); // active, suspended, expired
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->unique(['node_id', 'public_port']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ports');
    }
};
