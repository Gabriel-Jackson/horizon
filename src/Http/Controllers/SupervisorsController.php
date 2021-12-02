<?php

namespace Laravel\Horizon\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\ErrorHandler\BufferingLogger;

class SupervisorsController extends Controller{


    public function index(Request $request, string $supervisor){
      $comand = $request->input('command');

      if(!$comand){
        return [
          "error" => true,
          "message" => "Nenhum comando selecionado",
        ];
      }

      if($comand == 'pause'){
        return Artisan::call("horizon:pause-supervisor", ["name" => $supervisor],  
          new StreamOutput(fopen(storage_path()."/logs/laravel.log","a",false))
        );

      }

      return Artisan::call('horizon:continue-supervisor', ["name" => $supervisor]);
    }
}

