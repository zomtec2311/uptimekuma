<?php
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
        $this->mapper->delete($this->mapper->find($id));
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
