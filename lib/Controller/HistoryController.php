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

use OCA\UptimeKuma\Service\JobHistoryService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Attribute\AdminRequired;
use OCP\IRequest;

class HistoryController extends Controller {
    public function __construct(
        IRequest $request,
        private JobHistoryService $history
    ) {
        parent::__construct('uptimekuma', $request);
    }

    #[AdminRequired]
    public function index(): JSONResponse {
        $jobId = (int)$this->request->getParam('jobId', 0);
        $jobId = $jobId > 0 ? $jobId : null;
        $action = trim((string)$this->request->getParam('action', ''));
        $source = trim((string)$this->request->getParam('source', ''));
        $successParam = $this->request->getParam('success', '');
        $success = $successParam === '' ? null : in_array((string)$successParam, ['1', 'true'], true);
        $search = trim((string)$this->request->getParam('search', ''));
        $sort = trim((string)$this->request->getParam('sort', 'createdAt'));
        $direction = trim((string)$this->request->getParam('direction', 'DESC'));
        $limit = (int)$this->request->getParam('limit', 50);
        $offset = (int)$this->request->getParam('offset', 0);

        return new JSONResponse($this->history->list(
            $jobId,
            $action,
            $source,
            $success,
            $search,
            $sort,
            $direction,
            $limit,
            $offset
        ));
    }
}
