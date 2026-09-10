<?php
declare(strict_types=1);
namespace OCA\UptimeKuma\Service;
use OCA\UptimeKuma\Db\{Token,TokenMapper};use RuntimeException;
class TokenService {public function __construct(private TokenMapper $mapper){}
 public function create(int $jobId,string $description,?int $expiresAt):array{$raw='uk_'.bin2hex(random_bytes(32));$t=new Token();$t->setJobId($jobId);$t->setTokenHash(hash('sha256',$raw));$t->setDescription(trim($description)?:'API-Token');$t->setEnabled(true);$t->setLastUsedAt(null);$t->setCreatedAt(time());$t->setExpiresAt($expiresAt);$e=$this->mapper->insert($t);return['id'=>$e->getId(),'description'=>$e->getDescription(),'token'=>$raw,'expiresAt'=>$e->getExpiresAt()];}
 public function list(int $jobId):array{return array_map(fn(Token $t)=>['id'=>$t->getId(),'description'=>$t->getDescription(),'enabled'=>$t->getEnabled(),'lastUsedAt'=>$t->getLastUsedAt(),'createdAt'=>$t->getCreatedAt(),'expiresAt'=>$t->getExpiresAt()],$this->mapper->findByJob($jobId));}
 public function delete(int $id):void{$this->mapper->delete($this->mapper->find($id));}
 public function authenticate(string $raw):Token{$t=$this->mapper->findByHash(hash('sha256',$raw));if(!$t)throw new RuntimeException('Ungültiger API-Token.');if($t->getExpiresAt()!==null&&$t->getExpiresAt()<time())throw new RuntimeException('API-Token ist abgelaufen.');$t->setLastUsedAt(time());$this->mapper->update($t);return$t;}
}
