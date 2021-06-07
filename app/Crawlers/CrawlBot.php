<?php

namespace App\Crawlers;

use App\Crawlers\ModelRepositories\CrawlDatumRepository;
use App\Crawlers\ModelRepositories\CrawledDatumRepository;
use App\Crawlers\ModelRepositories\CrawledUrlRepository;
use App\Crawlers\ModelRepositories\CrawlerInstanceRepository;
use App\Crawlers\ModelRepositories\CrawlerRepository;
use App\Crawlers\ModelRepositories\CrawlSessionRepository;
use App\Crawlers\ModelRepositories\CrawlSessionUrlRepository;
use App\Crawlers\ModelRepositories\CrawlUrlRepository;
use App\Crawlers\Models\CrawlDatum;
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
     * @var CrawlSessionUrlRepository
     */
    protected $crawlSessionUrlRepository;

    /**
     * @var CrawledUrlRepository
     */
    protected $crawledUrlRepository;

    /**
     * @var CrawlDatumRepository
     */
    protected $crawlDatumRepository;

    /**
     * @var CrawledDatumRepository
     */
    protected $crawledDatumRepository;

    /**
     * @var string[]|array
     */
    protected $startingUrls = [];

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
        $crawlSessionUrlRepositoryClass = $this->crawlSessionUrlRepositoryClass();
        $this->crawlSessionUrlRepository = new $crawlSessionUrlRepositoryClass();
        $crawledUrlRepositoryClass = $this->crawledUrlRepositoryClass();
        $this->crawledUrlRepository = new $crawledUrlRepositoryClass();
        $this->instance = $instance ?: config('crawl.instance_name');
    }

    public function getName()
    {
        return static::NAME;
    }

    protected abstract function crawlerRepositoryClass();

    protected abstract function crawlSessionRepositoryClass();

    protected abstract function crawlUrlRepositoryClass();

    protected abstract function crawlSessionUrlRepositoryClass();

    protected abstract function crawledUrlRepositoryClass();

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
        $this->crawlSessionUrlRepository->createWithSessionAndUrl($this->crawlSession, $this->crawlingUrl);

        if ($this->crawlingUrl->status != CrawlUrl::STATUS_COMPLETED) {
            $status = CrawlUrl::STATUS_COMPLETED;

            $canCrawlData = $this->canCrawlData();
            $canCrawlUrls = $this->canCrawlUrls();
            if ($canCrawlData || $canCrawlUrls) {
                if ($this->retrieveCrawlingContent()->hasCrawlingContent()) {
                    $this->updateCrawlingProgress(CrawlUrl::STATUS_CRAWLING);
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
                    $this->clearCrawlingContent();
                }
            }
            $this->updateCrawlingProgress($status);
        }
        return $this;
    }

    protected function updateCrawlingProgress($status = CrawlUrl::STATUS_COMPLETED)
    {
        $this->crawlUrlRepository->withModel($this->crawlingUrl)->updateStatus($status);
        return $this;
    }

    protected function retrieveCrawlingContent()
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

    #region Crawl Data
    protected function canCrawlData()
    {
        return true;
    }

    protected abstract function crawlData();

    protected function withDatumRepository($crawlDatumRepository, $crawledDatumRepository)
    {
        if (is_string($crawlDatumRepository)) {
            $crawlDatumRepository = new $crawlDatumRepository;
        }
        if ($crawlDatumRepository instanceof CrawlDatumRepository) {
            $this->crawlDatumRepository = $crawlDatumRepository;
        }
        if (is_string($crawledDatumRepository)) {
            $crawledDatumRepository = new $crawledDatumRepository;
        }
        if ($crawledDatumRepository instanceof CrawledDatumRepository) {
            $this->crawledDatumRepository = $crawledDatumRepository;
        }
        return $this;
    }

    /**
     * @param string $index
     * @param array $meta
     * @param array $additional
     * @return CrawlDatum|mixed
     * @throws
     */
    protected function storeData(string $index, array $meta = [], array $additional = [])
    {
        if (is_null($this->crawlDatumRepository)) {
            throw new AppException('Crawl datum repository was not set.');
        }
        if (is_null($this->crawledDatumRepository)) {
            throw new AppException('Crawled datum repository was not set.');
        }

        $crawlDatum = $this->crawlDatumRepository->createWithCrawler($this->crawler, $index, $meta, $additional);
        $this->crawledDatumRepository->createWithFromUrlAndDatum($this->crawlingUrl, $crawlDatum);
        return $crawlDatum;
    }

    protected function urlExtension($url)
    {
        return empty($extension = pathinfo($url, PATHINFO_EXTENSION)) ? 'html' : $extension;
    }

    protected function urlCreateFilerForDownloading($url)
    {
        return ($filer = new Filer())->fromCreating(
            urldecode(pathinfo($url, PATHINFO_FILENAME)),
            $this->urlExtension($url),
            Helper::concatPath('crawled', $this->getName(), $filer->getDefaultToDirectory())
        );
    }

    protected function urlDownload($url)
    {
        $filer = $this->urlCreateFilerForDownloading($url);
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
            $filer = $this->urlCreateFilerForDownloading($url)
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
    #endregion

    #region Crawl Urls
    protected function canCrawlUrls()
    {
        return true;
    }

    protected function crawlUrls()
    {
        $this->storeUrlsForCrawling(
            $this->filterUrlsForCrawling(
                $this->findUrlsForCrawling()
            )
        );
    }

    protected function findUrlsForCrawling()
    {
        if (whenPregMatchAll('/https?:\/\/(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])([^"\'\)\]]*|\/[^"\'\)\]]*)/i', $this->crawlingContent, $matches)) {
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
                if (preg_match('/^https?:\/\/([^\/]+)(\/.*)?$/i', $url, $matches) === 1) {
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
                if (preg_match('/^https?:\/\/[^\/]+\/(.+)$/i', $url, $matches) === 1) {
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
                if (preg_match('/^https?:\/\/[^\/]+\/.+\.([a-zA-Z0-9]+)(\?.*)?$/i', $url, $matches) === 1) {
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
        $crawlUrl = $this->crawlUrlRepository->createWithCrawler($this->crawler, $url);
        if (!is_null($this->crawlingUrl)) {
            $this->crawledUrlRepository->createWithFromUrlAndUrl($this->crawlingUrl, $crawlUrl);
        }
        return $crawlUrl;
    }
    #endregion

    #region Single
    protected function crawlSingle(string $url)
    {
        array_unshift($this->startingUrls, $url);
        return $this->bootstrapSingle()
            ->startCrawlingSingle()
            ->crawlingSingle()
            ->endCrawlingSingle();
    }

    protected function bootstrapSingle()
    {
        $this->bootstrap();
        return $this;
    }

    protected function startCrawlingSingle()
    {
        return $this;
    }

    protected function endCrawlingSingle()
    {
        $this->crawlingUrl = null;
        return $this;
    }

    protected function crawlingSingle()
    {
        $this->crawlingUrl = $this->storeUrlForCrawling(array_shift($this->startingUrls));
        return $this->crawling();
    }
    #endregion

    #region Continuously
    /**
     * @return CrawlUrl[]|Collection
     */
    protected function retrieveNotCompletedCrawlingUrls()
    {
        return $this->crawlUrlRepository
            ->sort('id')
            ->limit($this->crawlingRetrieveMax)
            ->getNotCompletedByCrawlerAlongIdWithDividedOrder(
                $this->crawler,
                $this->instanceCount,
                $this->instanceOrder
            );
    }

    /**
     * @return static
     */
    protected function queueNotCompletedCrawlingUrls()
    {
        return $this->queueCrawlingUrls($this->retrieveNotCompletedCrawlingUrls());
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
        return $this->queueNotCompletedCrawlingUrls();
    }

    protected function endCrawlingContinuously()
    {
        $this->crawlingUrls = null;
        $this->crawlingCount = 0;
        $this->crawlingUrl = null;
        return $this;
    }

    /**
     * @return CrawlUrl
     */
    protected function nextFreshCrawlingUrl()
    {
        if (!$this->crawlingUrls->count()) {
            $this->queueNotCompletedCrawlingUrls();
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