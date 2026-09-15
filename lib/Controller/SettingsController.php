<?php
/**
 *
 * UptimeKuma APP (Nextcloud)
 *
 * @author Wolfgang Tödt <wtoedt@gmail.com>
 *
 * @copyright Copyright (c) 2026 Wolfgang Tödt
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=1);

namespace OCA\UptimeKuma\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\UseSession;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IConfig;
use OCP\IAppConfig;
use OCP\AppFramework\Http\DataResponse;
use Psr\Log\LoggerInterface;
use OCP\App\IAppManager;

class SettingsController extends Controller {
	private $config;
	private $l;
	public function __construct(
		IL10N $l,
		IConfig $config,
		IRequest $request,
		private readonly LoggerInterface $logger,
		private IAppManager $appManager,
		private IAppConfig $appConfig,
	) {
		parent::__construct('logcleaner', $request);
		$this->l = $l;
		$this->config = $config;
		$this->appManager = $appManager;
	}

	public function getparams(): DataResponse {

		return new DataResponse([
			'uptimekuma_entries_per_page_previously_jobs' => $this->appConfig->getValueInt('uptimekuma', 'uptimekuma_entries_per_page_previously_jobs',25),
			'uptimekuma_version' => $this->appManager->getAppVersion('uptimekuma', true),
		]);
	}

	public function setparam($who, $what): DataResponse {
		$what = intval($what);
       if ((int)$this->appConfig->setValueInt('uptimekuma', $who, $what));
			return new DataResponse([
            ]);
	}
}
