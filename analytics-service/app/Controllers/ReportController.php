<?php
namespace App\Controllers;
use App\Models\Report;
class ReportController extends BaseController{
 public function index(){
 if (session()->get('num_user') == '') {
 return redirect()->to('/login');}
 $model = model(Report::class);
 $data['report'] = $model->getDataReport();
 return view('header', $data).view('menu').
 view('report').view('footer');
 }
}
