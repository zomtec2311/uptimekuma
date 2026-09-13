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

use OCA\UptimeKuma\Db\JobMapper;
use OCA\UptimeKuma\Service\JobService;
use OCA\UptimeKuma\Service\TokenService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\IRequest;
use OCP\IL10N;
use Psr\Log\LoggerInterface;

class ApiController extends Controller {
     private $l;

    public function __construct(IRequest $request,private TokenService $tokens,private JobMapper $jobs,private JobService $service, private readonly LoggerInterface $logger, IL10N $l,)
    {
        $this->l = $l;
        parent::__construct('uptimekuma',$request);
    }

    #[PublicPage]
    #[NoCSRFRequired]
    public function start(string $token):JSONResponse{
        return $this->run($token,'start');
    }

    #[PublicPage]
    #[NoCSRFRequired]
    public function resolve(string $token):JSONResponse{
        return $this->run($token,'resolve');
    }

    #[PublicPage]
    #[NoCSRFRequired]
    public function failed(string $token):JSONResponse{
        return $this->run($token,'failed');
    }

    private function run(string $raw,string $action):JSONResponse{
        try{
            $t=$this->tokens->authenticate($raw);
            $j=$this->jobs->find($t->getJobId());
            if(!$j->getEnabled())throw new \RuntimeException($this->l->t('Job is deactivated.'));
            if($action==='start'){
                $i=$this->service->start($j);
                return new JSONResponse(['ok'=>true,'state'=>$i->getState()]);
            }
            if($action==='resolve'){
                $this->service->resolve($j);
                return new JSONResponse(['ok'=>true,'state'=>'resolved']);
            }
            $msg=trim((string)$this->request->getParam('message',$this->l->t('Backup failed')));
            $i=$this->service->failed($j,$msg);
            return new JSONResponse(['ok'=>true,'state'=>$i->getState()]);
        }
        catch(\Throwable $e){
            return new JSONResponse(['ok'=>false,'error'=>$e->getMessage()],400);
        }
    }
}
