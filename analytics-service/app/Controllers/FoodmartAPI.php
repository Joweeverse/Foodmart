<?php
namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use App\Models\FoodmartAuth;
use App\Models\Foodmart;
class FoodmartAPI extends ResourceController{
public function index($seg1 = null, $seg2 = null){
$model = model(FoodmartAuth::class);
$email = $seg1;
$password = md5($seg2);
$cek = $model->getDataAuthentication($email, $password);
if ($cek == 0) {
return("Wrong Authentication!");
} else {
$model1 = model(Foodmart::class);
$data = ['message' => 'success',
'foodmart' => $model1->getDataFoodmart()];
return $this->respond($data,200);
}
}
}
