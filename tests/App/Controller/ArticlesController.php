<?php
namespace RolesCapabilities\Test\App\Controller;

use Cake\Controller\Controller;
use RolesCapabilities\EntityAccess\ControllerAuthorizeTrait;

class ArticlesController extends Controller
{
    use ControllerAuthorizeTrait;

    /**
     * @return \Cake\Http\Response|void|null
     */
    public function index()
    {
    }
}
