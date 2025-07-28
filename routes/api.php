<?php

use App\Http\Controllers\AIProductController;
use App\Http\Controllers\BranchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\loginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockReceiptController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AddressCustomerController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CheckoutController;
use App\Models\Order;

//                              ADMIN
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [loginController::class, 'login']);


//                              LOCATION
Route::prefix('location')->group(function () {
    Route::get('/provinces', [LocationController::class, 'provinces']);
    Route::get('/provinces/{province}/districts', [LocationController::class,'districts']);
    Route::get('/districts/{district}/wards', [LocationController::class,'wards']);
});

//Category
Route::get('/category', [CategoryController::class,'getAllCategory']);
Route::get('/category/tree', [CategoryController::class,'getCategoryTree']);
Route::get('/category/leaf-only', [CategoryController::class, 'getLeafOnly']);
Route::get('/category/{category}', [CategoryController::class,'getCategory']);
Route::get('/category/children/{childrenId}', [CategoryController::class,'getCategoryChildrenById']);

//Product
Route::get('/category/product/{parentId}', [CategoryController::class,'getAllProductByChildrenCategoryTree']);
Route::get('/product', [ProductController::class,'getAllProduct']);
Route::get('/product/hot-sale', [ProductController::class,'getProductHotSale']);
Route::get('/product/{product}', [ProductController::class,'getProduct']);

//Customer
Route::post('/customer/create', [CustomerController::class,'addNewCustomer']);
Route::post('/customer/login', [CustomerController::class,'login']);

// Route cho customer lấy thông tin chính mình (chỉ customer đăng nhập mới truy cập được)
Route::middleware(['customer.auth', 'check.customer.token'])->get('/customer/me', [CustomerController::class, 'getMyInfo']);
Route::middleware(['customer.auth', 'check.customer.token'])->get('/me/address/{address}', [AddressCustomerController::class, 'getAddressCustomer']);

Route::middleware(['customer.auth', 'check.customer.token'])->group(function () {
//Address
    Route::post('/customer/address/create', [AddressCustomerController::class, 'addNewAddressCustomer']);
    Route::put('/customer/address/edit/{address}', [AddressCustomerController::class, 'editAddressCustomer']);
    Route::delete('/customer/address/delete/{address}', [AddressCustomerController::class, 'deleteAddressCustomer']);

//Cart
    Route::post('/customer/cart/create', [CartController::class, 'addNewCart']);
    Route::get('/customer/cart/me', [CartController::class, 'getMyCart']);
    Route::put('/customer/cart/update-quantity/{cartItem}', [CartController::class, 'updateQuantity']);
    Route::delete('/customer/cart/delete/{cartItem}', [CartController::class, 'deleteItem']);

//Order
    Route::post('/customer/order/create', [OrderController::class, 'createNewOrder']);
    Route::get('/customer/my-order', [OrderController::class, 'getMyListOrder'] );
    Route::get('/customer/my-order/{order}', [OrderController::class, 'getOrderDetail'] );

//Checkout
    Route::post('/customer/checkout/process', [CheckoutController::class, 'processCheckout']);
});

// Payment callbacks và thank you page (không cần auth)
Route::post('/checkout/momo-callback', [CheckoutController::class, 'momoCallback']);
Route::get('/checkout/thank-you', [CheckoutController::class, 'thankYou']);
Route::get('/customer/checkout/thank-you', [CheckoutController::class, 'thankYou']); // Thêm route cũ để tương thích

Route::middleware(['auth:sanctum','check.token.expiration','refresh.token.expiration'])->group(function () {
    Route::post('/logout', [loginController::class, 'logout']);

    //                           BRANCH
    Route::prefix('/branch')->group(function () {
        Route::get('/', [BranchController::class, 'getAllBranch']);
        Route::get('/{branch}', [BranchController::class, 'getBranch']);
        Route::post('/create', [BranchController::class, 'addNewBranch']);
        Route::put('/edit/{branch}', [BranchController::class, 'editBranch']);
        Route::delete('/delete/{branch}', [BranchController::class, 'deleteBranch']);
        Route::put('/update-status/{branch}', [BranchController::class, 'updateBranchStatus']);
    });

    //                           ROLE
    Route::prefix('/role')->group(function () {
        Route::get('/', [RoleController::class,'getAllRole']);
        Route::get('/{role}', [RoleController::class,'getRole']);
        Route::post('/create',[RoleController::class,'addNewRole']);
        Route::put('/edit/{role}', [RoleController::class,'editRole']);
        Route::delete('/delete/{role}', [RoleController::class,'deleteRole']);
        Route::put('/update-status/{role}', [RoleController::class,'updateRoleStatus']);
    });

    //                           USER
    Route::prefix('/user')->group(function () {
        Route::get('/', [UserController::class,'getAllUser']);
        Route::get('/debug-status/{status?}', [UserController::class,'debugStatusFilter']);
        Route::get('/{user}', [UserController::class,'getUser']);
        Route::post('/create', [UserController::class,'addNewUser']);
        Route::put('/edit/{user}',[UserController::class,'editUser']);
        Route::delete('/delete/{user}', [UserController::class,'deleteUser']);
        Route::put('/update-status/{user}', [UserController::class,'updateUserStatus']);
    });

    //                           CATEGORY
    Route::prefix('/category')->group(function () {
        Route::post('/create', [CategoryController::class,'addNewCategory']);
        Route::post('/reorder', [CategoryController::class, 'reorder']);
        Route::put('edit/{category}', [CategoryController::class,'editCategory']);
        Route::delete('/delete/{category}', [CategoryController::class,'deleteCategory']);
    });

    //                           PRODUCT
    Route::prefix('/product')->group(function () {
        Route::post('/create', [ProductController::class,'addNewProduct']);
        Route::put('/edit/{product}', [ProductController::class,'editProduct']);
        Route::put('/update-status/{product}', [ProductController::class,'updateProductStatus']);
        Route::delete('/delete/{product}', [ProductController::class,'deleteProduct']);
    });

    //                           SUPPLIER
    Route::prefix('/supplier')->group(function () {
        Route::get('/', [SupplierController::class,'getAllSuppliers']);
        Route::get('/{supplier}', [SupplierController::class,'getSupplier']);
        Route::post('/create', [SupplierController::class,'addNewSupplier']);
        Route::put('/edit/{supplier}', [SupplierController::class,'editSupplier']);
        Route::put('/update-status/{supplier}', [SupplierController::class,'updateStatusSupplier']);
        Route::delete('/delete/{supplier}', [SupplierController::class,'deleteSupplier']);
    });

    //                           STOCK RECEIPT
    Route::prefix('/stock-receipt')->group(function () {
        Route::get('/', [StockReceiptController::class,'getAllStockReceipt']);
        Route::get('/statusCount', [StockReceiptController::class,'getAllStockReceiptStatus']);
        Route::get('/{stockReceipt}', [StockReceiptController::class,'getStockReceipt']);
        Route::post('/create', [StockReceiptController::class,'addNewStockReceipt']);
        Route::put('/approve/{stock}', [StockReceiptController::class,'approveStockReceipt']);
    });

    //                           CUSTOMER
    Route::prefix('/customer')->group(function () {
        Route::get('/', [CustomerController::class,'getAllCustomer']);
        Route::get('/{customer}', [CustomerController::class,'getCustomer']);
    });

    //                           ORDER
    Route::prefix('/order')->group(function () {
        Route::get('/', [OrderController::class, 'getAllOrder']);
        Route::get('/total-profit', [OrderController::class, 'gettTotalProfit']);
        Route::get('/{order}', [OrderController::class, 'getOrderDetailForAdmin']);
        Route::put('/change-status/{order}', [OrderController::class, 'changeOrderStatus']);
    });

    //                           ADDRESS CUSTOMER
    Route::prefix('/address-customer')->group(function () {
        Route::get('/', [AddressCustomerController::class,'getAllAddressCustomer']);
    });

});


// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });
