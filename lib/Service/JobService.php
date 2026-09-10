<?php
declare(strict_types=1);
namespace OCA\UptimeKuma\Service;
use OCA\UptimeKuma\Db\{Job,JobMapper,InstanceMapper,Incident,IncidentMapper};use RuntimeException;
class JobService { private array $lastSyncInfo = []; public function getLastSyncInfo(): array { return $this->lastSyncInfo; } public function __construct(private JobMapper $jobs,private InstanceMapper $instances,private IncidentMapper $incidents,private KumaClient $kuma){}
 public function create(array $d):Job{$this->validate($d);$now=time();$j=new Job();$j->setName(trim($d['name']));$j->setInstanceId((int)$d['instanceId']);$j->setStatusSlug(trim($d['statusSlug']));$j->setTitle(trim($d['title']));$j->setContent((string)$d['content']);$j->setStyle(trim($d['style']??'warning')?:'warning');$j->setEnabled((bool)($d['enabled']??true));$j->setCreatedAt($now);$j->setUpdatedAt($now);return$this->jobs->insert($j);}
 public function update(int $id,array $d):Job{$this->validate($d);$j=$this->jobs->find($id);$j->setName(trim($d['name']));$j->setInstanceId((int)$d['instanceId']);$j->setStatusSlug(trim($d['statusSlug']));$j->setTitle(trim($d['title']));$j->setContent((string)$d['content']);$j->setStyle(trim($d['style']??'warning')?:'warning');$j->setEnabled((bool)($d['enabled']??true));$j->setUpdatedAt(time());return$this->jobs->update($j);}
 public function start(Job $j):Incident{$active=$this->incidents->findActiveByJob($j->getId());if($active)return$active;$i=$this->instances->find($j->getInstanceId());try{$kid=$this->kuma->start($i->getUrl(),$i->getUsername(),$i->getPasswordEncrypted(),$j->getStatusSlug(),$j->getTitle(),$j->getContent(),$j->getStyle());$e=new Incident();$e->setJobId($j->getId());$e->setKumaIncidentId($kid);$e->setTitle($j->getTitle());$e->setContent($j->getContent());$e->setStyle($j->getStyle());$e->setState('active');$e->setErrorMessage('');$e->setCreatedAt(time());$e->setResolvedAt(null);return$this->incidents->insert($e);}catch(\Throwable $e){throw new RuntimeException('Start fehlgeschlagen: '.$e->getMessage(),0,$e);}}
 public function resolve(Job $j):void{$active=$this->incidents->findOpenByJob($j->getId());if(!$active)return;$i=$this->instances->find($j->getInstanceId());$this->kuma->resolve($i->getUrl(),$i->getUsername(),$i->getPasswordEncrypted(),$j->getStatusSlug(),$active->getKumaIncidentId());$active->setState('resolved');$active->setResolvedAt(time());$this->incidents->update($active);}
 public function failed(Job $j,string $message):Incident{$active=$this->incidents->findActiveByJob($j->getId());if(!$active){$active=$this->start($j);} $i=$this->instances->find($j->getInstanceId());$this->kuma->update($i->getUrl(),$i->getUsername(),$i->getPasswordEncrypted(),$j->getStatusSlug(),$active->getKumaIncidentId(),$j->getTitle(),$message,'danger');$active->setState('failed');$active->setErrorMessage($message);$this->incidents->update($active);return$active;}
 public function sync(Job $j): Incident {
  $i=$this->instances->find($j->getInstanceId());
  $history=$this->kuma->getIncidentHistory($i->getUrl(),$i->getUsername(),$i->getPasswordEncrypted(),$j->getStatusSlug());
  $local=$this->incidents->findLatestByJob($j->getId());
  $remote=null;

  // Prefer the currently active Kuma incident with the exact job title.
  // This is important when a new incident was manually created in Kuma:
  // the previous local Kuma incident may already be resolved and therefore
  // must not prevent us from finding the new incident.
  foreach($history as $incident){
   if(is_array($incident) && (string)($incident['title']??'') === $j->getTitle() && $this->remoteIncidentIsActive($incident)){
    $remote=$incident;
    break;
   }
  }

  // If there is no active title match, follow the incident currently linked
  // to the local record. This correctly synchronizes a resolved incident.
  if($remote===null && $local){
   foreach($history as $incident){
    if(is_array($incident) && isset($incident['id']) && (int)$incident['id']===$local->getKumaIncidentId()){
     $remote=$incident;
     break;
    }
   }
  }

  // Finally, if there is no local incident yet, accept an incident with the
  // exact job title even when it is already resolved.
  if($remote===null && !$local){
   foreach($history as $incident){
    if(is_array($incident) && (string)($incident['title']??'') === $j->getTitle()){
     $remote=$incident;
     break;
    }
   }
  }

  if($remote===null){
   if($local){
    throw new RuntimeException('Der zugehörige Incident wurde in Kuma nicht gefunden. Die lokale Anzeige wurde nicht verändert.');
   }
   throw new RuntimeException('Kein Incident für diesen Job gefunden. Bei manuell angelegten Incidents muss der Titel exakt dem Job-Titel entsprechen.');
  }

  $remoteId=(int)($remote['id']??0);
  $localMatchesRemote=$local && $local->getKumaIncidentId()===$remoteId;

  // A different active Kuma incident means a new incident cycle. Never
  // overwrite the previous resolved local record; create a new one instead.
  if(!$local || !$localMatchesRemote){
   $local=new Incident();
   $local->setJobId($j->getId());
   $local->setKumaIncidentId($remoteId);
   $local->setCreatedAt(isset($remote['createdDate']) ? (strtotime((string)$remote['createdDate']) ?: time()) : time());
   $local->setResolvedAt(null);
   $local->setErrorMessage('');
  }

  $this->lastSyncInfo = [
   'id' => $remote['id'] ?? null,
   'active' => $remote['active'] ?? null,
   'activeType' => get_debug_type($remote['active'] ?? null),
   'pin' => $remote['pin'] ?? null,
   'style' => $remote['style'] ?? null
  ];

  $local->setTitle((string)($remote['title']??$local->getTitle()));
  $local->setContent((string)($remote['content']??$local->getContent()));
  $local->setStyle((string)($remote['style']??$local->getStyle()));
  $active=$this->remoteIncidentIsActive($remote);
  if($active){
   $style=$local->getStyle();
   $local->setState($style==='danger'?'failed':'active');
   $local->setErrorMessage($style==='danger'?$local->getContent():'');
   $local->setResolvedAt(null);
  }else{
   $local->setState('resolved');
   $local->setErrorMessage('');
   $updated=$remote['lastUpdatedDate']??$remote['createdDate']??null;
   $local->setResolvedAt($updated ? (strtotime((string)$updated) ?: time()) : time());
  }

  return $local->getId() ? $this->incidents->update($local) : $this->incidents->insert($local);
 }
 private function remoteIncidentIsActive(array $incident): bool {
  if(!array_key_exists('active',$incident)) return false;
  $value=$incident['active'];
  if(is_bool($value)) return $value;
  if(is_int($value) || is_float($value)) return (int)$value === 1;
  $normalized=strtolower(trim((string)$value));
  return in_array($normalized,['1','true','yes','on'],true);
 }
 private function validate(array $d):void{foreach(['name','instanceId','statusSlug','title'] as $k)if(!isset($d[$k])||(string)$d[$k]==='')throw new RuntimeException('Pflichtfeld fehlt: '.$k);if(!$this->instances->find((int)$d['instanceId']))throw new RuntimeException('Kuma-Instanz nicht gefunden.');}
}
