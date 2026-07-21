<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otps', function (Blueprint $table): void {
            $table->id();
            $table->string('identifier_type');
            $table->unsignedBigInteger('identifier_id');
            $table->string('code');
            $table->json('purpose'); // Changé de string à json
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['identifier_type', 'identifier_id']);
            $table->index('code');
            $table->index('expires_at');
            $table->index('used_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
