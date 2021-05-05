<?php

namespace App\Console\Commands\Crawl;

use App\Console\Commands\Base\Command;
use App\Utils\HandledFiles\Filer\Filer;
use App\Utils\ValidationTrait;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;

abstract class CrawlCommand extends Command
{
    use ValidationTrait;

    /**
     * @var Factory
     */
    protected $client;

    public function __construct()
    {
        parent::__construct();

        $this->client = Http::getFacadeRoot();
    }

    protected function crawl($url, callable $callback)
    {
        $this->validatedData([
            'url' => $url,
        ], [
            'url' => 'url',
        ]);
        $response = $this->client->get($url);
        if ($response->successful()) {
            $callback($response->body());
        }
    }

    protected function download($url)
    {
        $extension = pathinfo($url, PATHINFO_EXTENSION);
        $filer = (new Filer())->fromCreating(null, $extension);
        $response = $this->client
            ->withOptions([
                'sink' => $filer->getOriginStorage()->getRealPath(),
            ])
            ->get($url);
        if (!$response->successful()) {
            $filer->delete();
        }
    }

    public function streamDownload($url)
    {
        $response = $this->client
            ->withOptions(['stream' => true])
            ->get($url);
        if ($response->successful()) {
            $extension = pathinfo($url, PATHINFO_EXTENSION);
            $filer = (new Filer())->fromCreating(null, $extension)
                                  ->fOpen('wb');
            $this->info(sprintf('Downloading [%s]', $url));
            $body = $response->getBody();
            while (!$body->eof()) {
                $filer->fWrite($body->read(1024 * 1024));
            }
            $this->info('Downloaded!');
            $filer->fClose();
        }
    }
}