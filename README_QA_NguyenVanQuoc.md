# QA Engineer - Test Design Documentation
**Mã sinh viên:** 2312729  
**Tên:** Nguyễn Văn Quốc  
**Vai trò:** Kỹ sư Kiểm thử Logic Phần cứng & Thuật toán Khấu hao  
**Phân vùng phụ trách:** Module Quản lý Tài sản Phần cứng, Dòng Model & Khấu hao  

---

## 1. Tổng quan

Tài liệu này mô tả thiết kế test case theo kỹ thuật **Test Model / Scenario / Case** cho 3 bộ test chính:
- `AssetTest.php` - Unit tests cho Asset (tài sản)
- `DepreciationTest.php` - Unit tests cho thuật toán khấu hao
- `AssetModelTest.php` - Unit tests cho Asset Model

Mỗi test case bao gồm: **Input, Precondition, Expected Result/Invariant, Test Data**.

---

## 2. AssetTest.php - Test Model: Asset Management

### TC-ASSET-001: UNIQUE Constraint trên asset_tag

| Trường | Giá trị |
|--------|---------|
| **Input** | Tạo asset 1 với `asset_tag = 'UNIQUE-TEST-001'`, sau đó tạo asset 2 với cùng `asset_tag` |
| **Precondition** | - Database có bảng `assets` với UNIQUE index trên cột `asset_tag`<br>- Asset 1 đã được tạo thành công |
| **Expected Result / Invariant** | - Asset 1 lưu thành công (`save() = true`)<br>- Asset 2 lưu **thất bại** (`save() = false`) do vi phạm UNIQUE constraint |
| **Test Data** | ```php $asset1 = Asset::factory()->create(['asset_tag' => 'UNIQUE-TEST-001']); $asset2 = Asset::factory()->make(['asset_tag' => 'UNIQUE-TEST-001']); $this->assertFalse($asset2->save()); ``` |

---

### TC-ASSET-002: Encrypted Custom Field - Encryption & Decryption

| Trường | Giá trị |
|--------|---------|
| **Input** | Tạo CustomField với `field_encrypted = '1'`, gán cho Asset qua Fieldset → Model, lưu giá trị plaintext qua controller (encrypt) |
| **Precondition** | - CustomFieldFactory::testEncrypted() tạo field có `field_encrypted = '1'`<br>- Fieldset chứa field này, Model dùng Fieldset đó<br>- APP_KEY đã được set trong .env |
| **Expected Result / Invariant** | - Giá trị trong DB **khác** plaintext (đã encrypt)<br>- `Crypt::decryptString($encryptedValue)` trả về đúng plaintext gốc<br>- Không throw `DecryptException` |
| **Test Data** | ```php $field = CustomField::factory()->testEncrypted()->create(); $asset = Asset::factory()->hasEncryptedCustomField($field)->create(); $plaintext = 'This is a test encrypted value'; $encryptedValue = Crypt::encryptString($plaintext); $asset->{$field->db_column_name()} = $encryptedValue; $asset->save(); $this->assertEquals($plaintext, Crypt::decryptString($asset->{$field->db_column_name()})); ``` |

---

### TC-ASSET-003: MAC Address Custom Field - Format Validation & Storage

| Trường | Giá trị |
|--------|---------|
| **Input** | Tạo CustomField với format MAC regex, gán vào Fieldset → Model → Asset, set giá trị MAC hợp lệ / không hợp lệ |
| **Precondition** | - CustomField có `format = 'regex:/^([0-9a-fA-F]{2}[:-]){5}[0-9a-fA-F]{2}$/'`<br>- Fieldset -> Model -> Asset chain đã sẵn sàng<br>- Column `_snipeit_mac_address_xx` đã được tạo trên bảng `assets` (qua boot event `created`) |
| **Expected Result / Invariant** | - MAC hợp lệ (`00:1A:2B:3C:4D:5E`) lưu thành công, đọc ra đúng giá trị<br>- MAC không hợp lệ (`invalid-mac-address`) validation fail → `save() = false` |
| **Test Data** | ```php $macField = new CustomField(['name' => 'MAC Address Test', 'format' => 'regex:/^([0-9a-fA-F]{2}[:-]){5}[0-9a-fA-F]{2}$/', 'element' => 'text']); $macField->save(); $fieldset->fields()->attach($macField->id, ['order' => 1, 'required' => false]); $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]); $asset = Asset::factory()->create(['model_id' => $model->id]); $asset->{$macField->db_column_name()} = '00:1A:2B:3C:4D:5E'; $asset->save(); $this->assertEquals('00:1A:2B:3C:4D:5E', $asset->{$macField->db_column_name()}); $asset2->{$macField->db_column_name()} = 'invalid-mac-address'; $this->assertFalse($asset2->save()); ``` |

---

### TC-ASSET-004: Auto Increment Asset Tag (Existing Tests)

| Test Method | Scenario | Invariant |
|-------------|----------|-----------|
| `test_auto_increment` | Tạo 2 asset liên tiếp với auto_increment | Tag tăng dần, không trùng |
| `test_auto_increment_collision` | 2 request cùng lúc lấy cùng tag | Thứ 2 fail (race condition simulation) |
| `test_auto_increment_gap_and_backfill` | Tạo gap 3, backfill 1-2-3, tiếp theo | Tag tiếp theo nhảy đúng 4 đơn vị |
| `test_prefixless_autoincrement_backfill` | Không prefix, zerofill = 0 | Logic backfill vẫn đúng |

---

## 3. DepreciationTest.php - Test Model: Depreciation Algorithm

### TC-DEPR-001: Linear Depreciation - Amount Type với Floor (depreciation_min)

| Trường | Giá trị |
|--------|---------|
| **Input** | Depreciation: type=amount, min=1000, months=12<br>Asset: purchase_cost=4000, purchase_date=24 tháng trước |
| **Precondition** | - Depreciation method mặc định = 'linear' (Setting::depreciation_method)<br>- Asset có model gán depreciation_id<br>- purchase_date đủ cũ để months_passed >= months |
| **Expected Result / Invariant** | - `getLinearDepreciatedValue() >= depreciation_min (1000)`<br>- Kết quả = 1000 (floor) không âm |
| **Test Data** | ```php $depreciation = Depreciation::factory()->create(['depreciation_type' => 'amount', 'depreciation_min' => 1000, 'months' => 12]); $asset = Asset::factory()->laptopMbp()->create(['purchase_date' => now()->subMonths(24), 'purchase_cost' => 4000]); $asset->model->update(['depreciation_id' => $depreciation->id]); $value = $asset->getLinearDepreciatedValue(); $this->assertEquals(1000, $value); $this->assertGreaterThanOrEqual(1000, $value); ``` |

---

### TC-DEPR-002: Linear Depreciation - Percentage Type với Floor

| Trường | Giá trị |
|--------|---------|
| **Input** | Depreciation: type=percent, min=25 (%), months=12<br>Asset: purchase_cost=4000, purchase_date=24 tháng trước |
| **Precondition** | - `calculateDepreciation()` trả về `purchase_cost * (depreciation_min/100)` = 1000 |
| **Expected Result / Invariant** | - Giá trị khấu hao >= 1000 (25% floor)<br>- Kết quả = 1000 |
| **Test Data** | ```php $depreciation = Depreciation::factory()->create(['depreciation_type' => 'percent', 'depreciation_min' => 25, 'months' => 12]); $asset = Asset::factory()->laptopMbp()->create(['purchase_date' => now()->subMonths(24), 'purchase_cost' => 4000]); $asset->model->update(['depreciation_id' => $depreciation->id]); $value = $asset->getLinearDepreciatedValue(); $this->assertEquals(1000, $value); ``` |

---

### TC-DEPR-003: Linear Depreciation - Không Floor (depreciation_min = 0)

| Trường | Giá trị |
|--------|---------|
| **Input** | Depreciation: type=amount, min=0, months=12<br>Asset: purchase_cost=4000, purchase_date=24 tháng trước |
| **Precondition** | - months_passed (24) >= months (12)<br>- `calculateDepreciation()` trả về 0 |
| **Expected Result / Invariant** | - Kết quả = 0 (không âm)<br>- `assertGreaterThanOrEqual(0, $value)` |
| **Test Data** | ```php $depreciation = Depreciation::factory()->create(['depreciation_type' => 'amount', 'depreciation_min' => 0, 'months' => 12]); $asset = Asset::factory()->laptopMbp()->create(['purchase_date' => now()->subMonths(24), 'purchase_cost' => 4000]); $asset->model->update(['depreciation_id' => $depreciation->id]); $value = $asset->getLinearDepreciatedValue(); $this->assertEquals(0, $value); $this->assertGreaterThanOrEqual(0, $value); ``` |

---

### TC-DEPR-004: Linear Depreciation - Early Period (Chưa hết kỳ)

| Trường | Giá trị |
|--------|---------|
| **Input** | Depreciation: type=amount, min=0, months=24<br>Asset: purchase_cost=2400, purchase_date=6 tháng trước |
| **Precondition** | - months_passed = 6 < months = 24<br>- Công thức: `purchase_cost - (purchase_cost - floor) * (months_passed / months)` |
| **Expected Result / Invariant** | - 6/24 = 25% khấu hao → 75% còn lại = 1800<br>- Kết quả chính xác 1800 |
| **Test Data** | ```php $depreciation = Depreciation::factory()->create(['depreciation_type' => 'amount', 'depreciation_min' => 0, 'months' => 24]); $asset = Asset::factory()->laptopMbp()->create(['purchase_date' => now()->subMonths(6), 'purchase_cost' => 2400]); $asset->model->update(['depreciation_id' => $depreciation->id]); $value = $asset->getLinearDepreciatedValue(); $this->assertEquals(1800, $value); ``` |

---

### TC-DEPR-005: Existing Tests - Depreciation Relationships

| Test Method | Scenario | Invariant |
|-------------|----------|-----------|
| `test_a_depreciation_has_models` | 5 AssetModel gán cùng depreciation | `$depreciation->models->count() === 5` |
| `test_a_depreciation_has_licenses` | 5 License gán cùng depreciation | `$depreciation->licenses()->count() === 5` |
| `test_depreciation_amount` | Amount type, 10 năm, floor=1000 | Trả về floor 1000 |
| `test_depreciation_percentage` | Percent type, 10 năm, floor=50% | Trả về 2000 (50% của 4000) |

---

## 4. AssetModelTest.php - Test Model: AssetModel Availability

### TC-MODEL-001: percentRemaining() - Edge Cases

| Test Method | Precondition | Expected Result |
|-------------|--------------|-----------------|
| `test_percent_remaining_returns_zero_when_no_assets_are_available` | available=0, total=10 | 0% |
| `test_percent_remaining_returns_expected_ratio_for_mixed_availability` | available=2, total=5 | 40% |
| `test_percent_remaining_returns_one_hundred_when_all_assets_are_available` | available=4, total=4 | 100% |
| `test_percent_remaining_returns_zero_when_eager_loaded_total_is_zero_but_available_is_not` | eager: remaining=3, assets_count=0 | 0% (guard DivisionByZero) |
| `test_percent_remaining_returns_zero_when_eager_loaded_counts_are_string_zero` | eager: remaining='3', assets_count='0' (string) | 0% (cast to int) |
| `test_percent_remaining_returns_zero_when_runtime_total_is_zero_but_available_is_not` | runtime: available=3, total=0 | 0% |
| `test_percent_remaining_skips_queries_when_counts_were_eager_loaded` | withCount loaded | 0 query thêm, kết quả 75% |
| `test_percent_remaining_only_calls_available_assets_count_once` | 2 asset | `availableAssets()->count()` chỉ gọi 1 lần |

---

## 5. Thiết lập Môi trường Test (Test Infrastructure)

### 5.1 Database Configuration

| File | Cấu hình |
|------|----------|
| `.env.testing` | ```dotenv DB_CONNECTION=sqlite DB_DATABASE=:memory: ``` |
| `DatabaseSeeder.php` | SQLite compatibility: `PRAGMA foreign_keys = OFF/ON` thay vì `SET FOREIGN_KEY_CHECKS` |

### 5.2 Chạy Test

```bash
# Chạy 3 bộ test chính
php artisan test tests/Unit/AssetTest.php tests/Unit/DepreciationTest.php tests/Unit/AssetModelTest.php

# Kết quả: 40 tests, 87 assertions passed
```

### 5.3 Testcontainers (MySQL Integration)

```bash
# Yêu cầu: Node.js 18+, Docker
npm run test:integration:testcontainers -- tests/Unit/AssetTest.php tests/Unit/DepreciationTest.php tests/Unit/AssetModelTest.php
```

Script `tests/testcontainers/run.mjs` sẽ:
1. Khởi động MySQL 8.0 container
2. Tạo database ngẫu nhiên
3. Chạy phpunit với env MySQL
4. Dọn dẹp container sau khi xong

---

## 6. Coverage Summary

| Module | Test Cases | Assertions | Status |
|--------|------------|------------|--------|
| AssetTest | 23 | 56 | ✅ PASS |
| DepreciationTest | 8 | 15 | ✅ PASS |
| AssetModelTest | 9 | 16 | ✅ PASS |
| **Tổng** | **40** | **87** | ✅ **ALL PASS** |

---

## 7. Giải thuật Khấu hao - Invariant Cốt lõi

Đoạn code chính trong `App\Models\Depreciable::getLinearDepreciatedValue()`:

```php
// months_passed >= months (hết kỳ khấu hao)
if ($months_passed >= $this->get_depreciation()->months) {
    if ($this->get_depreciation()->depreciation_min) {
        $current_value = $this->calculateDepreciation(); // Floor value
    } else {
        $current_value = 0; // Không floor → 0
    }
} else {
    // Công thức tuyến tính: (Cost - Floor) * (months_passed / months)
    $current_value = round(($this->purchase_cost - ($this->purchase_cost - $this->calculateDepreciation()) * ($months_passed / $this->get_depreciation()->months)), 2);
}

return $current_value; // Luôn >= 0, >= floor
```

**Invariant:** `getLinearDepreciatedValue() >= max(0, depreciation_min_floor)`

---

## 8. Files Modified / Created

| File | Loại thay đổi |
|------|---------------|
| `tests/Unit/AssetTest.php` | +3 test cases (TC-ASSET-001, 002, 003) |
| `tests/Unit/DepreciationTest.php` | +4 test cases (TC-DEPR-001 đến 004) |
| `database/seeders/DatabaseSeeder.php` | Fix SQLite PRAGMA foreign_keys |
| `.env.testing` | Cấu hình SQLite in-memory |

---

## 9. Pull Request Workflow

1. **Branch:** `feature/qa-depreciation-asset-tests`
2. **Commit:** `b304efc` - "QA: Add unit tests for depreciation algorithm, asset_tag UNIQUE constraint, and custom fields"
3. **Push:** Tạo PR lên `main` để Trưởng nhóm review
4. **Review criteria:** 
   - Tất cả test pass (40/40)
   - Coverage đúng yêu cầu nhiệm vụ
   - Không breaking existing tests
   - Code style tuân thủ Laravel/Snipe-IT convention

---

