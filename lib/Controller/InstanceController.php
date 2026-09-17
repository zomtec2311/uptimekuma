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

use OCA\UptimeKuma\Db\InstanceMapper;
use OCA\UptimeKuma\Service\InstanceService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\AppFramework\Http\Attribute\AdminRequired;
use OCP\IL10N;

class InstanceController extends Controller {
    private $l;

    public function __construct(
        IRequest $request,
        private InstanceMapper $mapper,
        private InstanceService $service,
        IL10N $l,
    ) {
        $this->l = $l;
        parent::__construct('uptimekuma', $request);
    }

    #[AdminRequired]
    public function index(): JSONResponse {
        return new JSONResponse(array_map(fn($i) => [
            'id' => $i->getId(),
            'name' => $i->getName(),
            'url' => $i->getUrl(),
            'username' => $i->getUsername(),
        ], $this->mapper->findAll()));
    }

    #[AdminRequired]
    public function create(string $name, string $url, string $username, string $password): JSONResponse {
        return new JSONResponse($this->serialize($this->service->create($name, $url, $username, $password)));
    }

    #[AdminRequired]
    public function update(int $id, string $name, string $url, string $username, ?string $password = null): JSONResponse {
        return new JSONResponse($this->serialize($this->service->update($id, $name, $url, $username, $password)));
    }

    #[AdminRequired]
    public function destroy(int $id): JSONResponse {
        $this->service->delete($id);
        return new JSONResponse(['ok' => true]);
    }

    #[AdminRequired]
    public function test(int $id): JSONResponse {
        try {
            $this->service->test($this->mapper->find($id));
            return new JSONResponse(['ok' => true, 'message' => $this->l->t('Connection and login successful.')]);
        } catch (\Throwable $e) {
            return new JSONResponse(['ok' => false, 'message' => $e->getMessage()], 400);
        }
    }

    private function serialize($i): array {
        return [
            'id' => $i->getId(),
            'name' => $i->getName(),
            'url' => $i->getUrl(),
            'username' => $i->getUsername(),
        ];
    }
}
