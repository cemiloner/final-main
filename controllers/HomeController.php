<?php

namespace App\Controllers;

use App\Core\BaseController;

class HomeController extends BaseController
{
    /**
     * Displays the home page.
     */
    public function index(): void
    {
        // Load the home page view
        $this->view('home.index');
    }
} 