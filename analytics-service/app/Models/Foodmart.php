<?php
namespace App\Models;
use CodeIgniter\Model;
class Foodmart extends Model{
protected $table = 'view_sales_fact_1997';
public function getDataFoodmart(){
return $this->findAll(1000);
}
}