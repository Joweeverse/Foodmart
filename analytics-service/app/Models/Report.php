<?php
namespace App\Models;
use CodeIgniter\Model;
class Report extends Model{
 protected $table = 'view_report_sales';
 public function getDataReport(){
 return $this->findAll();
 }
}
