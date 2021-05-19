<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

use App\Vendors\Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateCrawlCsnaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crawl_csna_sessions', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('crawler_id')->unsigned();
            $table->timestamps();

            $table->index('crawler_id');
            $table->index('created_at');
            $table->index('updated_at');
        });

        Schema::create('crawl_csna_urls', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('crawler_id')->unsigned();
            $table->tinyInteger('status')->default(1);
            $table->string('index')->unique();
            $table->string('url', 2048);

            $table->index('crawler_id');
        });

        Schema::create('crawl_csna_session_urls', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('crawl_session_id')->unsigned();
            $table->integer('crawl_url_id')->unsigned();
            $table->timestamps();

            $table->index('crawl_session_id');
            $table->index('crawl_url_id');
            $table->index('created_at');
            $table->index('updated_at');
        });

        Schema::create('crawl_csna_crawled_urls', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('from_crawl_url_id')->unsigned();
            $table->integer('crawl_url_id')->unsigned();
            $table->timestamps();

            $table->index('from_crawl_url_id');
            $table->index('crawl_url_id');
            $table->index('created_at');
            $table->index('updated_at');
        });

        Schema::create('crawl_csna_songs', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('crawler_id')->unsigned();
            $table->string('index')->unique();
            $table->longText('meta');

            $table->index('crawler_id');
        });

        Schema::create('crawl_csna_files', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('crawler_id')->unsigned();
            $table->integer('song_id')->unsigned();
            $table->integer('file_id')->unsigned();
            $table->string('index')->unique();
            $table->longText('meta');

            $table->index('crawler_id');
            $table->index('song_id');
            $table->index('file_id');
        });

        Schema::create('crawl_csna_crawled_songs', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('from_crawl_url_id')->unsigned();
            $table->integer('crawl_datum_id')->unsigned();
            $table->timestamps();

            $table->index('from_crawl_url_id');
            $table->index('crawl_datum_id');
            $table->index('created_at');
            $table->index('updated_at');
        });

        Schema::create('crawl_csna_crawled_files', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('from_crawl_url_id')->unsigned();
            $table->integer('crawl_datum_id')->unsigned();
            $table->timestamps();

            $table->index('from_crawl_url_id');
            $table->index('crawl_datum_id');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crawl_csna_crawled_files');
        Schema::dropIfExists('crawl_csna_crawled_songs');
        Schema::dropIfExists('crawl_csna_files');
        Schema::dropIfExists('crawl_csna_songs');
        Schema::dropIfExists('crawl_csna_crawled_urls');
        Schema::dropIfExists('crawl_csna_session_urls');
        Schema::dropIfExists('crawl_csna_urls');
        Schema::dropIfExists('crawl_csna_sessions');
    }
}
