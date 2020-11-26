<?php
declare(strict_types=1);

namespace RolesCapabilities\Test\App\Controller;

use Cake\Controller\Controller;
use RolesCapabilities\EntityAccess\AccessControlTrait;

class ArticlesController extends Controller
{
    use AccessControlTrait;

    /**
     * @return \Cake\Http\Response|void|null
     */
    public function index()
    {
    }
}
