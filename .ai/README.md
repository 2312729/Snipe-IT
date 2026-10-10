# Hướng dẫn AI Coding Assistant (`.ai/`)

Kho mã này đi kèm với hướng dẫn dành cho trợ lý lập trình AI, do [Laravel Boost](https://laravel.com/docs/13.x/boost) quản lý. Hướng dẫn được chia thành hai lớp.

- **Hướng dẫn luôn có sẵn** — được hợp nhất vào `CLAUDE.md` và `AGENTS.md` ở thư mục gốc. Nó được tải vào mọi phiên làm việc, bất kể bạn đang sửa file nào.
- **Quy tắc theo path** — nằm trong [`.ai/rules/`](rules), chỉ tải khi bạn sửa một file khớp với rule tương ứng.

Các file `CLAUDE.md` và `AGENTS.md` được commit vào kho nên mọi người đều có thể thấy hướng dẫn mà không cần chạy Boost.

## Cách hoạt động

Có hai bước riêng biệt xảy ra ở hai thời điểm khác nhau.

### 1. Sinh lại file — `php artisan boost:install --guidelines`

Boost kết hợp các nguồn bạn đang chỉnh sửa với hướng dẫn gốc để tái sinh các file đã commit.

### 2. AI sẽ tải những gì

- `CLAUDE.md` và `AGENTS.md` được tải ở mọi request.
- `.ai/rules/**` chỉ tải khi sửa file thuộc phạm vi rule.

## Những file được sinh tự động

Boost sẽ tự cập nhật các file sau, nên không nên sửa thủ công:

- `CLAUDE.md` và `AGENTS.md`
- `.ai/rules/index.md`
- `.ai/rules/boost/*.md`

Chỉnh sửa nguồn gốc, rồi chạy lại lệnh sinh file.

## Cách thay đổi hướng dẫn

- Đổi hướng dẫn luôn có sẵn: sửa file trong [`.ai/guidelines/`](guidelines), sau đó chạy `php artisan boost:install --guidelines`.
- Thêm rule theo path: dùng tool `record-rule` để mô tả phạm vi, tiêu đề và nội dung hướng dẫn.

## Cấu hình (`config/boost.php`)

File này được commit để mọi người sinh ra kết quả giống nhau. Hai cài đặt quan trọng là:

- `rules.scoped_guidelines` — khi bật, các block scoped được tách ra thành `.ai/rules/boost/`.
- `guidelines.exclude` — bỏ các block không muốn đưa vào kết quả compose.

## Tìm hiểu thêm

Xem tài liệu của [Laravel Boost](https://laravel.com/docs/13.x/boost).
