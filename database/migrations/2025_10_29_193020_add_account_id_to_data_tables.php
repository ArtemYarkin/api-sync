<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountIdToDataTables extends Migration
{
    public function up()
    {
        
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('id');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

       
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('id');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        
        Schema::table('stocks', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('id');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        
        Schema::table('incomes', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('id');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
}