<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to create all tables matching bincom_test.sql.
     */
    public function up(): void
    {
        Schema::create('agentname', function (Blueprint $table) {
            $table->increments('name_id');
            $table->string('firstname', 255);
            $table->string('lastname', 255);
            $table->string('email', 255)->nullable();
            $table->string('phone', 13);
            $table->integer('pollingunit_uniqueid');
        });

        Schema::create('announced_lga_results', function (Blueprint $table) {
            $table->increments('result_id');
            $table->string('lga_name', 50);
            $table->string('party_abbreviation', 4);
            $table->integer('party_score');
            $table->string('entered_by_user', 50)->nullable();
            $table->string('date_entered', 50)->nullable();
            $table->string('user_ip_address', 50)->nullable();
        });

        Schema::create('announced_pu_results', function (Blueprint $table) {
            $table->increments('result_id');
            $table->string('polling_unit_uniqueid', 50)->index();
            $table->string('party_abbreviation', 4);
            $table->integer('party_score');
            $table->string('entered_by_user', 50)->nullable();
            $table->string('date_entered', 50)->nullable();
            $table->string('user_ip_address', 50)->nullable();
        });

        Schema::create('announced_state_results', function (Blueprint $table) {
            $table->increments('result_id');
            $table->string('state_name', 50);
            $table->string('party_abbreviation', 4);
            $table->integer('party_score');
            $table->string('entered_by_user', 50)->nullable();
            $table->string('date_entered', 50)->nullable();
            $table->string('user_ip_address', 50)->nullable();
        });

        Schema::create('announced_ward_results', function (Blueprint $table) {
            $table->increments('result_id');
            $table->string('ward_name', 50);
            $table->string('party_abbreviation', 4);
            $table->integer('party_score');
            $table->string('entered_by_user', 50)->nullable();
            $table->string('date_entered', 50)->nullable();
            $table->string('user_ip_address', 50)->nullable();
        });

        Schema::create('lga', function (Blueprint $table) {
            $table->increments('uniqueid');
            $table->integer('lga_id')->index();
            $table->string('lga_name', 50);
            $table->integer('state_id')->index();
            $table->text('lga_description')->nullable();
            $table->string('entered_by_user', 50)->nullable();
            $table->string('date_entered', 50)->nullable();
            $table->string('user_ip_address', 50)->nullable();
        });

        Schema::create('party', function (Blueprint $table) {
            $table->increments('id');
            $table->string('partyid', 11);
            $table->string('partyname', 11);
        });

        Schema::create('polling_unit', function (Blueprint $table) {
            $table->increments('uniqueid');
            $table->integer('polling_unit_id')->default(0);
            $table->integer('ward_id')->default(0)->index();
            $table->integer('lga_id')->default(0)->index();
            $table->integer('uniquewardid')->nullable()->index();
            $table->string('polling_unit_number', 50)->nullable();
            $table->string('polling_unit_name', 50)->nullable();
            $table->text('polling_unit_description')->nullable();
            $table->string('lat', 255)->nullable();
            $table->string('long', 255)->nullable();
            $table->string('entered_by_user', 50)->nullable();
            $table->string('date_entered', 50)->nullable();
            $table->string('user_ip_address', 50)->nullable();
        });

        Schema::create('states', function (Blueprint $table) {
            $table->integer('state_id')->primary();
            $table->string('state_name', 50);
        });

        Schema::create('ward', function (Blueprint $table) {
            $table->increments('uniqueid');
            $table->integer('ward_id')->default(0);
            $table->string('ward_name', 50);
            $table->integer('lga_id')->index();
            $table->text('ward_description')->nullable();
            $table->string('entered_by_user', 50)->nullable();
            $table->string('date_entered', 50)->nullable();
            $table->string('user_ip_address', 50)->nullable();
        });

        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method', 10);
            $table->string('path', 255);
            $table->string('action_label', 255);
            $table->string('ip_address', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referer', 255)->nullable();
            $table->timestamp('visited_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
        Schema::dropIfExists('ward');
        Schema::dropIfExists('states');
        Schema::dropIfExists('polling_unit');
        Schema::dropIfExists('party');
        Schema::dropIfExists('lga');
        Schema::dropIfExists('announced_ward_results');
        Schema::dropIfExists('announced_state_results');
        Schema::dropIfExists('announced_pu_results');
        Schema::dropIfExists('announced_lga_results');
        Schema::dropIfExists('agentname');
    }
};

