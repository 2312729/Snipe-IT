<laravel-boost-guidelines>
=== .ai/snipe-it-architecture rules ===

# Kiến trúc Snipe-IT

## Các controller

Có hai nhánh controller song song:

- `app/Http/Controllers/` — controller web/UI trả về các Blade view.
- `app/Http/Controllers/Api/` — controller API trả về JSON, được datatables và select2 tiêu thụ.

Cả hai nhánh dùng cùng nhóm thư mục: `Assets/`, `Licenses/`, `Users/`, `Accessories/`, `Consumables/`, `Components/`, `Kits/`, `Account/`, `Auth/`.

## Transformer API

Mọi controller API đều phải trả dữ liệu qua một **Transformer** trong `app/Http/Transformers/`. Không được trả về raw model attributes trực tiếp từ controller API. `DatatablesTransformer` bao bọc dữ liệu phân trang.

```php
return (new AssetsTransformer)->transformAssets($assets, $assets->count());
```

## Phân quyền

- Tất cả quyền đều đi qua **Policies** trong `app/Policies/`.
- `CheckoutablePermissionsPolicy` là base cho assets, licenses, accessories và consumables.
- Các phương thức `checkout()` / `checkin()` chấp nhận `$item = null`, nên `@can('checkout', \App\Models\Asset::class)` hoạt động mà không cần instance.

## Route

- Route UI nằm trong `routes/web.php` **và** trong các file theo entity trong `routes/web/` (`hardware.php`, `users.php`, `licenses.php`, `accessories.php`, `components.php`, `consumables.php`, `kits.php`, `models.php`, `fields.php`, `locations.php`). Cần kiểm tra cả hai nơi khi thêm hoặc tìm route.
- Route API nằm trong `routes/api.php`.
- Breadcrumbs định nghĩa inline bằng `->breadcrumbs(fn (Trail $trail) => ...)` từ `tabuna/breadcrumbs`. **Mọi UI route nên có breadcrumb.**
- Một số tên route có dấu gạch chéo thay vì dấu chấm. Ví dụ: `route('reports/unaccepted_assets')`.

## Hỗ trợ đa công ty (FMCS)

`Setting::getSettings()->full_multiple_companies_support == '1'` kiểm soát filter theo công ty. Các endpoint select2 (`selectlist()`) nhận tham số `companyId`.

```php
if ((Setting::getSettings()->full_multiple_companies_support == '1') && ($request->filled('companyId'))) {
    $query->where('table.company_id', $request->input('companyId'));
}
```

Kết nối từ Blade bằng `data-company-id="{{ $user->company_id }}"`.

## Select2 AJAX Dropdowns

Dùng `class="js-data-ajax"` với `data-endpoint="hardware|licenses|consumables|..."`. `snipeit.js` tự khởi tạo và chuyển `data-company-id` thành `companyId`, `data-asset-status-type` thành `statusType` tới API.

## Luồng redirect sau checkout

Sau khi checkout, `Helper::getRedirectOption()` đọc `$request->redirect_option`. Để quay về người dùng được gán, form phải đặt:

- `redirect_option=target`
- `checkout_to_type=user`
- `assigned_user={{ $user->id }}`

## Biến view toàn cục

`$snipeSettings` được chia sẻ cho mọi view bởi `SettingsServiceProvider`. Dùng trực tiếp trong Blade — không được truyền `Setting::getSettings()` từ controller.

## Các helper chính (`app/Helpers/Helper.php`)

- `Helper::deployableStatusLabelList()` — nhãn trạng thái cho form checkout.
- `Helper::defaultChartColors(int $index = 0)` — bảng màu 10 màu.
- `Helper::getRedirectOption($request, $id, $table, $item_id = null)` — logic redirect sau checkout.

=== .ai/snipe-it-stack rules ===

# Stack và công cụ Snipe-IT

## Frontend là Laravel Mix, không phải Vite

- Assets được build bằng **Laravel Mix (webpack)** thông qua `webpack.mix.js`. Dự án không có `vite.config.js` và không có Vite manifest.
- Không có script `npm run build`. Bỏ qua hướng dẫn chung kể cả khi nó nói chạy `npm run build`. Dùng:
  - `npm run dev` — build phát triển
  - `npm run watch` — build liên tục khi có thay đổi
  - `npm run prod` — build production
- Nếu người dùng không thấy thay đổi trên UI, hãy yêu cầu chạy `npm run dev` hoặc `npm run watch`.

## Lớp UI

- **AdminLTE 2 / Bootstrap 3** cho Blade view. Không có Inertia.
- **Livewire v4** được cài đặt và dùng cho các widget rời rạc trong `app/Livewire` (ví dụ `Importer`, `CustomFieldEditor`, `LdapSettings`). Nó không phải tầng UI chính.
- Mặc định dùng Blade view và controller chuẩn. Chỉ dùng Livewire khi mở rộng component Livewire hiện có hoặc khi người dùng yêu cầu.

## Biểu đồ

- **Chart.js v2.9.4**, nằm trong `public/js/dist/Chart.min.js`.
- Dùng **API v2**, không dùng v3. Ví dụ kiểu chart là `horizontalBar` (v3 đã bỏ nó theo hướng `indexAxis`).
- Dùng `Helper::defaultChartColors()` cho bảng 10 màu.

## Lệnh thường dùng

```bash
# Xóa cache sau khi thay đổi config/route
php artisan optimize:clear

# Báo cáo coverage (do Laravel Herd phục vụ)
herd coverage
```

=== foundation rules ===

# Hướng dẫn Laravel Boost

Các hướng dẫn Laravel Boost do maintainer Laravel biên soạn cho ứng dụng này. Nên tuân thủ chặt chẽ để có trải nghiệm tốt nhất khi làm việc với Laravel.

## Bối cảnh nền tảng

Đây là ứng dụng Laravel chạy trên PHP 8.2. Bạn là chuyên gia trong hệ sinh thái Laravel. Luôn dùng API phù hợp với phiên bản gói đang cài — đừng suy đoán theo phiên bản cũ.

Trước khi phụ thuộc vào API của một gói, hãy xác nhận phiên bản cài đặt của nó:
- PHP packages: chạy `composer show --direct` hoặc `composer show <vendor/package>`.
- JS packages: kiểm tra `package.json`.

## Kích hoạt skill

Dự án có các skill theo domain trong `**/skills/**`. Bạn phải kích hoạt skill phù hợp khi làm việc trong phần đó.

## Quy ước

- Tuân thủ quy ước hiện có trong ứng dụng.
- Sử dụng tên biến và phương thức mô tả rõ, ví dụ `isRegisteredForDiscounts` thay vì `discount()`.
- Tìm component có sẵn để tái sử dụng trước khi viết mới.

## Kiểm tra bằng script

- Không tạo script kiểm tra riêng khi tests đã cover chức năng đó và chứng minh chúng hoạt động.

## Cấu trúc ứng dụng

- Giữ nguyên cấu trúc thư mục hiện có, không tạo thư mục gốc mới nếu không cần.
- Không thay đổi dependency của ứng dụng nếu chưa được phê duyệt.

## Bundling frontend

- Nếu không thấy thay đổi UI phản ánh trên giao diện, có thể cần chạy `npm run build`, `npm run dev` hoặc `composer run dev`.

## Tài liệu

- Chỉ tạo tệp documentation khi người dùng yêu cầu rõ ràng.

## Trả lời

- Nên ngắn gọn, tập trung vào điều quan trọng, không giải thích những thứ hiển nhiên.

=== boost rules ===

# Laravel Boost

## Công cụ

- Laravel Boost là server MCP chuyên dụng cho ứng dụng này. Nên ưu tiên dùng Boost thay vì các cách thủ công.
- Dùng `database-query` để chạy query readonly thay vì dùng raw SQL trong tinker.
- Dùng `database-schema` để kiểm tra schema trước khi viết migration hoặc model.
- Dùng `get-absolute-url` để xác định scheme, domain và port chính xác trước khi chia sẻ URL.
- Dùng `browser-logs` để đọc logs lỗi trên browser.

## Tìm tài liệu (QUAN TRỌNG)

- Dùng `search-docs` trước khi thay đổi phụ thuộc vào API Laravel, behavior hoặc cấu hình cụ thể.
- Chỉ dùng những kết quả đáng tin cậy và tái sử dụng lại nếu phù hợp.

## Quy ước

- Học cách làm theo Laravel; ưu tiên các API đúng với phiên bản đang dùng.
- Dùng route tên, không dùng URL hardcode khi có thể.

</laravel-boost-guidelines>
