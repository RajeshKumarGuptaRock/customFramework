<?php
namespace App\Controllers;
use Core\View; 
class HomeController {
  
    public function index() {
        View::render('home', ['title' => 'Home Page']);
    }
    public function about() {
        View::render('about',['title' => 'AboutUs Page']);
    }

    public function privacy() {
        View::render('privacy',['title' => 'Privacy Page']);
    }
    
}

