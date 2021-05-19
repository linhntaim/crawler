<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio;

use App\Crawlers\CrawlBot as BaseCrawlBot;
use App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories\CsnaCrawledFileRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories\CsnaCrawledSongRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories\CsnaCrawledUrlRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories\CsnaCrawlerRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories\CsnaFileRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories\CsnaSessionRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories\CsnaSessionUrlRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories\CsnaSongRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories\CsnaUrlRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\Models\CsnaFile;
use App\Crawlers\Targets\ChiaSeNhacAudio\Models\CsnaSong;
use App\ModelRepositories\HandledFileRepository;
use Illuminate\Database\Eloquent\Collection;

class CrawlBot extends BaseCrawlBot
{
    public const NAME = 'chia_se_nhac_audio';

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
     * @var CsnaSong
     */
    protected $crawlingSong;

    /**
     * @var CsnaFile[]|Collection
     */
    protected $crawlingFiles;

    protected function crawlerRepositoryClass()
    {
        return CsnaCrawlerRepository::class;
    }

    protected function crawlSessionRepositoryClass()
    {
        return CsnaSessionRepository::class;
    }

    protected function crawlUrlRepositoryClass()
    {
        return CsnaUrlRepository::class;
    }

    protected function crawlSessionUrlRepositoryClass()
    {
        return CsnaSessionUrlRepository::class;
    }

    protected function crawledUrlRepositoryClass()
    {
        return CsnaCrawledUrlRepository::class;
    }

    protected function canCrawlData()
    {
        if (preg_match('/^https?:\/\/[^\/]+\/mp3\/.+-([a-z0-9]+)\.html/i', $this->crawlingUrl->url, $matches) === 1) {
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
        $this->crawlingSong = $this->withDatumRepository(
            CsnaSongRepository::class,
            CsnaCrawledSongRepository::class
        )
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
        if (whenPregMatchAll('/https?:\/\/[^"\'\)\]]*\.(flac|mp3|m4a)/i', $this->crawlingContent, $matches)) {
            $this->withDatumRepository(
                CsnaFileRepository::class,
                CsnaCrawledFileRepository::class
            );
            $urls = (function ($urls, $extensions) {
                $uniqueUrls = [];
                foreach ($urls as $index => $url) {
                    $lowerUrl = mb_strtolower($url);
                    if (!array_key_exists($lowerUrl, $uniqueUrls)) {
                        $uniqueUrls[$lowerUrl] = [
                            'original' => $url,
                            'extension' => mb_strtolower($extensions[$index]),
                        ];
                    }
                }
                return $uniqueUrls;
            })($matches[0], $matches[1]);
            foreach ($urls as $lowerUrl => $url) {
                if ($filer = $this->urlDownload($url['original'])) {
                    $fileIndex = $url['extension'] . '.' . $this->dataIndex . '.' . md5($lowerUrl);
                    $this->crawlingFiles->push(
                        $this->storeData(
                            $fileIndex,
                            [
                                'file_url' => $url['original'],
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