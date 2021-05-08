<?php

namespace App\Crawlers\Models;

use App\Models\Base\Model;

/**
 * Class CrawlerInstance
 * @package App\Crawlers\Models
 * @property int $id
 * @property string $name
 */
class CrawlerInstance extends Model
{
    protected $table = 'crawler_instances';

    protected $fillable = [
        'name',
    ];

    public $timestamps = false;
}