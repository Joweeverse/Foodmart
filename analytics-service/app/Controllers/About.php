<?php
namespace App\Controllers;
class About extends BaseController
{
public function index()
{
    if (session()->get('num_user') == '') {
        return redirect()->to('/login');
    }
    return view('header').view('menu').view('about').view('footer');
}

}

