<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\MenuPrasmananController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaketPrasmananController;
use App\Http\Controllers\SubMenuPrasmananController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\KonsultasiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NotificationAdminController;
use App\Http\Controllers\NotificationPelangganController;
use App\Http\Controllers\PaketGalleryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TestimoniController;
use App\Http\Controllers\UserController;
use App\Models\Invoice;
use App\Models\PaketPrasmanan;
use App\Models\Testimoni;
use Twilio\Rest\Api\V2010\Account\Call\PaymentContext;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// verification email
Route::get('/email/verify/{id}',[AuthController::class, 'verify'])->name('verification.verify');
Route::get('/email/verify',[AuthController::class, 'notice'])->name('verification.notice');

// resend verification email
Route::get('/email/resend', [AuthController::class, 'resend'])->name('verification.resend')->middleware('jwt.verify');

// Login dan Register Pelanggan Routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Reset Password Pelanggan   
Route::post('forgot-password',[AuthController::class, 'forgotPassword']);
Route::post('reset-password',[AuthController::class, 'reset']);

// Admin Authentication Routes
Route::post('admin/login',[AdminAuthController::class, 'login']);

// Callback Midtrans
Route::post('/callbackMidtrans',[PaymentController::class,'callback']);

// Show Menu Routes
Route::get('show_menu/{id}',[PaketPrasmananController::class, 'showPaketMenu']);

// Kategori Routes
Route::apiResource('kategori', KategoriController::class)->only(['index','show']);

// Paket Prasmanan Routes
Route::apiResource('paket_prasmanan', PaketPrasmananController::class)->only(['index','show']);
Route::get('/paket-released',[PaketPrasmananController::class, 'getPaketReleased']);

// Search Paket
Route::post('/search',[PaketPrasmananController::class,'search']);

// Menu Prasmanan Routes
Route::apiResource('menu_prasmanan', MenuPrasmananController::class)->only('index');

// Sub Menu Routes
Route::apiResource('sub_menu_prasmanan', SubMenuPrasmananController::class)->only('index');

// Testimoni Routes
Route::get('/testimoni-acc',[TestimoniController::class, 'getTestimoniAcc']);

// Login with google
Route::post('/login-google',[AuthController::class, 'loginWithGoogle']);
Route::get('/google-callback',[AuthController::class, 'handleGoogleCallback']);

// Banner Routes
Route::get('/banner',[BannerController::class, 'index']);

// Konsultasi Routes
Route::post('/konsultasi',[KonsultasiController::class, 'store']);

// Manual Payment
Route::post('/manual_payment',[PaymentController::class, 'manualPayment']);

Route::middleware(['jwt.verify'])->group(function() {
    Route::get('/order/{id}',[OrderController::class, 'getOrderById']);
});

Route::middleware(['jwt.verify','pelanggan'])->group(function() {
    // Auth Pelanggan Routes
    Route::get('user',[AuthController::class, 'getAuthenticatedUser']);
    Route::post('/logout',[AuthController::class, 'logout']);

    // Cart Routes
    Route::apiResource('cart', CartController::class)->except('show');
    Route::post('/detail-cart', [CartController::class, 'showDetail']);

    // User Routes
    Route::post('user',[UserController::class, 'updateProfile']);
    Route::put('user/{id}',[UserController::class, 'changePassword']);

    // Routes Order
    Route::post('checkout',[OrderController::class, 'checkout']);
    Route::get('/myOrder',[OrderController::class,'getOrderbyUser']);
    Route::post('/cancel/{id}',[OrderController::class, 'cancelOrder']);
    
    // Payment Gateway
    Route::post('/snapToken',[PaymentController::class, 'paymentMidtransDP']);
    Route::post('/snapTokenLunas',[PaymentController::class, 'lunasPayment']);
    Route::post('/pending/{id}', [OrderController::class, 'pendingPayment']);
    
    // Testimoni Routes
    Route::post('/testimoni',[TestimoniController::class, 'store']);
    Route::get('/show-testimoni',[TestimoniController::class,'show']);
    
    // get Notifikasi
    Route::get('/notifikasi', [NotificationPelangganController::class,'getNotificationByUser']);
    Route::put('/set-notifikasi', [NotificationPelangganController::class,'setReadNotification']);
    Route::get('/amount-notifikasi',[NotificationPelangganController::class,'getAmountNotification']);

    // get Invoice
    Route::get('/invoice',[InvoiceController::class, 'getInvoiceByOrderId']);
});

Route::get('/laporan-penjualan', [LaporanController::class, 'penjualanReport']);
Route::post('admin/register',[AdminAuthController::class, 'register']);
Route::middleware(['jwt.verify','admin'])->group(function(){
    // Customer Routes
    Route::get('user/{id}',[UserController::class,'show']);
    
    // Auth Routes
    Route::get('admin/auth',[AdminAuthController::class,'getAdminAuth']);
    Route::post('/admin/logout',[AdminAuthController::class, 'logout']);
    
    // Kategori Route
    Route::apiResource('kategori',KategoriController::class)->only(['store','destroy']);
    Route::post('kategori/{id}',[KategoriController::class, 'updateKategori']);
    
    // Paket Prasmanan Routes
    Route::post('paket_prasmanan/{id}',[PaketPrasmananController::class, 'updatePaket']);
    Route::post('paket_prasmanan',[PaketPrasmananController::class, 'store']);
    Route::delete('paket_prasmanan/{id}',[PaketPrasmananController::class, 'destroy']);
    Route::post('/set_andalan/{id}',[PaketPrasmananController::class,'setPaketAndalan']);
    Route::post('/set_release/{id}',[PaketPrasmananController::class,'setPaketRelease']);
    
    // Gallery Paket Routes
    Route::get('/gallery_paket',[PaketGalleryController::class, 'index']);
    Route::post('/gallery_paket',[PaketGalleryController::class, 'store']);
    Route::post('/gallery_paket/{id}',[PaketGalleryController::class, 'update']);
    Route::get('/gallery_paket/{id}',[PaketGalleryController::class, 'show']);
    Route::delete('/gallery_paket/{id}',[PaketGalleryController::class, 'delete']);
    
    // Menu Prasmanan Routes
    Route::apiResource('menu_prasmanan', MenuPrasmananController::class)->except('index');
    // Sub Menu Prasmanan Routes
    Route::apiResource('sub_menu_prasmanan', SubMenuPrasmananController::class)->except('index');
    // insert menu paket prasmanan
    Route::post('add_menu/{id}',[PaketPrasmananController::class, 'insertMenu']);
    Route::delete('delete_menu/{id}',[PaketPrasmananController::class, 'deleteMenu']);
    Route::put('update_menu/{id}',[PaketPrasmananController::class, 'updateMenu']);
    

    // Admin Routes
    Route::get('admin',[AdminController::class, 'index']);
    Route::delete('admin/{id}',[AdminController::class, 'destroy']);
    Route::post('admin/update/{id}',[AdminController::class, 'updateProfile']);
    Route::delete('admin/update/{id}', [AdminController::class, 'deleteProfilePicture']);
    Route::put('admin/change-password/{id}',[AdminController::class,'changePassword']);
    
    // Pelanggan routes
    Route::get('/pelanggan',[UserController::class, 'index']);
    Route::delete('/pelanggan/{id}',[UserController::class, 'delete']);
    
    // Banner Routes
    Route::get('/banner/{id}',[BannerController::class, 'show']);
    Route::post('/banner',[BannerController::class, 'store']);
    Route::post('/banner/{id}',[BannerController::class, 'update']);
    Route::delete('/banner/{id}',[BannerController::class, 'destroy']);

    // Order Routes
    Route::get('/order',[OrderController::class,'index']);
    // Route::get('/order/{id}',[OrderController::class,'getOrderById']);
    Route::post('/updateStatusPesanan/{id}', [OrderController::class, 'updateStatusPesanan']);
    Route::post('/endProcess/{id}',[OrderController::class, 'endProcess']);
    Route::post('/process',[OrderController::class, 'processOrder']);
    
    // Laporan Routes
    Route::get('/paket-terlaris',[LaporanController::class, 'paket_terlaris_report']);
    Route::get('/laporan-pelanggan',[LaporanController::class, 'customerReport']);
    
    // Testimoni Routes
    Route::get('/testimoni',[TestimoniController::class, 'index']);
    Route::put('/testimoni/{id}',[TestimoniController::class, 'update']);
    Route::delete('/testimoni/{id}',[TestimoniController::class,'destroy']);

    // Dashboard ROutes
    Route::get('/dashboard',[DashboardAdminController::class, 'index']);
    
    // Konsultasi Routes
    Route::get('/konsultasi',[KonsultasiController::class, 'index']);
    Route::post('/admin-reply',[KonsultasiController::class, 'adminReply']);

    // Admin Notification Routes
    Route::get('/notifikasi-admin', [NotificationAdminController::class, 'getAdminNotification']);
    Route::get('/notifikasi-amount',[NotificationAdminController::class,'getAmountNotification']);
    Route::put('/set-notifikasi-admin', [NotificationAdminController::class, 'setReadNotification']);

    // get all invoices
    Route::get('/all-invoices',[InvoiceController::class,'getAllInvoice']);
});


Route::get('/print-invoice/{id}',[InvoiceController::class, 'printInvoice']);
Route::get('/print-pembayaran',[PaymentController::class, 'printDetailPayment']);