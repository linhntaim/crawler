<?php

namespace App\Console\Commands\Crawl;

use App\Console\Commands\Base\Command;
use App\Crawlers\CrawlBotFactory;

class CrawlCommand extends Command
{
    protected $signature = 'crawl {name} {--instance=} {--url=} {--max=1000} {--max-retrieve=1000}';

    protected function go()
    {
        $name = $this->argument('name');
        if ($crawlBot = CrawlBotFactory::factory($name, $this->optionOr('instance', config('crawl.instance_name')))) {
            $this->warn(sprintf('Crawl bot [%s] running...', $name));
            $crawlBot
                ->setCrawlingMax($this->option('max'))
                ->setCrawlingRetrieveMax($this->option('max-retrieve'))
                ->crawl($this->optionOr('url'));
            $this->info(sprintf('Session [%d] of crawler [%d] crawled!', $crawlBot->getCrawlSession()->id, $crawlBot->getCrawler()->id));
        }
        else {
            $this->error(sprintf('Crawl bot [%s] was unknown!', $name));
            $this->info('Available crawl bots:');
            foreach (CrawlBotFactory::availableCrawlBots() as $availableCrawlBot) {
                $this->warn(sprintf('- %s', $availableCrawlBot));
            }
        }
    }
}