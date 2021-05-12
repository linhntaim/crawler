<?php

namespace App\Crawlers;

use App\Crawlers\ModelRepositories\CrawlDataRepository;
use App\Crawlers\ModelRepositories\CrawlerInstanceRepository;
use App\Crawlers\ModelRepositories\CrawlerRepository;
use App\Crawlers\ModelRepositories\CrawlSessionRepository;
use App\Crawlers\ModelRepositories\CrawlUrlRepository;
use App\Crawlers\Models\CrawlData;
use App\Crawlers\Models\Crawler;
use App\Crawlers\Models\CrawlSession;
use App\Crawlers\Models\CrawlUrl;
use App\Exceptions\AppException;
use App\Utils\HandledFiles\Filer\Filer;
use App\Utils\HandledFiles\Helper;
use App\Utils\ReportExceptionTrait;
use App\Vendors\Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Throwable;

abstract class CrawlBot
{
    use ReportExceptionTrait;

    public const NAME = 'crawl';
    public const CRAWLING_MAX = 1000;
    public const CRAWLING_RETRIEVE_MAX = 1000;

    /**
     * @var Factory
     */
    protected $client;

    /**
     * @var CrawlerInstanceRepository
     */
    protected $crawlerInstanceRepository;

    /**
     * @var CrawlerRepository
     */
    protected $crawlerRepository;

    /**
     * @var CrawlSessionRepository
     */
    protected $crawlSessionRepository;

    /**
     * @var CrawlUrlRepository
     */
    protected $crawlUrlRepository;

    /**
     * @var CrawlDataRepository
     */
    protected $crawlDataRepository;

    /**
     * @var string[]|array
     */
    protected $startingUrls = [];

    /**
     * @var string
     */
    protected $startingUrl;

    protected $crawlingMax = CrawlBot::CRAWLING_MAX;

    protected $crawlingRetrieveMax = CrawlBot::CRAWLING_RETRIEVE_MAX;

    protected $instance;

    protected $instanceCount = 0;

    protected $instanceOrder = 0;

    /**
     * @var Crawler
     */
    protected $crawler;

    /**
     * @var CrawlSession
     */
    protected $crawlSession;

    protected $urlDomainPolicy = [];

    protected $urlPathPolicy = [];

    protected $urlExtensionPolicy = [
        'allow' => [
            'htm*', 'php*', 'asp*', 'jsp*',
            '*html',
            'erb', 'py', 'pl', 'cgi',
        ],
    ];

    /**
     * @var CrawlUrl[]|Collection
     */
    protected $crawlingUrls;

    protected $crawlingCount = 0;

    /**
     * @var CrawlUrl
     */
    protected $crawlingUrl;

    protected $crawlingContent = null;

    public function __construct(string $instance = null)
    {
        $this->client = Http::getFacadeRoot();
        $this->crawlerInstanceRepository = new CrawlerInstanceRepository();
        $crawlerRepositoryClass = $this->crawlerRepositoryClass();
        $this->crawlerRepository = new $crawlerRepositoryClass();
        $crawlSessionRepositoryClass = $this->crawlSessionRepositoryClass();
        $this->crawlSessionRepository = new $crawlSessionRepositoryClass();
        $crawlUrlRepositoryClass = $this->crawlUrlRepositoryClass();
        $this->crawlUrlRepository = new $crawlUrlRepositoryClass();
        $this->instance = $instance ?: config('crawl.instance_name');
    }

    public function getName()
    {
        return static::NAME;
    }

    protected abstract function crawlerRepositoryClass();

    protected abstract function crawlSessionRepositoryClass();

    protected abstract function crawlUrlRepositoryClass();

    /**
     * @param int $crawlingMax
     * @return static
     */
    public function setCrawlingMax(int $crawlingMax)
    {
        $this->crawlingMax = $crawlingMax;
        return $this;
    }

    /**
     * @param int $crawlingRetrieveMax
     * @return static
     */
    public function setCrawlingRetrieveMax(int $crawlingRetrieveMax)
    {
        $this->crawlingRetrieveMax = $crawlingRetrieveMax;
        return $this;
    }

    /**
     * @return Crawler
     */
    public function getCrawler()
    {
        return $this->crawler;
    }

    /**
     * @return CrawlSession
     */
    public function getCrawlSession()
    {
        return $this->crawlSession;
    }

    /**
     * @return CrawlUrl
     */
    public function getCrawlingUrl()
    {
        return $this->crawlingUrl;
    }

    /**
     * @return static
     */
    protected function retrieveInstance()
    {
        $this->crawlerInstanceRepository->firstOrCreateWithName($this->instance);
        $instances = $this->crawlerInstanceRepository->sort('id')
            ->getAll()
            ->pluck('name')
            ->all();
        $this->instanceCount = count($instances);
        $this->instanceOrder = array_search($this->instance, $instances) + 1;
        return $this;
    }

    public function clearInstance()
    {
        $this->crawlerInstanceRepository->delete();
        $this->instanceOrder = 0;
    }

    public function clearInstances()
    {
        $this->crawlerInstanceRepository->deleteAll();
        $this->instanceCount = 0;
        $this->instanceOrder = 0;
    }

    protected function retrieveCrawler()
    {
        $this->crawler = $this->crawlerRepository->firstOrCreateWithName($this->getName());
        return $this;
    }

    protected function generateSession()
    {
        $this->crawlSession = $this->crawlSessionRepository->createWithCrawler($this->crawler);
        return $this;
    }

    /**
     * @return bool
     */
    protected function hasCrawled()
    {
        return $this->crawlUrlRepository->has(['crawler_id' => $this->crawler->id]);
    }

    /**
     * @param CrawlUrl[]|Collection $crawlingUrls
     * @return static
     */
    protected function queueCrawlingUrls($crawlingUrls)
    {
        $this->crawlingUrls->push(
            ...($crawlingUrls instanceof Collection ? $crawlingUrls->all() : $crawlingUrls)
        );
        return $this;
    }

    /**
     * @return CrawlUrl
     */
    protected function nextCrawlingUrl()
    {
        return $this->crawlingUrls->shift();
    }

    protected function bootstrap()
    {
        return $this->retrieveInstance()
            ->retrieveCrawler()
            ->generateSession();
    }

    protected function crawling()
    {
        $canCrawlData = $this->canCrawlData();
        $canCrawlUrls = $this->canCrawlUrls();
        if ($canCrawlData || $canCrawlUrls) {
            if ($this->setCrawlingContent()->hasCrawlingContent()) {
                $this->updateCrawlingProgress(CrawlUrl::STATUS_CRAWLING);
                $status = CrawlUrl::STATUS_COMPLETED;
                if ($canCrawlData) {
                    try {
                        $this->crawlData();
                    }
                    catch (Throwable $exception) {
                        $this->reportException($exception);

                        $status = CrawlUrl::STATUS_UNCOMPLETED;
                    }
                }
                if ($canCrawlUrls) {
                    try {
                        $this->crawlUrls();
                    }
                    catch (Throwable $exception) {
                        $this->reportException($exception);

                        $status = $status == CrawlUrl::STATUS_UNCOMPLETED ?
                            CrawlUrl::STATUS_FAILED : CrawlUrl::STATUS_UNCOMPLETED;
                    }
                }
                $this->updateCrawlingProgress($status)
                    ->clearCrawlingContent();
            }
        }
        return $this;
    }

    protected function updateCrawlingProgress($status = CrawlUrl::STATUS_COMPLETED)
    {
        $this->crawlUrlRepository->withModel($this->crawlingUrl)->updateStatus($status);
        return $this;
    }

    protected function setCrawlingContent()
    {
        $response = $this->client->get($this->crawlingUrl->url);

        $this->crawlingContent = $response->successful() ? $response->body() : null;
        return $this;
    }

    protected function clearCrawlingContent()
    {
        $this->crawlingContent = null;
        return $this;
    }

    protected function hasCrawlingContent()
    {
        return filled($this->crawlingContent);
    }

    protected function canCrawlData()
    {
        return true;
    }

    protected function canCrawlUrls()
    {
        return true;
    }

    protected abstract function crawlData();

    protected function withDataRepository($dataRepository)
    {
        if (is_string($dataRepository)) {
            $dataRepository = new $dataRepository;
        }
        if ($dataRepository instanceof CrawlDataRepository) {
            $this->crawlDataRepository = $dataRepository;
        }
        return $this;
    }

    /**
     * @param string $index
     * @param array $meta
     * @param array $additional
     * @return CrawlData|mixed
     * @throws
     */
    protected function storeData(string $index, array $meta = [], array $additional = [])
    {
        if (is_null($this->crawlDataRepository)) {
            throw new AppException('Data repository was not set.');
        }

        return $this->crawlDataRepository->firstOrCreateWithAttributes(
            [
                'index' => $index,
            ],
            [
                'crawl_url_id' => $this->crawlingUrl->id,
                'crawl_session_id' => $this->crawlSession->id,
                'crawler_id' => $this->crawler->id,
                'index' => $index,
                'meta' => $meta,
            ] + $additional
        );
    }

    protected function findUrlsForCrawling()
    {
        if (whenPregMatchAll('/https?:\/\/(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])([^"\']*|\/[^"\']*)/', $this->crawlingContent, $matches)) {
            return array_map(function ($url) {
                return rtrim($url, " \t\n\r\0\x0B/");
            }, $matches[0]);
        }
        return [];
    }

    protected function filterUrlsForCrawling(array $urls)
    {
        return $this->filterUrlsByExtensions(
            $this->filterUrlsByPaths(
                $this->filterUrlsByDomains($urls)
            )
        );
    }

    protected function filterUrlsByDomains(array $urls)
    {
        if (count($this->urlDomainPolicy)) {
            return array_filter($urls, function ($url) {
                if (preg_match('/^https?:\/\/([^\/]+)(\/.*)?$/', $url, $matches) === 1) {
                    return $this->filterByPolicy(strtolower($matches[1]), $this->urlDomainPolicy);
                }
                return false;
            });
        }
        return $urls;
    }

    protected function filterUrlsByPaths(array $urls)
    {
        if (count($this->urlPathPolicy)) {
            return array_filter($urls, function ($url) {
                if (preg_match('/^https?:\/\/[^\/]+\/(.+)$/', $url, $matches) === 1) {
                    return $this->filterByPolicy(strtolower($matches[1]), $this->urlPathPolicy);
                }
                return true;
            });
        }
        return $urls;
    }

    protected function filterUrlsByExtensions(array $urls)
    {
        if (count($this->urlExtensionPolicy)) {
            return array_filter($urls, function ($url) {
                if (preg_match('/^https?:\/\/[^\/]+\/.+\.([a-zA-Z0-9]+)(\?.*)?$/', $url, $matches) === 1) {
                    return $this->filterByPolicy(strtolower($matches[1]), $this->urlExtensionPolicy);
                }
                return true;
            });
        }
        return $urls;
    }

    protected function filterByPolicy($value, $policy)
    {
        $allows = $policy['allow'] ?? null;
        $denies = $policy['deny'] ?? null;
        return (!$allows || Str::is($allows, $value))
            && (!$denies || !Str::is($denies, $value));
    }

    protected function crawlUrls()
    {
        $this->storeUrlsForCrawling(
            $this->filterUrlsForCrawling(
                $this->findUrlsForCrawling()
            )
        );
    }

    /**
     * @param string[]|array $urls
     */
    protected function storeUrlsForCrawling(array $urls)
    {
        foreach ($urls as $url) {
            $this->storeUrlForCrawling($url);
        }
    }

    /**
     * @param string $url
     * @return CrawlUrl
     */
    protected function storeUrlForCrawling(string $url)
    {
        return $this->crawlUrlRepository->firstOrCreateWithAttributes(
            [
                'index' => ($index = $this->urlIndex($url)),
            ],
            $this->urlAttributes($url, $index)
        );
    }

    protected function urlAttributes($url, $index = null)
    {
        return [
            'crawler_id' => $this->crawler->id,
            'crawl_session_id' => $this->crawlSession->id,
            'crawl_url_id' => is_null($this->crawlingUrl) ? null : $this->crawlingUrl->id,
            'status' => CrawlUrl::STATUS_FRESH,
            'index' => is_null($index) ? $this->urlIndex($url) : $index,
            'url' => $url,
        ];
    }

    protected function urlIndex($url)
    {
        $parsed = parse_url($url);
        return sprintf(
            '%s.%s',
            md5($parsed['host']),
            md5(sprintf('%s?%s#%s', $parsed['path'] ?? '', $parsed['query'] ?? '', $parsed['hash'] ?? ''))
        );
    }

    protected function urlExtension($url)
    {
        return empty($extension = pathinfo($url, PATHINFO_EXTENSION)) ? 'html' : $extension;
    }

    protected function urlCreateDownloadFiler($url)
    {
        return ($filer = new Filer())->fromCreating(urldecode(pathinfo($url, PATHINFO_FILENAME)), $this->urlExtension($url), Helper::concatPath('crawled', $this->getName(), $filer->getDefaultToDirectory()));
    }

    protected function urlDownload($url)
    {
        $filer = $this->urlCreateDownloadFiler($url);
        return $this->client
            ->withOptions([
                'sink' => $filer->getOriginStorage()->getRealPath(),
            ])
            ->get($url)
            ->successful() ? $filer : null;
    }

    protected function urlStreamDownload($url)
    {
        $response = $this->client
            ->withOptions(['stream' => true])
            ->get($url);
        if ($response->successful()) {
            $filer = $this->urlCreateDownloadFiler($url)
                ->fEnableBinaryHandling()
                ->fStartWriting();
            $body = $response->getBody();
            while (!$body->eof()) {
                $filer->fWrite($body->read(1024 * 1024));
            }
            $filer->fClose();
            return $filer;
        }
        return null;
    }

    #region Single
    protected function crawlSingle(string $url)
    {
        $this->startingUrl = $url;
        return $this->bootstrapSingle()
            ->startCrawlingSingle()
            ->crawlingSingle()
            ->endCrawlingSingle();
    }

    protected function bootstrapSingle()
    {
        $this->bootstrap();
        $this->crawlingUrl = $this->storeUrlForCrawling($this->startingUrl);
        return $this;
    }

    protected function crawlingSingle()
    {
        return $this->crawlingUrl->status == CrawlUrl::STATUS_FRESH ?
            $this->crawling() : $this;
    }

    protected function startCrawlingSingle()
    {
        return $this;
    }

    protected function endCrawlingSingle()
    {
        return $this;
    }
    #endregion

    #region Continuously

    /**
     * @return CrawlUrl[]|Collection
     */
    protected function retrieveFreshCrawlingUrls()
    {
        return $this->crawlUrlRepository
            ->sort('id')
            ->limit($this->crawlingRetrieveMax)
            ->getFreshByCrawlerAlongIdWithDividedOrder(
                $this->crawler,
                $this->instanceCount,
                $this->instanceOrder
            );
    }

    /**
     * @return static
     */
    protected function queueFreshCrawlingUrls()
    {
        return $this->queueCrawlingUrls($this->retrieveFreshCrawlingUrls());
    }

    protected function crawlContinuously()
    {
        return $this->bootstrapContinuously()
            ->startCrawlingContinuously()
            ->crawlingContinuously()
            ->endCrawlingContinuously();
    }

    protected function bootstrapContinuously()
    {
        $this->bootstrap();
        if (!$this->hasCrawled()) {
            $this->storeUrlsForCrawling($this->startingUrls);
        }
        $this->crawlingUrls = new Collection();
        return $this;
    }

    protected function startCrawlingContinuously()
    {
        return $this->queueFreshCrawlingUrls();
    }

    protected function endCrawlingContinuously()
    {
        $this->crawlingUrls = null;
        $this->crawlingCount = 0;
        $this->crawlingUrl = null;
        $this->crawlingContent = null;
        return $this;
    }

    /**
     * @return CrawlUrl
     */
    protected function nextFreshCrawlingUrl()
    {
        if (!$this->crawlingUrls->count()) {
            $this->queueFreshCrawlingUrls();
        }
        return $this->nextCrawlingUrl();
    }

    protected function crawlingContinuously()
    {
        if (++$this->crawlingCount > $this->crawlingMax
            || is_null($this->crawlingUrl = $this->nextFreshCrawlingUrl())) {
            return $this;
        }

        return $this->crawling()->crawlingContinuously();
    }

    #endregion

    public function crawl(string $url = null)
    {
        return is_null($url) ?
            $this->crawlContinuously() : $this->crawlSingle($url);
    }
}