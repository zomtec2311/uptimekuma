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

namespace OCA\UptimeKuma\Service;

use OCA\UptimeKuma\Db\{Token,TokenMapper};
use OCP\IL10N;
use RuntimeException;

class TokenService {
     private $l;

    public function __construct(private TokenMapper $mapper, IL10N $l,){
        $this->l = $l;
    }

    public function create(int $jobId,string $description,?int $expiresAt):array{
        $raw='uk_'.bin2hex(random_bytes(32));
        $t=new Token();
        $t->setJobId($jobId);
        $t->setTokenHash(hash('sha256',$raw));
        $t->setDescription(trim($description)?:'API-Token');
        $t->setEnabled(true);$t->setLastUsedAt(null);
        $t->setCreatedAt(time());
        $t->setExpiresAt($expiresAt);
        $e=$this->mapper->insert($t);
        return['id'=>$e->getId(),'description'=>$e->getDescription(),'token'=>$raw,'expiresAt'=>$e->getExpiresAt()];
    }

    public function list(int $jobId):array{
        return array_map(fn(Token $t)=>['id'=>$t->getId(),'description'=>$t->getDescription(),'enabled'=>$t->getEnabled(),'lastUsedAt'=>$t->getLastUsedAt(),'createdAt'=>$t->getCreatedAt(),'expiresAt'=>$t->getExpiresAt()],$this->mapper->findByJob($jobId));
    }

    public function delete(int $id):void{
        $this->mapper->delete($this->mapper->find($id));
    }

    public function authenticate(string $raw):Token{
        $t=$this->mapper->findByHash(hash('sha256',$raw));
        if(!$t) throw new RuntimeException($this->l->t('Invalid API Token.'));
        if($t->getExpiresAt()!==null&&$t->getExpiresAt()<time()) throw new RuntimeException($this->l->t('API Token expired'));
        $t->setLastUsedAt(time());
        $this->mapper->update($t);
        return $t;
    }
}
