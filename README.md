# BE_Laravel - Dự án Quản lý Bán hàng

## Giới thiệu

Đây là dự án backend xây dựng bằng Laravel, cung cấp hệ thống quản lý bán hàng với nhiều chức năng hiện đại, bảo mật và dễ mở rộng. Dự án thể hiện khả năng thiết kế kiến trúc phần mềm, quản lý dữ liệu phức tạp, xác thực bảo mật và tích hợp các kỹ thuật tiên tiến.

## Kỹ thuật & Công nghệ sử dụng

- **Laravel Framework**: Sử dụng phiên bản mới, tận dụng Eloquent ORM, Service Layer, Observer, Middleware, Request Validation.
- **Authentication & Authorization**: Xác thực người dùng, phân quyền vai trò, xác minh email, quản lý token bảo mật.
- **Database Migration & Seeder**: Thiết kế, quản lý và khởi tạo dữ liệu với migration, seeder, factory.
- **RESTful API**: Xây dựng các API chuẩn REST cho quản lý sản phẩm, người dùng, đơn hàng, kho, nhà cung cấp, v.v.
- **Middleware**: Kiểm soát truy cập, xác thực token, kiểm tra trạng thái tài khoản, refresh token tự động.
- **Observer Pattern**: Theo dõi và xử lý sự kiện trên các model như Branch, Role.
- **Service Layer**: Tách logic nghiệp vụ ra khỏi controller, dễ bảo trì và mở rộng.
- **Unit Test & Feature Test**: Viết test kiểm thử chức năng xác thực, đăng ký, đặt lại mật khẩu, v.v.
- **Xử lý file & QR Code**: Quản lý hình ảnh sản phẩm, sinh mã QR cho sản phẩm.
- **Quản lý trạng thái (status)**: Cho các thực thể như User, Branch, Role, Supplier, v.v.
- **Bảo mật**: Sử dụng Laravel Sanctum cho API token, kiểm soát truy cập, xác thực email, đặt lại mật khẩu.

## Các chức năng nổi bật

- Quản lý người dùng: Đăng ký, đăng nhập, xác thực email, phân quyền, cập nhật thông tin, trạng thái tài khoản.
- Quản lý sản phẩm: CRUD sản phẩm, hình ảnh, thông số kỹ thuật, tag, sinh mã QR.
- Quản lý kho: Nhập kho, xuất kho, kiểm soát tồn kho, phiếu nhập kho, nhà cung cấp.
- Quản lý đơn hàng: Đặt hàng, thanh toán, vận chuyển, đánh giá sản phẩm.
- Quản lý chi nhánh, địa chỉ, khách hàng.
- Hệ thống phân quyền động, dễ mở rộng.
- API chuẩn REST, dễ tích hợp với frontend hoặc mobile app.
- Seed dữ liệu mẫu, dễ dàng khởi tạo môi trường phát triển.


## Hướng dẫn cài đặt

1. Clone dự án về máy:
   ```
   git clone <repo-url>
   ```
2. Cài đặt các package:
   ```
   composer install
   ```
3. Tạo file `.env` và cấu hình database.
4. Chạy migration và seed dữ liệu:
   ```
   php artisan migrate --seed
   ```
5. Khởi động server:
   ```
   php artisan serve
   ```
