<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('/onboarding/1', 'onboarding-1')->name('onboarding.1');
Route::view('/onboarding/2', 'onboarding-2')->name('onboarding.2');
Route::view('/onboarding/3', 'onboarding-3')->name('onboarding.3');
Route::view('/procesando', 'procesando')->name('procesando');
Route::view('/salida', 'salida')->name('salida');
Route::view('/traductor', 'traductor')->name('traductor');
Route::view('/orientacion', 'orientacion')->name('orientacion');
Route::view('/urgencia', 'urgencia')->name('urgencia');
Route::view('/ia', 'ia-dual')->name('ia.dual');

Route::post('/traductor/analizar', [App\Http\Controllers\TraductorController::class, 'analizar'])
     ->name('traductor.analizar');
Route::post('/orientacion/mensaje', [App\Http\Controllers\OrientacionController::class, 'mensaje'])
     ->name('orientacion.mensaje');
Route::post('/urgencia/consultar', [App\Http\Controllers\UrgenciaController::class, 'consultar'])
     ->name('urgencia.consultar');

Route::get('/test-ia', function () {
    try {
        $groq = new \App\Services\GroqService();
        return $groq->chat([
            ['role' => 'system', 'content' => 'Eres un orientador electoral mexicano. Solo respondes temas electorales.'],
            ['role' => 'user', 'content' => '¿Qué es un medio de impugnación electoral?']
        ]);
    } catch (\Exception $e) {
        return 'ERROR: ' . $e->getMessage();
    }
});

Route::get('/test-normativa', function() {
    $clasificador = new App\Services\ClasificadorService();
    $texto = "se le notifica la resolución del medio de impugnación interpuesto";
    $resultado = $clasificador->clasificar($texto);
    return response()->json($resultado);
});