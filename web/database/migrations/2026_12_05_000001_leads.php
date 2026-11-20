<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * People who asked for a link to Loft to open on their computer (decision 50): the address, where
 * they asked and where their visit began, whether they said Loft could follow up, and the studio they
 * went on to start. Deleted after 90 days by the housekeeping run.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('email', 190)->index();
            $table->string('page', 20); // home | demo
            $table->json('signup_source')->nullable(); // the visit's campaign tags and referrer (SignupSource)
            $table->dateTime('follow_up_consent_at')->nullable(); // ticked "you can email me about Loft later"
            $table->dateTime('link_sent_at')->nullable();
            $table->foreignId('studio_id')->nullable()->constrained()->nullOnDelete(); // the studio they started, if they did
            $table->dateTime('converted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
