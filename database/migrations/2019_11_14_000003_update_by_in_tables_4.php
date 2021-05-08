<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

use App\Vendors\Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class UpdateByInTables4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_imports', function (Blueprint $table) {
            $table->integer('created_by')->after('id')->unsigned()->nullable();

            $table->foreign('created_by')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_imports', function (Blueprint $table) {
            $table->dropForeign('data_imports_created_by_foreign');
            $table->dropColumn('created_by');
        });
    }
}
