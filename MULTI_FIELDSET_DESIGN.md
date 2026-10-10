# Thiết kế multi-fieldset cho AssetModel: điểm dừng

## Vấn đề đang xét

Cho phép một AssetModel gắn nhiều CustomFieldset thay vì chỉ một khóa ngoại duy nhất `models.fieldset_id` như hiện nay.

## Pivot hiện có trong khu vực này

- `custom_field_custom_fieldset`: CustomField đến CustomFieldset (xác định trường nào thuộc một fieldset). Đã tồn tại.
- `models_custom_fields`: AssetModel đến CustomField, với cột `asset_model_id`, `custom_field_id`, `default_value`. **Mục đích là ghi đè giá trị mặc định theo model.** Nó không quyết định model có những field nào; điều đó vẫn đi qua `models.fieldset_id`, rồi `custom_field_custom_fieldset`, rồi `custom_fields`. Migration: `database/migrations/2018_04_16_133902_create_custom_field_default_values_table.php`.

## Pivot còn thiếu cho tính năng

- AssetModel đến CustomFieldset (many-to-many). Chưa tồn tại trong bất kỳ form nào hiện tại.

## Hai hướng đi đã đề xuất

### Hướng A: thay đổi nhỏ hơn

Thêm một pivot mới:

```php
Schema::create('asset_model_custom_fieldset', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('model_id')->index();
    $table->unsignedBigInteger('custom_fieldset_id')->index();
    $table->unsignedInteger('order')->default(0);
    $table->timestamps();
    $table->unique(['model_id', 'custom_fieldset_id']);
});
```

- Giữ mô hình tư duy: model có fieldsets, fieldsets có fields, hợp nhất khi render.
- `models.fieldset_id` giữ vai trò shim tương thích trong quá trình chuyển đổi. Migration sau đó có thể xóa nó khi mọi consumer đọc từ pivot mới.
- Phạm vi consumer cần duyệt lại: AssetsController, API transformer, blades create/edit, custom reports, CSV import, PDF label renderer, bulk edit. Khoảng 15–25 điểm, mỗi chỗ nhỏ.
- Thay đổi contract API: `assets.*.custom_fieldset` trở thành mảng `custom_fieldsets`. Cách rollout mềm nhất là giữ cả hai khóa trong một thời gian deprecation.

### Hướng B: viết lại theo kiến trúc sạch hơn

Nâng `models_custom_fields` từ side-table default thành bảng chính để xác định "model có những field nào".

- Thêm `order` vào `models_custom_fields`, giữ `default_value`.
- Giảm vai trò của CustomFieldset thành template tái sử dụng.
- Model đến field trở thành direct relation; fieldset chỉ là tiện ích UI để attach hàng loạt.
- Loại bỏ tầng `model -> fieldset -> pivot -> field` trong mọi consumer.

## Câu hỏi chưa giải quyết khi dừng lại

1. Chọn A hay B: tùy quyết định của Snipe-IT; B sạch hơn nhưng A triển khai nhanh hơn.
2. Sắp xếp field giữa các fieldset khi dùng A: cần có `order` ở fieldset và `order` trong từng field.
3. Trùng field: nếu cùng một CustomField nằm trong hai fieldset, form có thể render duplicate. Cần de-dupe khi merge danh sách field.
4. Khoảng API tương thích ngược về khóa `custom_fieldset` singular. Nên giữ lâu một minor release cùng `custom_fieldsets` array rồi bỏ ở release sau.
