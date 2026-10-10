# Stack và công cụ Snipe-IT

## Frontend là Laravel Mix, không phải Vite

- Assets được build bằng **Laravel Mix (webpack)** thông qua `webpack.mix.js`. Dự án không có `vite.config.js` và không có Vite manifest.
- Không có script `npm run build`. Bỏ qua hướng dẫn chung nói chạy nó. Dùng:
  - `npm run dev` — build phát triển
  - `npm run watch` — build liên tục khi có thay đổi
  - `npm run prod` — build production
- Nếu người dùng không thấy thay đổi trên giao diện, hãy yêu cầu chạy `npm run dev` hoặc `npm run watch`.

## Lớp UI

- **AdminLTE 2 / Bootstrap 3** cho Blade view. Không có Inertia.
- **Livewire v4** được cài đặt và dùng cho các widget nhỏ trong `app/Livewire` (ví dụ `Importer`, `CustomFieldEditor`, `LdapSettings`). Nó không phải tầng UI chính.
- Mặc định dùng Blade view + controller chuẩn. Chỉ dùng Livewire khi mở rộng component đã có hoặc khi người dùng yêu cầu.

## Biểu đồ

- **Chart.js v2.9.4**, nằm trong `public/js/dist/Chart.min.js`.
- Dùng **API v2**, không dùng v3. Ví dụ kiểu chart là `horizontalBar` (v3 đã bỏ nó theo `indexAxis`).
- Dùng `Helper::defaultChartColors()` cho bảng 10 màu.

## Lệnh thường dùng

```bash
# Xóa cache sau khi thay đổi config/route
php artisan optimize:clear

# Báo cáo coverage (do Laravel Herd phục vụ)
herd coverage
```
