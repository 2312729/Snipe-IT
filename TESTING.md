# Chạy bộ kiểm thử

Tài liệu này dành cho các nhà phát triển muốn sửa đổi mã nguồn của ứng dụng và chạy bộ kiểm thử hiện có.

Trước khi bắt đầu, hãy làm theo [hướng dẫn cài đặt](README.md#cai-dat) để cài đặt ứng dụng cục bộ và đảm bảo bạn có thể mở được trong trình duyệt.

## Kiểm thử đơn vị và chức năng

Trước khi chạy bộ kiểm thử, hãy sao chép file môi trường mẫu cho kiểm thử và cập nhật giá trị phù hợp với môi trường của bạn:

```bash
cp .env.testing.example .env.testing
```

Dưới đây là cấu hình làm việc với SQLite trong bộ nhớ:

```dotenv
APP_ENV=testing
APP_DEBUG=true
APP_KEY=base64:glJpcM7BYwWiBggp3SQ/+NlRkqsBQMaGEOjemXqJzOU=
APP_URL=http://localhost:8000
APP_TIMEZONE='UTC'
APP_LOCALE=en-US

DB_CONNECTION=sqlite_testing
```

Nếu dùng MySQL, cập nhật các biến `DB_` cho phù hợp với cơ sở dữ liệu kiểm thử cục bộ:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE={}
DB_USERNAME={}
DB_PASSWORD={}
```

Bạn đã sẵn sàng chạy toàn bộ bộ kiểm thử từ terminal:

```bash
php artisan test
```

Để chạy từng file kiểm thử riêng lẻ, hãy truyền đường dẫn tới test cần chạy:

```bash
php artisan test tests/Unit/AccessoryTest.php
```

Một số test, ví dụ LDAP, được đánh dấu với annotation `@group`. Các nhóm này có thể chạy hoặc loại trừ bằng cờ `--group` hoặc `--exclude-group`:

```bash
php artisan test --group=ldap
php artisan test --exclude-group=ldap
```

Điều này hữu ích khi một tập hợp test đang thất bại vì thiếu extension như LDAP.

## Kiểm thử tích hợp với Testcontainers

Runner Node.js trong `tests/testcontainers/run.mjs` cấp phát một MySQL 8.0.36
riêng cho mỗi lần chạy, truyền host/cổng ánh xạ động và thông tin xác thực vào
tiến trình PHPUnit, sau đó dừng container. Runner cũng tạo cặp khóa RSA
Passport chỉ dùng trong tiến trình kiểm thử. Lệnh npm là entrypoint được dùng
trong local và workflow CI:

```powershell
npm ci
composer install
npm run test:integration:testcontainers
```

Yêu cầu PHP 8.2+, extension PDO MySQL, Node.js 18+, Docker Engine, Composer
development dependencies và npm dependencies. Trên Windows, runner tự dùng
`C:\xampp\php\php.exe` nếu tồn tại; nếu cài PHP ở đường dẫn khác, đặt biến
môi trường `PHP_BINARY` trước khi chạy. Runner chuyển tiếp đường dẫn test và
các tùy chọn PHPUnit:

```powershell
npm run test:integration:testcontainers -- --filter=asset
npm run test:integration:testcontainers -- tests/Feature/Checkouts/Api/AssetCheckoutTest.php
npm run test:integration:testcontainers -- tests/Feature/Checkouts/Api/AssetCheckoutTest.php --filter=test_asset_can_be_checked_out_and_checked_back_in_through_api
```

Runner giữ nguyên `.env.testing` hiện có. Nếu file này chưa tồn tại, runner tạo
bản tạm từ `.env.testing.example` và xóa bản tạm khi kết thúc. Database,
username, password và Passport keys không được lấy từ môi trường ứng dụng
thường ngày. Container được dừng sau khi PHPUnit hoàn tất; không chạy lệnh
integration này nếu Docker không sẵn sàng.

Test checkout rồi checkin tài sản đã được xác minh cục bộ bằng:

```powershell
npm run test:integration:testcontainers -- tests/Feature/Checkouts/Api/AssetCheckoutTest.php --filter=test_asset_can_be_checked_out_and_checked_back_in_through_api
```

Kết quả đã ghi nhận: PHPUnit 11.5.55 trên PHP 8.2.12, 1 test và 11
assertions pass trong 2 phút 8.481 giây. Kết quả này chỉ chứng minh test mục
tiêu, không phải toàn bộ Unit/Feature suite hoặc kết quả GitHub Actions. Runner
chỉ khởi chạy MySQL; queue, cache và dịch vụ ngoài DB nằm ngoài phạm vi hiện tại.
Chỉ xem lượt chạy là pass khi PHPUnit kết thúc với exit code 0.
