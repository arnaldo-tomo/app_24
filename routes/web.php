<?php
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\RestaurantController as AdminRestaurantController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Restaurant\RestaurantDashboardController;
use App\Http\Controllers\Restaurant\MenuController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->isRestaurantOwner()) {
        return redirect()->route('restaurant.dashboard');
    } else {
        return view('dashboard');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/restaurants', [AdminController::class, 'restaurants'])->name('restaurants.index');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders.index');
    Route::get('/customers', [AdminController::class, 'customers'])->name('customers.index');
    Route::get('/delivery-persons', [AdminController::class, 'deliveryPersons'])->name('delivery-persons.index');


    // Restaurant Management
    Route::resource('restaurants', AdminRestaurantController::class);
    Route::patch('/restaurants/{restaurant}/toggle-status', [AdminRestaurantController::class, 'toggleStatus'])->name('restaurants.toggle-status');
    Route::patch('/restaurants/{restaurant}/toggle-featured', [AdminRestaurantController::class, 'toggleFeatured'])->name('restaurants.toggle-featured');
        // Categories
        Route::resource('categories', CategoryController::class);
        Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
        Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');


    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('{payment}', [PaymentController::class, 'show'])->name('show');
        Route::post('{payment}/refund', [PaymentController::class, 'refund'])->name('refund');
        Route::get('analytics', [PaymentController::class, 'analytics'])->name('analytics');

        // Payment Methods
        Route::get('methods', [PaymentController::class, 'methods'])->name('methods');
        Route::get('methods/create', [PaymentController::class, 'createMethod'])->name('methods.create');
        Route::post('methods', [PaymentController::class, 'storeMethod'])->name('methods.store');
        Route::get('methods/{paymentMethod}/edit', [PaymentController::class, 'editMethod'])->name('methods.edit');
        Route::put('methods/{paymentMethod}', [PaymentController::class, 'updateMethod'])->name('methods.update');
        Route::delete('methods/{paymentMethod}', [PaymentController::class, 'destroyMethod'])->name('methods.destroy');
        Route::patch('methods/{paymentMethod}/toggle-status', [PaymentController::class, 'toggleMethodStatus'])->name('methods.toggle-status');
    });


        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportsController::class, 'index'])->name('index');
            Route::get('sales', [ReportsController::class, 'sales'])->name('sales');
            Route::get('restaurants', [ReportsController::class, 'restaurants'])->name('restaurants');
            Route::get('deliveries', [ReportsController::class, 'deliveries'])->name('deliveries');
            Route::get('customers', [ReportsController::class, 'customers'])->name('customers');
        });

        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::get('create', [SettingsController::class, 'create'])->name('create');
            Route::post('/', [SettingsController::class, 'store'])->name('store');
            Route::get('{setting}/edit', [SettingsController::class, 'edit'])->name('edit');
            Route::put('{setting}', [SettingsController::class, 'update'])->name('update');
            Route::delete('{setting}', [SettingsController::class, 'destroy'])->name('destroy');
            Route::post('bulk-update', [SettingsController::class, 'bulkUpdate'])->name('bulk-update');
        });
});







// Restaurant Owner Routes
Route::middleware(['auth'])->prefix('restaurant')->name('restaurant.')->group(function () {
    Route::get('/dashboard', [RestaurantDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [RestaurantDashboardController::class, 'orders'])->name('orders.index');
    Route::patch('/orders/{order}/status', [RestaurantDashboardController::class, 'updateOrderStatus'])->name('orders.update-status');
    Route::get('/orders/{id}/details', [RestaurantDashboardController::class, 'getOrderDetails'])
    ->name('restaurant.orders.details');
    // Menu Management
    Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
    Route::get('/menu/categories/create', [MenuController::class, 'createCategory'])->name('menu.categories.create');
    Route::post('/menu/categories', [MenuController::class, 'storeCategory'])->name('menu.categories.store');
    Route::get('/menu/categories/{menuCategory}/items/create', [MenuController::class, 'createItem'])->name('menu.items.create');
    Route::post('/menu/categories/{menuCategory}/items', [MenuController::class, 'storeItem'])->name('menu.items.store');
    Route::get('/menu/items/{menuItem}/edit', [MenuController::class, 'editItem'])->name('menu.items.edit');
    Route::post('/menu/items/{menuItem}', [MenuController::class, 'updateItem'])->name('menu.items.update');
    Route::patch('/menu/items/{menuItem}/toggle-availability', [MenuController::class, 'toggleItemAvailability'])->name('menu.items.toggle-availability');
    Route::post('categories/{menuCategory}/items', [MenuController::class, 'storeItem'])->name('menu.store-item');

    Route::get('categories/create', [MenuController::class, 'createCategory'])->name('create-category');
        Route::post('categories', [MenuController::class, 'storeCategory'])->name('store-category');
        Route::get('categories/{menuCategory}/edit', [MenuController::class, 'editCategory'])->name('edit-category');
        Route::put('categories/{menuCategory}', [MenuController::class, 'updateCategory'])->name('update-category');
        Route::delete('categories/{menuCategory}', [MenuController::class, 'destroyCategory'])->name('destroy-category');


        // Itens do menu
        Route::get('categories/{menuCategory}/items/create', [MenuController::class, 'createItem'])->name('create-item');
        // Route::post('categories/{menuCategory}/items', [MenuController::class, 'storeItem'])->name('store-item');
        Route::get('items/{menuItem}/edit', [MenuController::class, 'editItem'])->name('edit-item');
        Route::put('items/{menuItem}', [MenuController::class, 'updateItem'])->name('update-item');
        Route::delete('items/{menuItem}', [MenuController::class, 'destroyItem'])->name('destroy-item');
        Route::patch('items/{menuItem}/toggle-availability', [MenuController::class, 'toggleItemAvailability'])->name('toggle-item-availability');
});

require __DIR__.'/auth.php';
