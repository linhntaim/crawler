<?php

namespace App\Crawlers\Targets\ChiaSeNhacMusic;

use App\Crawlers\CrawlBot as BaseCrawlBot;
use App\Crawlers\Targets\ChiaSeNhacMusic\ModelRepositories\CsnFileRepository;
use App\Crawlers\Targets\ChiaSeNhacMusic\ModelRepositories\CsnSongRepository;
use App\Crawlers\Targets\ChiaSeNhacMusic\Models\CsnFile;
use App\Crawlers\Targets\ChiaSeNhacMusic\Models\CsnSong;
use App\ModelRepositories\HandledFileRepository;
use Illuminate\Database\Eloquent\Collection;

class CrawlBot extends BaseCrawlBot
{
    public const NAME = 'chia_se_nhac_music';

    protected $startingUrls = [
        'https://chiasenhac.vn',
    ];

    protected $urlDomainPolicy = [
        'allow' => [
            'chiasenhac.vn',
            '*.chiasenhac.vn',
        ],
        'deny' => [
            'old.chiasenhac.vn',
        ],
    ];

    protected $urlPathPolicy = [
        'deny' => [
            'embed',
            'embed/*',
            'hd',
            'hd/*',
        ],
    ];

    /**
     * @var string
     */
    protected $dataIndex;

    /**
     * @var CsnSong
     */
    protected $crawlingSong;

    /**
     * @var CsnFile[]|Collection
     */
    protected $crawlingFiles;

    protected function canCrawlData()
    {
        if (preg_match('/^https?:\/\/[^\/]+\/mp3\/.+-([a-z0-9]+)\.html/', $this->crawlingUrl->url, $matches) === 1) {
            $this->dataIndex = $matches[1];
            return true;
        }
        return false;
    }

    protected function crawling()
    {
        $this->crawlingSong = null;
        $this->crawlingFiles = new Collection();

        return parent::crawling();
    }

    protected function crawlData()
    {
        $this->crawlSong();
        $this->crawlFiles();
    }

    protected function crawlSong()
    {
        $this->crawlingSong = $this->withDataRepository(CsnSongRepository::class)
            ->storeData($this->dataIndex, [
                'artist' => preg_match('/(?<=<li><span>Ca sĩ: <\/span>).+?(?=<\/li>)/', $this->crawlingContent, $matches) === 1 ?
                    strip_tags($matches[0]) : null,
                'title' => preg_match('/(?<=<h2 class="card-title">).+?(?=<\/h2>)/', $this->crawlingContent, $matches) === 1 ?
                    strip_tags($matches[0]) : null,
                'album' => preg_match('/(?<=<li><span>Album: <\/span>).+?(?=<\/li>)/', $this->crawlingContent, $matches) === 1 ?
                    strip_tags($matches[0]) : null,
                'composer' => preg_match('/(?<=<li><span>Sáng tác: <\/span>).+?(?=<\/li>)/', $this->crawlingContent, $matches) === 1 ?
                    strip_tags($matches[0]) : null,
                'date' => preg_match('/(?<=<li><span>Năm phát hành: <\/span>).+?(?=<\/li>)/', $this->crawlingContent, $matches) === 1 ?
                    strip_tags($matches[0]) : null,
            ]);
    }

    protected function crawlFiles()
    {
        if (whenPregMatchAll('/https?:\/\/[^"\']*\.(flac|mp3|m4a)/', $this->crawlingContent, $matches)) {
            $this->withDataRepository(CsnFileRepository::class);
            $urls = (function ($urls, $extensions) {
                $uniqueUrls = [];
                foreach ($urls as $index => $url) {
                    if (!array_key_exists($url, $uniqueUrls)) {
                        $uniqueUrls[$url] = $extensions[$index];
                    }
                }
                return $uniqueUrls;
            })($matches[0], $matches[1]);
            foreach ($urls as $url => $extension) {
                if ($filer = $this->urlDownload($url)) {
                    $fileIndex = $extension . '.' . $this->dataIndex . '.' . md5($url);
                    if (!$this->crawlDataRepository->hasIndex($fileIndex)) {
                        $this->crawlingFiles->push(
                            $this->storeData(
                                $fileIndex,
                                [
                                    'file_url' => $url,
                                ],
                                [
                                    'file_id' => (new HandledFileRepository())->createWithFiler($filer)->id,
                                    'song_id' => $this->crawlingSong->id,
                                ]
                            )
                        );
                    }
                }
            }
        }
    }
}