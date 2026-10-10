# Kiến trúc Snipe-IT

## Các controller

Có hai nhánh controller song song:

- `app/Http/Controllers/` — controller giao diện web trả về Blade view.
- `app/Http/Controllers/Api/` — controller REST API trả về JSON, được datatables và select2 tiêu thụ.

Cả hai nhánh dùng cùng nhóm thư mục: `Assets/`, `Licenses/`, `Users/`, `Accessories/`, `Consumables/`, `Components/`, `Kits/`, `Account/`, `Auth/`.

## Transformer API

Mọi controller API đều trả dữ liệu qua một **Transformer** trong `app/Http/Transformers/`. Không trả raw model attributes trực tiếp từ API controller. `DatatablesTransformer` bọc kết quả phân trang.

```php
return (new AssetsTransformer)->transformAssets($assets, $assets->count());
```

## Phân quyền

- Tất cả xác thực quyền đi qua **Policies** trong `app/Policies/`.
- `CheckoutablePermissionsPolicy` là base cho asset, license, accessory và consumable.
- Phương thức `checkout()` / `checkin()` chấp nhận `$item = null`, nên `@can('checkout', \App\Models\Asset::class)` hoạt động mà không cần instance.

## Route

- Route UI nằm trong `routes/web.php` **và** các file theo entity trong `routes/web/` (`hardware.php`, `users.php`, `licenses.php`, `accessories.php`, `components.php`, `consumables.php`, `kits.php`, `models.php`, `fields.php`, `locations.php`). Kiểm tra cả hai nơi khi thêm hoặc tìm route.
- Route API nằm trong `routes/api.php`.
- Breadcrumbs được định nghĩa inline bằng `->breadcrumbs(fn (Trail $trail) => ...)` từ `tabuna/breadcrumbs`. **Mọi UI route nên có breadcrumb.**
- Một số tên route chứa dấu gạch chéo thay vì dấu chấm. Ví dụ: `route('reports/unaccepted_assets')`.

## Hỗ trợ đa công ty (FMCS)

`Setting::getSettings()->full_multiple_companies_support == '1'` là điều kiện lọc theo công ty. Endpoint select2 (`selectlist()`) nhận tham số `companyId`.

```php
if ((Setting::getSettings()->full_multiple_companies_support == '1') && ($request->filled('companyId'))) {
    $query->where('table.company_id', $request->input('companyId'));
}
```

Kết nối từ Blade bằng `data-company-id="{{ $user->company_id }}"`.

## Select2 AJAX Dropdowns

Dùng `class="js-data-ajax"` với `data-endpoint="hardware|licenses|consumables|..."`. `snipeit.js` tự khởi tạo và chuyển `data-company-id` thành `companyId`, `data-asset-status-type` thành `statusType` tới API.

## Luồng redirect sau checkout

Sau khi checkout, `Helper::getRedirectOption()` đọc `$request->redirect_option`. Để quay lại người dùng được gán, form phải đặt:

- `redirect_option=target`
- `checkout_to_type=user`
- `assigned_user={{ $user->id }}`

## Biến view toàn cục

`$snipeSettings` được chia sẻ cho mọi view bởi `SettingsServiceProvider`. Dùng trực tiếp trong Blade — không truyền `Setting::getSettings()` từ controller.

## Helper quan trọng (`app/Helpers/Helper.php`)

- `Helper::deployableStatusLabelList()` — nhãn trạng thái cho form checkout.
- `Helper::defaultChartColors(int $index = 0)` — 10 màu mặc định.
- `Helper::getRedirectOption($request, $id, $table, $item_id = null)` — logic redirect sau checkout.
