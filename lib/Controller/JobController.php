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

use OCA\UptimeKuma\Db\{JobMapper, IncidentMapper};
use OCA\UptimeKuma\Service\{JobService, TokenService, JobHistoryService};
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Attribute\AdminRequired;
use OCP\IRequest;
use OCP\IL10N;

class JobController extends Controller {
    private $l;

    public function __construct(
        IRequest $request,
        private JobMapper $jobs,
        private IncidentMapper $incidents,
        private JobService $service,
        private TokenService $tokens,
        private JobHistoryService $history,
        IL10N $l,
    ) {
        $this->l = $l;
        parent::__construct('uptimekuma', $request);
    }

    #[AdminRequired]
    public function index(): JSONResponse {
        $out = [];
        foreach ($this->jobs->findAll() as $j) {
            $row = $this->ser($j);
            $i = $this->incidents->findLatestByJob($j->getId());
            if ($i) {
                $row['currentState'] = $i->getState();
                $row['currentIncidentId'] = $i->getId();
                $row['currentKumaIncidentId'] = $i->getKumaIncidentId();
                $row['currentErrorMessage'] = $i->getErrorMessage();
                $row['currentCreatedAt'] = $i->getCreatedAt();
                $row['currentResolvedAt'] = $i->getResolvedAt();
                $row['currenttokens'] = $this->mytokens($j->getId());
            } else {
                $row['currentState'] = 'none';
                $row['currentIncidentId'] = null;
                $row['currentKumaIncidentId'] = null;
                $row['currentErrorMessage'] = '';
                $row['currentCreatedAt'] = null;
                $row['currentResolvedAt'] = null;
                $row['currenttokens'] = 0;
            }
            $out[] = $row;
        }
        return new JSONResponse($out);
    }

    #[AdminRequired]
    public function create(): JSONResponse {
        try {
            return new JSONResponse($this->ser($this->service->create($this->request->getParams())));
        }
        catch (\Throwable $e) {
            return new JSONResponse(['error'=>$e->getMessage()],400);
        }
    }

    #[AdminRequired]
    public function update(int $id): JSONResponse {
        try {
            return new JSONResponse($this->ser($this->service->update($id,$this->request->getParams())));
        }
        catch (\Throwable $e) {
            return new JSONResponse(['error'=>$e->getMessage()],400);
        }
    }

    #[AdminRequired]
    public function destroy(int $id): JSONResponse {
        $this->jobs->delete($this->jobs->find($id));
        return new JSONResponse(['ok'=>true]);
    }

    #[AdminRequired]
    public function incidents(int $id): JSONResponse {
        return new JSONResponse(array_map(fn($i)=>[
            'id' => $i->getId(),'kumaIncidentId'=>$i->getKumaIncidentId(),'state'=>$i->getState(),
            'title' => $i->getTitle(),'content'=>$i->getContent(),'style'=>$i->getStyle(),
            'errorMessage' => $i->getErrorMessage(),'createdAt'=>$i->getCreatedAt(),'resolvedAt'=>$i->getResolvedAt()
        ],$this->incidents->findAllByJob($id)));
    }

    #[AdminRequired]
    public function start(int $id): JSONResponse {
        try {
            $job = $this->jobs->find($id);
            $i = $this->service->start($job);
            $this->history->log($id, 'start', $this->history->source('admin-test'), true);
            return new JSONResponse(['ok'=>true,'state'=>$i->getState(),'incidentId'=>$i->getId(),'kumaIncidentId'=>$i->getKumaIncidentId()]);
        } catch (\Throwable $e) {
            $this->history->log($id, 'start', $this->history->source('admin-test'), false, $e->getMessage());
            return new JSONResponse(['ok'=>false,'error'=>$e->getMessage()],400);
        }
    }

    #[AdminRequired]
    public function resolve(int $id): JSONResponse {
        try {
            $job = $this->jobs->find($id);
            $this->service->resolve($job);
            $this->history->log($id, 'resolve', $this->history->source('admin-test'), true);
            return new JSONResponse(['ok'=>true,'state'=>'resolved']);
        } catch (\Throwable $e) {
            $this->history->log($id, 'resolve', $this->history->source('admin-test'), false, $e->getMessage());
            return new JSONResponse(['ok'=>false,'error'=>$e->getMessage()],400);
        }
    }

    #[AdminRequired]
    public function failed(int $id): JSONResponse {
        try {
            $msg=trim((string)$this->request->getParam('message','Backup fehlgeschlagen'));
            if($msg==='')$msg='Backup fehlgeschlagen';
            $i=$this->service->failed($this->jobs->find($id),$msg);
            $this->history->log($id, 'failed', $this->history->source('admin-test'), true);
            return new JSONResponse(['ok'=>true,'state'=>$i->getState(),'incidentId'=>$i->getId(),'kumaIncidentId'=>$i->getKumaIncidentId()]);
        } catch (\Throwable $e) {
            $this->history->log($id, 'failed', $this->history->source('admin-test'), false, $e->getMessage());
            return new JSONResponse(['ok'=>false,'error'=>$e->getMessage()],400);
        }
    }

    #[AdminRequired]
    public function sync(int $id): JSONResponse {
        try {
            $incident = $this->service->sync($this->jobs->find($id));
            $this->history->log($id, 'sync', $this->history->source('admin-sync'), true);
            return new JSONResponse([
                'ok' => true,
                'state' => $incident->getState(),
                'incidentId' => $incident->getId(),
                'kumaIncidentId' => $incident->getKumaIncidentId(),
                'kuma' => $this->service->getLastSyncInfo(),
            ]);
        } catch (\Throwable $e) {
            $this->history->log($id, 'sync', $this->history->source('admin-sync'), false, $e->getMessage());
            return new JSONResponse(['ok'=>false,'error'=>$e->getMessage()],400);
        }
    }

    #[AdminRequired]
    public function tokens(int $id): JSONResponse {
        return new JSONResponse($this->tokens->list($id));
    }

    #[AdminRequired]
    public function mytokens(int $id): int {
        return count($this->tokens->list($id));
    }

    #[AdminRequired]
    public function createToken(int $id): JSONResponse {
        try {
            $p=$this->request->getParams();
            return new JSONResponse($this->tokens->create($id,(string)($p['description']??''),isset($p['expiresAt'])&&$p['expiresAt']!==''?(int)$p['expiresAt']:null));
        }
        catch (\Throwable $e) {
            return new JSONResponse(['error'=>$e->getMessage()],400);
        }
    }

    #[AdminRequired]
    public function deleteToken(int $id): JSONResponse {
        $this->tokens->delete($id);
        return new JSONResponse(['ok'=>true]);
    }

    private function ser($j): array {
        return ['id'=>$j->getId(),'name'=>$j->getName(),'instanceId'=>$j->getInstanceId(),'statusSlug'=>$j->getStatusSlug(),'title'=>$j->getTitle(),'content'=>$j->getContent(),'style'=>$j->getStyle(),'enabled'=>$j->getEnabled()];
    }
}
