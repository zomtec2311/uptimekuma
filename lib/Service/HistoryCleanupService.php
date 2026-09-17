<?php
declare(strict_types=1);

namespace OCA\UptimeKuma\Service;

use OCA\UptimeKuma\Db\JobHistoryMapper;
use OCP\IConfig;
use OCP\IAppConfig;

class HistoryCleanupService {
    private const BATCH_SIZE = 5000;
    private const MAX_BATCHES_PER_RUN = 20;

    public function __construct(
        private JobHistoryMapper $history,
        private IConfig $config,
        private IAppConfig $appConfig,
    ) {}

    public function getRetentionDays(): int {
        $value = $this->appConfig->getValueInt('uptimekuma', 'uptimekuma_history_retention_days',365),

        if ($value < 0) {
            $value = 365;
        }

        return $value;
    }

    public function cleanup(): int {
        $days = $this->getRetentionDays();

        if ($days === 0) {
            return 0;
        }

        $cutoff = time() - ($days * 86400);
        $deleted = 0;

        for ($batch = 0; $batch < self::MAX_BATCHES_PER_RUN; $batch++) {
            $count = $this->history->deleteOlderThan($cutoff, self::BATCH_SIZE);
            $deleted += $count;

            if ($count < self::BATCH_SIZE) {
                break;
            }
        }

        return $deleted;
    }
}
