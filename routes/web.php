<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Category Management
Route::get('/category', [App\Http\Controllers\CategoryController::class, 'index'])->name('category');
Route::post('/category-list', [App\Http\Controllers\CategoryController::class, 'categorylist'])->name('category_list');
Route::post('/category-add', [App\Http\Controllers\CategoryController::class, 'addCategory'])->name('createCategory');
Route::post('/category-delete', [App\Http\Controllers\CategoryController::class, 'deleteCategory'])->name('deleteCategory');
Route::post('/category-options', [App\Http\Controllers\CategoryController::class, 'categoryOptions'])->name('categoryOptions');
Route::get('/category-hierarchy', [App\Http\Controllers\CategoryController::class, 'showHierarchy'])->name('showHierarchy');

// Products Management
Route::get('/products', [App\Http\Controllers\ProductsController::class, 'index'])->name('product');
Route::post('/product-list', [App\Http\Controllers\ProductsController::class, 'productlist'])->name('product_list');
Route::post('/product-add', [App\Http\Controllers\ProductsController::class, 'addProduct'])->name('createProduct');
Route::post('/product-delete', [App\Http\Controllers\ProductsController::class, 'deleteProduct'])->name('deleteProduct');


