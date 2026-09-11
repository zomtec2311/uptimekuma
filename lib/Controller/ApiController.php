<?php
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
