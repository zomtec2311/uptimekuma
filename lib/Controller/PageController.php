<?php
declare(strict_types=1);

namespace OCA\UptimeKuma\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\AppFramework\Http\Attribute\AdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;

class PageController extends Controller {
    public function __construct(IRequest $request) {
        parent::__construct('uptimekuma', $request);
    }

    #[AdminRequired]
    #[NoCSRFRequired]
    public function index(): TemplateResponse {
        return new TemplateResponse('uptimekuma', 'main');
    }
}
