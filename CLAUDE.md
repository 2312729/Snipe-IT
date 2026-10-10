# Hướng dẫn cho Claude Code

Đây là tài liệu hướng dẫn cho trợ lý lập trình AI, được quản lý bằng [Laravel Boost](https://laravel.com/docs/13.x/boost). Dự án tích hợp hai lớp hướng dẫn.

- **Hướng dẫn luôn có sẵn** — được hợp nhất vào `CLAUDE.md` và `AGENTS.md` ở thư mục gốc.
- **Quy tắc theo đường dẫn** — nằm trong `.ai/rules/` và chỉ tải khi bạn chỉnh sửa file phù hợp với path.

Mục tiêu là giúp AI hiểu kiến trúc ứng dụng, quy ước code và cách tương tác hợp lý với dự án Snipe-IT.

## Cập nhật và sinh lại

Các file `.ai/guidelines/*.md` là nguồn gốc. Sau khi chỉnh sửa, hãy chạy:

```bash
php artisan boost:install --guidelines
```

Để tái sinh `CLAUDE.md`, `AGENTS.md` và các file rule tương ứng.

## Lưu ý quan trọng

- Không sửa các file generated thủ công.
- Chỉnh sửa file nguồn trong `.ai/guidelines/` hoặc `.ai/rules/` thay vì sửa trực tiếp `CLAUDE.md`/`AGENTS.md`.
- Tuân thủ kiến trúc hiện có của Snipe-IT và các convention của Laravel.
