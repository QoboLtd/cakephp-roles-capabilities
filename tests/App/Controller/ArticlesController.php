<?php
namespace Qobo\RolesCapabilities\Test\App\Controller;

use Cake\Controller\Controller;
use Qobo\RolesCapabilities\CapabilityTrait;

class ArticlesController extends Controller
{
    use CapabilityTrait;

    public function index()
    {
    }
}
