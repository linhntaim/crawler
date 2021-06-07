<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

use App\Vendors\Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateImpersonatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (config('starter.impersonated_by_admin')) {
            Schema::create('impersonates', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->rowFormat = 'DYNAMIC';

                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->integer('via_user_id')->unsigned();
                $table->string('impersonate_token')->unique();
                $table->string('auth_token')->nullable();
                $table->timestamps();

                $table->index('user_id');
                $table->index('auth_token');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('impersonates');
    }
}
