<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

use App\Vendors\Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateCrawlersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crawler_instances', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->string('name')->unique();
        });

        Schema::create('crawlers', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->string('name')->unique();
            $table->timestamps();

            $table->index('created_at');
            $table->index('updated_at');
        });

        Schema::create('crawl_sessions', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('crawler_id')->unsigned();
            $table->timestamps();

            $table->index('crawler_id');
            $table->index('created_at');
            $table->index('updated_at');
        });

        Schema::create('crawl_urls', function (Blueprint $table) {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('crawler_id')->unsigned();
            $table->integer('crawl_session_id')->unsigned();
            $table->tinyInteger('status')->default(1);
            $table->string('index')->unique();
            $table->string('url', 2048);
            $table->timestamps();

            $table->index('crawler_id');
            $table->index('crawl_session_id');
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
        Schema::dropIfExists('crawl_urls');
        Schema::dropIfExists('crawl_sessions');
        Schema::dropIfExists('crawlers');
        Schema::dropIfExists('crawler_instances');
    }
}
