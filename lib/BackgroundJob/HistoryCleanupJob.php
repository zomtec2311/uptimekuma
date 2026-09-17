<?php
declare(strict_types=1);

namespace OCA\UptimeKuma\BackgroundJob;

use OCA\UptimeKuma\Service\HistoryCleanupService;
use OCP\BackgroundJob\TimedJob;
use Psr\Log\LoggerInterface;

class HistoryCleanupJob extends TimedJob {
    private LoggerInterface $logger;

    public function __construct(
        private HistoryCleanupService $cleanupService,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->setInterval(86400);
        $this->setAllowParallelRuns(false);
        $this->logger = $logger;
    }

    protected function run($argument): void {
        $deletedjobs = $this->cleanupService->cleanup();
        if ( $deletedjobs > 0 ) $this->logger->info("UptimeKuma background job executed. $deletedjobs previously executed Job(s) deleted from history database");
    }
}
