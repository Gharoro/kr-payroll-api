<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayslipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('month');
            $table->decimal('basic_salary');
            $table->decimal('net_salary');
            $table->decimal('housing');
            $table->decimal('transportation');
            $table->decimal('paye_amount');
            $table->decimal('pension_amount');
            $table->decimal('nsitf_amount');
            $table->decimal('nhf_amount');
            $table->decimal('total_deductions');
            $table->decimal('total_earnings');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payslips');
    }
}
