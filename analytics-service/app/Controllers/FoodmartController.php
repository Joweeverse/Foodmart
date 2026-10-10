<?php
namespace App\Controllers;
use App\Models\Foodmart;
class FoodmartController extends BaseController
{
    public function index()
    {
        if (session()->get('num_user') == '') {
            return redirect()->to('/login');
        }
        $model = model(Foodmart::class);
        $data['foodmart'] = $model->getDataFoodmart();
        return view('header', $data).view('menu').
        view('foodmart').view('footer');
    }
}

