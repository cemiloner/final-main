<?php

namespace App\Controllers;

use App\Core\BaseController;

class MainMenuController extends BaseController
{
    public function index(): void
    {
        $this->view('mainmenu', ['pageTitle' => 'Ana Menü'], 'main');
    }
}
