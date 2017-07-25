<?php
namespace RolesCapabilities\Test\App\Controller;

use Cake\Controller\Controller;
use RolesCapabilities\CapabilityTrait;

class ArticlesController extends Controller
{
    use CapabilityTrait;

    public function index()
    {
    }
}
