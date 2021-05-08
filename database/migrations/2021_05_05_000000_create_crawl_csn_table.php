<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

use App\Vendors\Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateCrawlCsnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crawl_data_csn_songs', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('crawl_url_id')->unsigned();
            $table->integer('crawl_session_id')->unsigned();
            $table->integer('crawler_id')->unsigned();
            $table->string('index')->unique();
            $table->longText('meta');
            $table->timestamps();

            $table->index('crawl_url_id');
            $table->index('crawl_session_id');
            $table->index('crawler_id');
        });

        Schema::create('crawl_data_csn_files', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('file_id')->unsigned();
            $table->integer('song_id')->unsigned();
            $table->integer('crawl_url_id')->unsigned();
            $table->integer('crawl_session_id')->unsigned();
            $table->integer('crawler_id')->unsigned();
            $table->string('index')->unique();
            $table->longText('meta');
            $table->timestamps();

            $table->index('file_id');
            $table->index('song_id');
            $table->index('crawl_url_id');
            $table->index('crawl_session_id');
            $table->index('crawler_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crawl_data_csn_files');
        Schema::dropIfExists('crawl_data_csn_songs');
    }
}
