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

return ['routes'=>[
    ['name'=>'page#index','url'=>'/','verb'=>'GET'],
    ['name'=>'instance#index','url'=>'/api/instances','verb'=>'GET'],
    ['name'=>'instance#create','url'=>'/api/instances','verb'=>'POST'],
    ['name'=>'instance#update','url'=>'/api/instances/{id}','verb'=>'PUT'],
    ['name'=>'instance#destroy','url'=>'/api/instances/{id}','verb'=>'DELETE'],
    ['name'=>'instance#test','url'=>'/api/instances/{id}/test','verb'=>'POST'],
    ['name'=>'job#index','url'=>'/api/jobs','verb'=>'GET'],
    ['name'=>'job#create','url'=>'/api/jobs','verb'=>'POST'],
    ['name'=>'job#update','url'=>'/api/jobs/{id}','verb'=>'PUT'],
    ['name'=>'job#destroy','url'=>'/api/jobs/{id}','verb'=>'DELETE'],
    ['name'=>'job#incidents','url'=>'/api/jobs/{id}/incidents','verb'=>'GET'],
    ['name'=>'job#tokens','url'=>'/api/jobs/{id}/tokens','verb'=>'GET'],
    ['name'=>'job#start','url'=>'/api/jobs/{id}/start','verb'=>'POST'],
    ['name'=>'job#resolve','url'=>'/api/jobs/{id}/resolve','verb'=>'POST'],
    ['name'=>'job#failed','url'=>'/api/jobs/{id}/failed','verb'=>'POST'],
    ['name'=>'job#sync','url'=>'/api/jobs/{id}/sync','verb'=>'POST'],
    ['name'=>'job#createToken','url'=>'/api/jobs/{id}/tokens','verb'=>'POST'],
    ['name'=>'job#deleteToken','url'=>'/api/tokens/{id}','verb'=>'DELETE'],
    ['name'=>'api#start','url'=>'/api/external/{token}/start','verb'=>'GET'],
    ['name'=>'api#resolve','url'=>'/api/external/{token}/resolve','verb'=>'GET'],
    ['name'=>'api#failed','url'=>'/api/external/{token}/failed','verb'=>'GET'],
]];
