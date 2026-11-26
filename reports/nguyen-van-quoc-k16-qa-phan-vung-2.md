# Báo cáo QA K16 – Phân vùng 2: Asset, Asset Model và Depreciation

> **Căn cứ phạm vi:** Người giao việc xác nhận không có tài liệu phân công hoặc User Case riêng. Phạm vi bên dưới được tổng hợp từ yêu cầu trong cuộc trao đổi, chức năng trong repository và test hiện có; đây **không phải** danh sách User Case chính thức. Báo cáo không gán mã UC. Các nhãn `S2-*` chỉ là mã phạm vi nội bộ; test ID là mã QA nội bộ.

| Thông tin | Giá trị |
|---|---|
| Người thực hiện | Nguyễn Văn Quốc – MSSV 2312729 |
| Vai trò | QA Engineer |
| Dự án | Snipe-IT – Laravel 12 |
| Kỹ thuật | Testcontainers / Ephemeral Integration Testing |
| Phạm vi | Phân vùng 2 – Tài sản phần cứng, Dòng Model & Khấu hao |
| Repository / nhánh | `D:\KiemThu\Snipe-IT` / `quoc1` |
| Ngày ghi nhận | 2026-11-26 |

## 1. Giới thiệu

Báo cáo ghi lại kiểm thử model Asset, `asset_tag`, Custom Fields, khấu hao đường thẳng `getLinearDepreciatedValue`, Asset Model và Feature tests liên quan. Báo cáo phân biệt kết quả chạy PHPUnit với phần Testcontainers bị chặn do máy không có container runtime khả dụng.

## 2. Phạm vi kiểm thử

- **Asset và `asset_tag`:** sinh tag tự tăng, xử lý va chạm/backfill, validation tag trùng và endpoint tạo Asset.
- **Custom Fields:** kiểm tra ciphertext trong DB, giải mã, lưu/validation định dạng IMEI và MAC Address; API lưu field mã hóa.
- **Khấu hao đường thẳng:** tính giá trị trong kỳ, giá trị sàn `depreciation_min` theo tiền hoặc phần trăm, và giá trị không âm khi hết kỳ.
- **Asset Model:** quan hệ với Asset/Depreciation và phép tính `percentRemaining()`, gồm trường hợp eager-loaded, tổng bằng 0 và số đếm dạng chuỗi.
- **Feature:** toàn bộ `tests/Feature/Assets/` và `tests/Feature/AssetModels/`.
- **Testcontainers:** thử chạy đúng runner MySQL ephemeral mà repository cấu hình; runner không khởi tạo được container nên PHPUnit không chạy qua Testcontainers.

Không sửa logic ứng dụng, migration, dependency hoặc schema trong nhiệm vụ này.

### Bảng phạm vi và truy vết nội bộ (không phải User Case)

| Mã phạm vi nội bộ | Chức năng tổng hợp từ yêu cầu | Yêu cầu/luồng dự kiến kiểm tra theo thông tin hiện có | Test hiện có | Phần còn thiếu / cần xác nhận | File liên quan |
|---|---|---|---|---|---|
| S2-ASSET | Tài sản phần cứng | Lưu Asset; tạo tag; từ chối tag trùng theo `unique_undeleted`; custom field lưu/validation; Feature API/UI hiện có | `AssetTest`, `StoreAssetTest`, các test trong `Feature/Assets` | Luồng Asset chính thức, cách xử lý tag sau soft-delete, quyền theo từng vai trò và luồng checkout chưa được gắn với UC chính thức | `tests/Unit/AssetTest.php`, `tests/Feature/Assets/` |
| S2-MODEL | Dòng Model tài sản | Quan hệ Asset–Model; tỷ lệ Asset khả dụng; count runtime/eager-loaded; API/UI hiện có | `AssetModelTest`, các test trong `Feature/AssetModels` | Công thức tỷ lệ tồn kho chuẩn, bộ lọc/danh mục chính thức và quyền theo vai trò cần xác nhận | `tests/Unit/AssetModelTest.php`, `tests/Feature/AssetModels/` |
| S2-DEPR | Khấu hao | `getLinearDepreciatedValue`: tính giữa kỳ, floor amount/percent, hết kỳ và không âm; quan hệ Model/Depreciation | `DepreciationTest`, `AssetTest::test_depreciation_progress_percent_returns_expected_value` | Quy tắc kế toán, xử lý biên ngày, timezone và quy tắc rounding chính thức cần xác nhận | `tests/Unit/DepreciationTest.php`, `tests/Unit/AssetTest.php` |
| S2-CF | Custom Fields thuộc Asset | Mã hóa/lưu/giải mã; IMEI/MAC hợp lệ và không hợp lệ; API validation/permission | Unit Asset tests, `StoreAssetTest`, `AssetHistoryTest`, `EncryptedCustomFieldFormDisclosureTest` | Yêu cầu cụ thể theo field/role và vòng đời dữ liệu mã hóa chưa có tài liệu UC để xác nhận | `tests/Unit/AssetTest.php`, `tests/Feature/Assets/` |

Các nội dung ở cột “cần xác nhận” là giới hạn truy vết, không được coi là yêu cầu bắt buộc hoặc lỗi sản phẩm.

## 3. Kế hoạch tổng thể kiểm thử Phân vùng 2

| STT | Giai đoạn | Công việc | Phạm vi nội bộ | Đầu vào | Kết quả đầu ra | Điều kiện hoàn thành |
|---:|---|---|---|---|---|---|
| 1 | Xác định phạm vi | Đối chiếu yêu cầu hiện có, model/validation và test; đánh dấu rõ chưa có UC chính thức | S2-ASSET, S2-MODEL, S2-DEPR, S2-CF | Yêu cầu trong cuộc trao đổi; source và test repository | Bảng phạm vi nội bộ, danh sách điểm cần xác nhận | Không tuyên bố có UC chính thức; không mở rộng sang phân vùng khác |
| 2 | Kiểm tra môi trường | Xác nhận repo/nhánh, PHP/Laravel/PHPUnit/Node, DB test, extension và runtime container | Toàn phạm vi | `php --version`, `php artisan --version`, `phpunit --version`, `.env.testing`, lệnh runtime | Thông số môi trường và các giới hạn đã kiểm chứng | Môi trường được ghi nhận; runtime thiếu được phân loại BLOCKED |
| 3 | Thiết kế Unit | Kiểm tra tag, custom field, quan hệ và thuật toán khấu hao theo behavior hiện có | S2-ASSET, S2-CF, S2-DEPR, S2-MODEL | Factories/model/schema hiện có; dữ liệu biên trong test | Danh sách case nội bộ và expected result theo assertion | Các case không áp đặt quy tắc ngoài code/yêu cầu đã nêu |
| 4 | Thiết kế Feature | Rà API/UI Asset và Asset Model, validation, phân quyền, custom field, quan hệ | S2-ASSET, S2-MODEL, S2-CF | Feature tests và controllers/routes liên quan | Ma trận Feature inventory | Test hiện hữu được dùng lại; incomplete/skip giữ nguyên trạng thái |
| 5 | Chạy theo tầng | Chạy 3 Unit file; Feature chọn lọc; barcode regression; sau đó toàn bộ hai Feature folder | Toàn phạm vi | SQLite `:memory:`, PHPUnit 11 | PHPUnit output, JUnit XML và log lệnh | Exit code và thống kê được lưu; lỗi không bị che |
| 6 | Integration ephemeral | Chạy MySQL Testcontainers đã cấu hình để so sánh SQL thật với SQLite | S2-ASSET, S2-MODEL, S2-CF | Docker Engine/runtime, Node deps, PDO MySQL | Kết quả Testcontainers nếu runtime sẵn sàng | PHPUnit chạy bên trong runner; nếu thiếu daemon ghi NOT RUN/BLOCKED |
| 7 | Phân loại và regression | Đối chiếu assertion, log ứng dụng, fixture, memory limit và chạy lại test sau xử lý cần thiết | Toàn phạm vi | PHPUnit/JUnit, log ứng dụng cũ, fixture barcode | Phân loại product/test/fixture/environment; regression result | Mỗi vấn đề có nguyên nhân và evidence; không bỏ qua test |
| 8 | Báo cáo và giao nhận | Tổng hợp traceability, kế hoạch, case, số liệu, log và giới hạn; rà soát staged diff | Toàn phạm vi nội bộ | Evidence đã thu thập; Git diff | Báo cáo và bằng chứng có thể kiểm tra | Không chứa thông tin xác thực; file Git chỉ thuộc phần được giao |

## 4. Môi trường và công cụ đã xác minh

| Thành phần | Giá trị quan sát được |
|---|---|
| Hệ điều hành / shell | Windows, PowerShell |
| PHP CLI | 8.5.11 |
| Laravel Framework | 12.68.0 |
| PHPUnit | 11.5.55 |
| Node.js | 24.21.0 |
| PDO drivers | `pdo_mysql`, `pdo_sqlite`; `sqlite3` extension có mặt |
| Cấu hình DB test hiện hành | `.env.testing`: `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` |
| PHPUnit config | `phpunit.xml`, hai testsuite Unit và Feature |
| Docker/Podman runtime | Không tìm thấy CLI/service; Testcontainers báo không tìm thấy runtime khả dụng |

Các số liệu trên đến từ lệnh phiên bản, module PHP, file `.env.testing` và output thực tế của test runner. Không có lượt test MySQL container thành công trong môi trường này.

## 5. Đặc tả test case trọng tâm

Các tiền điều kiện và dữ liệu dưới đây phản ánh factory, cấu hình và assertion trong test. “Thực tế” là kết quả của lượt chạy Unit cuối cùng hoặc full Feature suite có ghi nhận riêng.

`UC liên quan / phạm vi nội bộ` không chứa mã UC chính thức. P1/P2 là mức ưu tiên QA đề xuất cho các test đang có, không phải ưu tiên trích từ tài liệu phân công.

| Test Case ID | UC liên quan / phạm vi nội bộ | Tên test case | Mục tiêu | Loại | Ưu tiên (QA đề xuất) | Tiền điều kiện | Dữ liệu kiểm thử | Các bước thực hiện | Kết quả mong đợi | Kết quả thực tế | Trạng thái | Bằng chứng/log |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| U-A-01 | Chưa có UC chính thức; S2-ASSET | `AssetTest::test_asset_tag_unique_constraint` | Từ chối tag đang được Asset chưa xóa sử dụng | Unit / regression | P1 | DB test đã migrate; Asset factory hợp lệ | Hai Asset cùng `UNIQUE-TEST-001` | Lưu Asset đầu; gọi `save()` Asset thứ hai; đọc lỗi model | Lưu thứ hai trả false và có lỗi `asset_tag` | Assertion lưu false và lỗi `asset_tag` pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-A-02 | Chưa có UC chính thức; S2-ASSET | `AssetTest::test_encrypted_custom_field_encryption_and_decryption` | Xác minh ciphertext được lưu nguyên trong DB và giải mã được | Unit / regression | P1 | Có encrypted Custom Field và Asset liên kết | `This is a test encrypted value`; `Crypt::encrypt()` | Ghi ciphertext vào cột custom; đọc trực tiếp DB; giải mã ciphertext | DB không lưu plaintext; ciphertext khớp; giải mã ra giá trị gốc | Ba assertion về DB/ciphertext/decrypt pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-A-03 | Chưa có UC chính thức; S2-ASSET | `AssetTest::test_mac_address_custom_field_format_and_storage` | Lưu MAC hợp lệ và từ chối sai format | Unit / regression | P1 | Custom Field regex MAC gắn vào fieldset của Asset Model | Hợp lệ `00:1A:2B:3C:4D:5E`; sai `invalid-mac-address` | Lưu Asset có giá trị hợp lệ; tạo Asset sai; kiểm tra lỗi validation | Cột custom được tạo; giá trị hợp lệ lưu; giá trị sai bị từ chối | Test pass, gồm kiểm tra lỗi theo cột custom | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-A-04 | Chưa có UC chính thức; S2-ASSET | `AssetTest::test_imei_custom_field_format_and_storage` | Lưu IMEI 15 số và từ chối dữ liệu sai | Unit / regression | P1 | Custom Field IMEI gắn fieldset/Asset Model | Hợp lệ `490154203237518`; sai `not-an-imei` | Lưu Asset hợp lệ; đọc giá trị DB; thử lưu giá trị sai | Giá trị 15 số được lưu; chuỗi không hợp lệ bị từ chối | Test pass; giá trị DB và lỗi validation được xác nhận | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-D-01 | Chưa có UC chính thức; S2-DEPR | `DepreciationTest::test_a_depreciation_has_models` | Quan hệ Depreciation–AssetModel | Unit / regression | P2 | Có category asset và Depreciation | 5 Asset Model cùng `depreciation_id` | Tạo model và đếm relation | Có 5 model | Assertion count = 5 pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-D-02 | Chưa có UC chính thức; S2-DEPR | `DepreciationTest::test_depreciation_amount` | Tính khấu hao amount khi đã hết kỳ | Unit / regression | P1 | Asset có purchase date, model liên kết Depreciation | Cost 4000; floor 1000; 36 tháng; ngày mua 10 năm trước | Gọi `getLinearDepreciatedValue()` | Book value bằng floor 1000 | Assertion bằng `depreciation_min` pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-D-03 | Chưa có UC chính thức; S2-DEPR | `DepreciationTest::test_depreciation_percentage` | Tính sàn phần trăm sau khi hết kỳ | Unit / regression | P1 | Asset liên kết Depreciation percent | Cost 4000; floor 50%; 36 tháng; ngày mua 10 năm trước | Gọi `getLinearDepreciatedValue()` | Giá trị còn lại bằng 2000 | Assertion 2000 pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-D-04 | Chưa có UC chính thức; S2-DEPR | `DepreciationTest::test_a_depreciation_has_licenses` | Quan hệ Depreciation–License | Unit / regression | P2 | Có Depreciation và license category | 5 License cùng depreciation | Tạo và đếm licenses | Relation trả 5 License | Assertion count = 5 pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-D-05 | Chưa có UC chính thức; S2-DEPR | `DepreciationTest::test_linear_depreciation_never_negative_and_respects_floor` | Sàn amount và chặn giá trị âm | Unit / regression | P1 | Asset đã qua hết vòng đời khấu hao | Cost 4000; floor 1000; 12 tháng; đã 24 tháng | Gọi hàm khấu hao; so sánh với floor | Giá trị = 1000 và không thấp hơn floor | Hai assertion pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-D-06 | Chưa có UC chính thức; S2-DEPR | `DepreciationTest::test_linear_depreciation_percentage_never_negative` | Sàn phần trăm | Unit / regression | P1 | Asset đã qua hết kỳ | Cost 4000; floor 25%; 12 tháng; đã 24 tháng | Gọi hàm khấu hao | Giá trị = 1000 (25% của 4000), không âm | Hai assertion pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-D-07 | Chưa có UC chính thức; S2-DEPR | `DepreciationTest::test_linear_depreciation_no_floor_does_not_go_negative` | Không có floor thì book value không âm | Unit / regression | P1 | Asset hết kỳ; depreciation amount | Cost 4000; floor 0; 12 tháng; đã 24 tháng | Gọi hàm khấu hao | Giá trị bằng 0 và >= 0 | Hai assertion pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-D-08 | Chưa có UC chính thức; S2-DEPR | `DepreciationTest::test_linear_depreciation_early_period_calculates_correctly` | Tính tuyến tính trong kỳ | Unit / regression | P1 | Asset mua 6 tháng trước, kỳ 24 tháng | Cost 2400; floor 0; 6/24 tháng | Gọi hàm khấu hao | Còn 75% giá trị, bằng 1800 | Assertion 1800 pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-M-01 | Chưa có UC chính thức; S2-MODEL | `AssetModelTest::test_an_asset_model_contains_assets` | Quan hệ AssetModel–Asset | Unit / regression | P1 | Có category asset | Một model và một Asset gắn `model_id` | Tạo Asset rồi đếm relation | Model có 1 Asset | Assertion count = 1 pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-M-02 | Chưa có UC chính thức; S2-MODEL | `AssetModelTest::test_percent_remaining_returns_zero_when_no_assets_are_available` | Guard khi không có Asset khả dụng | Unit / regression | P1 | Model stub có relation count | Available 0; tổng 10 | Gọi `percentRemaining()` | Kết quả 0 | Assertion 0 pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-M-03 | Chưa có UC chính thức; S2-MODEL | `AssetModelTest::test_percent_remaining_returns_expected_ratio_for_mixed_availability` | Tính phần trăm còn lại | Unit / regression | P1 | Model stub có Asset available và total | 2/5 | Gọi `percentRemaining()` | 40.0% | Assertion 40.0 pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-M-04 | Chưa có UC chính thức; S2-MODEL | `AssetModelTest::test_percent_remaining_skips_queries_when_counts_were_eager_loaded` | Không tạo query N+1 khi đã eager-load count | Unit / regression | P2 | Model có `remaining`/`assets_count` tương ứng `withCount` | 3/4 | Gắn DB listener rồi gọi hàm | Không phát sinh query; trả 75.0% | Query count 0 và tỷ lệ 75.0 pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-M-05 | Chưa có UC chính thức; S2-MODEL | `AssetModelTest::test_percent_remaining_only_calls_available_assets_count_once` | Chỉ đếm Asset khả dụng một lần | Unit / regression | P2 | DB test có model và hai Asset | 2 Asset khả dụng | Đếm query `count(*)` khả dụng qua listener | Chính xác một query count khả dụng | Assertion count = 1 pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-M-06 | Chưa có UC chính thức; S2-MODEL | `AssetModelTest::test_percent_remaining_returns_one_hundred_when_all_assets_are_available` | Trường hợp toàn bộ Asset khả dụng | Unit / regression | P1 | Model stub có available/total count | 4/4 | Gọi `percentRemaining()` | 100.0% | Assertion 100.0 pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-M-07 | Chưa có UC chính thức; S2-MODEL | `AssetModelTest::test_percent_remaining_returns_zero_when_eager_loaded_total_is_zero_but_available_is_not` | Không chia cho 0 với count không nhất quán | Unit / regression | P1 | Eager-loaded count giả lập anomaly | `remaining=3`, `assets_count=0` | Gọi `percentRemaining()` | Trả 0, không ném lỗi | Assertion 0 pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-M-08 | Chưa có UC chính thức; S2-MODEL | `AssetModelTest::test_percent_remaining_returns_zero_when_eager_loaded_counts_are_string_zero` | Guard số 0 dạng chuỗi từ PDO | Unit / regression | P1 | Eager-loaded attributes dạng string | `remaining='3'`, `assets_count='0'` | Gọi `percentRemaining()` | Trả 0, không `DivisionByZeroError` | Assertion 0 pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| U-M-09 | Chưa có UC chính thức; S2-MODEL | `AssetModelTest::test_percent_remaining_returns_zero_when_runtime_total_is_zero_but_available_is_not` | Guard tổng 0 trên đường không eager-load | Unit / regression | P1 | Model stub runtime relations | Available 3; total 0 | Gọi `percentRemaining()` | Trả 0, không chia cho 0 | Assertion 0 pass | Pass | `reports/k16-quoc-evidence/unit-run.txt` |
| F-A-01 | Chưa có UC chính thức; S2-ASSET | `StoreAssetTest::test_asset_tags_must_be_unique_when_undeleted` | API từ chối tag đang dùng | Feature / integration | P1 | Superuser API; model và deployable status tồn tại | Cùng tag `1234567890` gửi hai lần | POST route lưu Asset hai lần | Lần đầu thành công; lần hai báo error | Pass trong Feature suite | Pass | `reports/k16-quoc-evidence/feature-full.txt`; `feature-assets-assetmodels.junit.xml` |
| F-A-02 | Chưa có UC chính thức; S2-ASSET | `StoreAssetTest::test_asset_tags_can_be_duplicated_if_deleted` | Xác nhận quy tắc bỏ qua Asset đã xóa mềm | Feature / integration | P1 | Superuser API; Asset cùng tag đã soft-delete | Tag `1234567890` | POST Asset mới với tag cũ | Tạo thành công theo `unique_undeleted` | Pass trong Feature suite | Pass | `reports/k16-quoc-evidence/feature-full.txt`; `feature-assets-assetmodels.junit.xml` |
| F-CF-01 | Chưa có UC chính thức; S2-ASSET | `StoreAssetTest::test_encrypted_custom_field_can_be_stored` | API mã hóa và lưu custom field | Feature / integration | P1 | SQLite test; user superuser; encrypted field | `This is encrypted field` | POST asset với plaintext; đọc Asset; decrypt DB/model field | API thành công và decrypt ra plaintext ban đầu | Pass trong Feature suite | Pass | `reports/k16-quoc-evidence/feature-full.txt`; `feature-assets-assetmodels.junit.xml` |
| F-CF-02 | Chưa có UC chính thức; S2-ASSET | `StoreAssetTest::test_encrypted_custom_field_validation_passes` | Validation alpha/numeric/email cho field mã hóa | Feature / integration | P1 | SQLite test; ba encrypted field có rule | `Thisisencryptedfield`, `1234567890`, `poop@poop.com` | POST dữ liệu hợp lệ; decrypt ba cột | API thành công; ba plaintext khớp dữ liệu gửi | Pass trong Feature suite | Pass | `reports/k16-quoc-evidence/feature-full.txt`; `feature-assets-assetmodels.junit.xml` |
| F-CF-03 | Chưa có UC chính thức; S2-ASSET | `StoreAssetTest::test_encrypted_custom_field_validation_fails` | Từ chối custom field mã hóa không đúng format | Feature / integration | P1 | SQLite test; encrypted alpha field | `Thisisencryptedfield123` | POST dữ liệu; kiểm tra lỗi JSON | API báo error cho cột alpha | Pass trong Feature suite | Pass | `reports/k16-quoc-evidence/feature-full.txt`; `feature-assets-assetmodels.junit.xml` |
| F-CF-04 | Chưa có UC chính thức; S2-ASSET | `StoreAssetTest::test_permission_needed_to_store_encrypted_field` | Kiểm tra quyền ghi encrypted field | Feature / integration | P1 | Test được đánh dấu incomplete trong source (`markTestIncomplete`) | Field mã hóa và user editAssets | Theo source sẽ POST rồi kiểm tra giá trị bị giữ nguyên | Cần assertion quyền thành công để hoàn tất | Bị đánh dấu incomplete trực tiếp trong test | Incomplete | `reports/k16-quoc-evidence/feature-full.txt`; `feature-assets-assetmodels.junit.xml` |
| K16-INT-01 | Chưa có UC chính thức; S2-ASSET | `StoreAssetTest::test_asset_tags_must_be_unique_when_undeleted` trên MySQL ephemeral | Xác minh cùng validation khi dùng MySQL container theo kỹ thuật K16 | Integration / Testcontainers | P1 | Docker Engine khả dụng, Node/npm deps và PDO MySQL | Lưu tag `1234567890` hai lần | Chạy runner Testcontainers với test lọc tag trùng | PHPunit khởi chạy trong MySQL ephemeral và báo từ chối tag thứ hai | Runner dừng trước PHPUnit với `Could not find a working container runtime strategy` | BLOCKED | `reports/k16-quoc-evidence/testcontainers.txt` |

## 6. Kết quả thực thi

### Lệnh và kết quả

| Lệnh | Kết quả thực tế | Bằng chứng |
|---|---|---|
| `vendor/bin/phpunit tests/Unit/AssetTest.php tests/Unit/DepreciationTest.php tests/Unit/AssetModelTest.php --no-progress --display-errors` | Pass: 41 tests, 96 assertions, 0 failure/skip/incomplete | `reports/k16-quoc-evidence/unit-run.txt` |
| `vendor/bin/phpunit tests/Feature/Assets/Api/StoreAssetTest.php tests/Feature/AssetModels/Api/AssetsForAssetModelTest.php --no-progress --display-errors` | 48 tests, 190 assertions; 1 incomplete, 0 failure (47 pass) | `reports/k16-quoc-evidence/feature-focused.txt` |
| `vendor/bin/phpunit tests/Feature/Assets/AssetBarcodeAuthorizationTest.php --no-progress --display-errors` | Pass: 2 tests, 2 assertions | `reports/k16-quoc-evidence/barcode-feature.txt` |
| `php -d memory_limit=1G vendor/bin/phpunit tests/Feature/Assets tests/Feature/AssetModels --no-progress --display-errors --display-skipped --display-incomplete --log-junit reports/k16-quoc-evidence/feature-assets-assetmodels.junit.xml` | Pass: 503 tests, 1,864 assertions; 0 failure/error, 1 skipped, 2 incomplete; 500 pass | `reports/k16-quoc-evidence/feature-full.txt` và JUnit XML |
| `npm.cmd run test:integration:testcontainers -- tests/Feature/Assets/Api/StoreAssetTest.php --filter=test_asset_tags_must_be_unique_when_undeleted` | Exit 1 trước PHPUnit: `Could not find a working container runtime strategy`; 0 test chạy qua Testcontainers | `reports/k16-quoc-evidence/testcontainers.txt` |
| `vendor/bin/pint --test tests/Unit/AssetTest.php` | Pass | Terminal output trong lượt này |
| `php -l tests/Unit/AssetTest.php`; `php -l tests/Unit/DepreciationTest.php`; `php -l tests/Unit/AssetModelTest.php` | Cả 3 file không lỗi cú pháp | Terminal output trong lượt này |
| `git diff --check` | Pass | Chạy lại khi rà soát staged diff |

Tổng full scope: 544 lượt test (41 Unit + 503 Feature), gồm 541 pass, 1 skipped, 2 incomplete, 0 failure. Các suite chọn lọc có giao với full suite nên không cộng lặp.

| Bộ test / phạm vi nguồn | Tổng lượt | Pass | Fail | Skipped | Incomplete |
|---|---:|---:|---:|---:|---:|
| `tests/Unit/AssetTest.php` | 24 | 24 | 0 | 0 | 0 |
| `tests/Unit/DepreciationTest.php` | 8 | 8 | 0 | 0 | 0 |
| `tests/Unit/AssetModelTest.php` | 9 | 9 | 0 | 0 | 0 |
| Feature `Assets` | 392 | 390 | 0 | 1 | 1 |
| Feature `AssetModels` | 111 | 110 | 0 | 0 | 1 |
| **Tổng full scope** | **544** | **541** | **0** | **1** | **2** |

Các lượt Feature được đếm từ JUnit XML, chia theo namespace/class trong hai thư mục. Unit count được đối chiếu với output PHPUnit và số method test trong ba file. Bảng không cộng các lượt Feature chọn lọc lần thứ hai.

Full Feature suite là lần chạy đầy đủ trên SQLite. Các trạng thái không thành công là:

| Test | Trạng thái | Nguyên nhân theo test/source |
|---|---|---|
| `Assets\Api\StoreAssetTest::test_permission_needed_to_store_encrypted_field` | Incomplete | Test gọi `markTestIncomplete()` trực tiếp; source còn ghi TODO về authorization response |
| `AssetModels\Api\IndexAssetModelsTest::test_asset_model_index_filter_can_search_computed_count_aliases` | Incomplete | Test tự đánh dấu không tương thích SQLite; cần kiểm chứng trên MySQL |
| `Assets\Api\DeleteAssetsTest::test_cannot_delete_asset_that_is_checked_out` | Skipped | Source ghi hành vi này hiện chưa hoạt động |

Các kết quả của các suite có giao nhau không được cộng gộp thành tổng độc lập. Số liệu full-scope chính là lượt chạy 503 test / 1,864 assertions ở trên.

### Fixture barcode

Trước khi bổ sung fixture, `AssetBarcodeAuthorizationTest::test_barcode_route_allows_asset_viewer` thất bại vì controller fallback đọc `public/uploads/barcodes/invalid_barcode.gif` nhưng file không có trong repository. Đã thêm GIF 1×1 hợp lệ ở đúng đường dẫn và ngoại lệ cụ thể trong `public/uploads/.gitignore`. `getimagesize()` xác nhận MIME `image/gif`; test file barcode pass 2/2 và full Feature suite hiện không còn failure liên quan fixture.

### Testcontainers / ephemeral DB

Runner đã được gọi bằng lệnh ở trên nhưng không khởi tạo được MySQL container vì không tìm thấy Docker/Podman runtime khả dụng. Đây là giới hạn môi trường, không phải PHPUnit failure; Testcontainers chưa thực sự chạy và kết quả integration trên MySQL là **BLOCKED / NOT RUN**. Lượt kiểm tra không tìm thấy Docker/Podman CLI hoặc Docker service. Điều kiện tái chạy: Docker Engine/Desktop được cài và daemon đang chạy, sau đó chạy lại đúng lệnh Testcontainers.

### Log, bằng chứng và phân loại

- PHPUnit output theo từng lệnh và JUnit XML 503 case được lưu dưới `reports/k16-quoc-evidence/`.
- Hai log văn bản `quality-checks.txt` và `testcontainers.txt` chỉ được chuẩn hóa trailing horizontal whitespace sinh bởi PowerShell; thông báo, lỗi và exit code được giữ nguyên để `git diff --cached --check` không báo whitespace. Log gốc ứng dụng `storage/logs/laravel.log` không bị sửa.
- Lỗi runner Testcontainers được giữ trong `testcontainers.txt`; runner không tìm được runtime và không thực thi test trong container.
- `storage/logs/laravel.log` không được cập nhật bởi lượt chạy hiện tại; file gốc được giữ nguyên. `app-log-extract.txt` là bản trích lọc, bỏ `userId` và đường dẫn tuyệt đối, ghi hai lỗi cũ trước lần chạy mới: fixture barcode thiếu và PHP CLI hết memory 128 MB.
- **Fixture:** controller fallback tham chiếu GIF không có trong checkout trước đó. Đã thêm GIF 1×1 hợp lệ đúng đường dẫn và ngoại lệ cụ thể trong `public/uploads/.gitignore`; kiểm tra MIME `image/gif`; barcode test và full suite pass. Phân loại: thiếu fixture trong repository, đã xử lý; không sửa logic controller.
- **Memory:** lần chạy full trước đó với giới hạn mặc định 128 MB lỗi trong Blade compilation; lượt hiện tại dùng `memory_limit=1G` và pass. Phân loại: giới hạn môi trường tiến trình PHPUnit, đã xử lý cho lượt xác minh này; không đổi cấu hình dự án.
- **Incomplete/Skipped:** hai incomplete và một skipped được source đánh dấu như bảng trên; không thay đổi hoặc hạ tiêu chí.
- **Regression:** chạy lại Unit, Feature chọn lọc, `AssetBarcodeAuthorizationTest` và full hai Feature directory sau khi thêm fixture; không còn failure.
- Các log PHPUnit/Testcontainers đã được rà mẫu khóa/token/password; không phát hiện chuỗi phù hợp trong evidence. Log ứng dụng gốc không được đưa vào commit; chỉ bản trích lọc đã ẩn thông tin nhận diện.

## 7. Danh mục đầy đủ các test case trong phạm vi

Danh sách dưới đây được trích từ các method `test_*` trong ba Unit file và từ JUnit XML của lượt full Feature run (503 test executions). Các test Feature pass được xác định từ kết quả tổng thể 0 failure và được phân biệt với ba trạng thái skipped/incomplete đã nêu. `F` IDs theo thứ tự JUnit XML; `U` IDs theo thứ tự file/method trong Unit source.

Danh mục có **544 lượt test**: 41 Unit và 503 Feature. Theo kết quả chạy, 541 pass, 1 skipped, 2 incomplete, 0 fail. Bảng trọng tâm ở mục 5 trình bày tiền điều kiện, dữ liệu, bước chạy, mong đợi và thực tế cho các scenario trực tiếp của phân vùng; bảng dưới liệt kê đầy đủ tên và trạng thái từng lượt test trong phạm vi.

| Test Case ID | Class::test method | Source file | Trạng thái thực tế |
|---|---|---|---|
| U-001 | `AssetTest::test_auto_increment` | `AssetTest` | Pass |
| U-002 | `AssetTest::test_auto_increment_collision` | `AssetTest` | Pass |
| U-003 | `AssetTest::test_auto_increment_double` | `AssetTest` | Pass |
| U-004 | `AssetTest::test_auto_increment_gap_and_backfill` | `AssetTest` | Pass |
| U-005 | `AssetTest::test_prefixless_autoincrement_backfill` | `AssetTest` | Pass |
| U-006 | `AssetTest::test_unzerofilled_prefixless_autoincrement_backfill` | `AssetTest` | Pass |
| U-007 | `AssetTest::test_auto_increment_big` | `AssetTest` | Pass |
| U-008 | `AssetTest::test_auto_increment_almost_big` | `AssetTest` | Pass |
| U-009 | `AssetTest::test_warranty_expires_attribute` | `AssetTest` | Pass |
| U-010 | `AssetTest::test_eol_progress_percent_returns_expected_value` | `AssetTest` | Pass |
| U-011 | `AssetTest::test_depreciation_progress_percent_returns_expected_value` | `AssetTest` | Pass |
| U-012 | `AssetTest::test_warranty_progress_percent_returns_zero_without_required_dates` | `AssetTest` | Pass |
| U-013 | `AssetTest::test_assigned_type_without_assign_to` | `AssetTest` | Pass |
| U-014 | `AssetTest::test_get_image_url_method` | `AssetTest` | Pass |
| U-015 | `AssetTest::test_undeployable_status_returns_falseif_asset_is_deployable` | `AssetTest` | Pass |
| U-016 | `AssetTest::test_undeployable_status_returns_trueand_tags_if_asset_is_un_deployable` | `AssetTest` | Pass |
| U-017 | `AssetTest::test_undeployable_status_check_filters_out_undeployable_ids` | `AssetTest` | Pass |
| U-018 | `AssetTest::test_asset_accessories_relationship_uses_accessory_checkout_rows` | `AssetTest` | Pass |
| U-019 | `AssetTest::test_asset_components_calculated_total_uses_assigned_quantity` | `AssetTest` | Pass |
| U-020 | `AssetTest::test_asset_components_calculated_total_treats_null_purchase_cost_as_zero` | `AssetTest` | Pass |
| U-021 | `AssetTest::test_asset_tag_unique_constraint` | `AssetTest` | Pass |
| U-022 | `AssetTest::test_encrypted_custom_field_encryption_and_decryption` | `AssetTest` | Pass |
| U-023 | `AssetTest::test_mac_address_custom_field_format_and_storage` | `AssetTest` | Pass |
| U-024 | `AssetTest::test_imei_custom_field_format_and_storage` | `AssetTest` | Pass |
| U-025 | `DepreciationTest::test_a_depreciation_has_models` | `DepreciationTest` | Pass |
| U-026 | `DepreciationTest::test_depreciation_amount` | `DepreciationTest` | Pass |
| U-027 | `DepreciationTest::test_depreciation_percentage` | `DepreciationTest` | Pass |
| U-028 | `DepreciationTest::test_a_depreciation_has_licenses` | `DepreciationTest` | Pass |
| U-029 | `DepreciationTest::test_linear_depreciation_never_negative_and_respects_floor` | `DepreciationTest` | Pass |
| U-030 | `DepreciationTest::test_linear_depreciation_percentage_never_negative` | `DepreciationTest` | Pass |
| U-031 | `DepreciationTest::test_linear_depreciation_no_floor_does_not_go_negative` | `DepreciationTest` | Pass |
| U-032 | `DepreciationTest::test_linear_depreciation_early_period_calculates_correctly` | `DepreciationTest` | Pass |
| U-033 | `AssetModelTest::test_an_asset_model_contains_assets` | `AssetModelTest` | Pass |
| U-034 | `AssetModelTest::test_percent_remaining_returns_zero_when_no_assets_are_available` | `AssetModelTest` | Pass |
| U-035 | `AssetModelTest::test_percent_remaining_returns_expected_ratio_for_mixed_availability` | `AssetModelTest` | Pass |
| U-036 | `AssetModelTest::test_percent_remaining_skips_queries_when_counts_were_eager_loaded` | `AssetModelTest` | Pass |
| U-037 | `AssetModelTest::test_percent_remaining_only_calls_available_assets_count_once` | `AssetModelTest` | Pass |
| U-038 | `AssetModelTest::test_percent_remaining_returns_one_hundred_when_all_assets_are_available` | `AssetModelTest` | Pass |
| U-039 | `AssetModelTest::test_percent_remaining_returns_zero_when_eager_loaded_total_is_zero_but_available_is_not` | `AssetModelTest` | Pass |
| U-040 | `AssetModelTest::test_percent_remaining_returns_zero_when_eager_loaded_counts_are_string_zero` | `AssetModelTest` | Pass |
| U-041 | `AssetModelTest::test_percent_remaining_returns_zero_when_runtime_total_is_zero_but_available_is_not` | `AssetModelTest` | Pass |
| F-001 | `Tests.Feature.Assets.Api.AssetBySerialTest::test_returns_asset_by_serial` | `tests/Feature/Assets/Api/AssetBySerialTest.php` | Pass |
| F-002 | `Tests.Feature.Assets.Api.AssetBySerialTest::test_returns_multiple_assets_with_same_serial` | `tests/Feature/Assets/Api/AssetBySerialTest.php` | Pass |
| F-003 | `Tests.Feature.Assets.Api.AssetBySerialTest::test_returns_error_when_serial_not_found` | `tests/Feature/Assets/Api/AssetBySerialTest.php` | Pass |
| F-004 | `Tests.Feature.Assets.Api.AssetBySerialTest::test_requires_permission` | `tests/Feature/Assets/Api/AssetBySerialTest.php` | Pass |
| F-005 | `Tests.Feature.Assets.Api.AssetBySerialTest::test_does_not_return_deleted_assets_by_default` | `tests/Feature/Assets/Api/AssetBySerialTest.php` | Pass |
| F-006 | `Tests.Feature.Assets.Api.AssetBySerialTest::test_returns_deleted_assets_when_requested` | `tests/Feature/Assets/Api/AssetBySerialTest.php` | Pass |
| F-007 | `Tests.Feature.Assets.Api.AssetBySerialTest::test_serial_with_slashes_works_in_path` | `tests/Feature/Assets/Api/AssetBySerialTest.php` | Pass |
| F-008 | `Tests.Feature.Assets.Api.AssetFilesTest::test_asset_api_accepts_file_upload` | `tests/Feature/Assets/Api/AssetFilesTest.php` | Pass |
| F-009 | `Tests.Feature.Assets.Api.AssetFilesTest::test_asset_api_lists_files` | `tests/Feature/Assets/Api/AssetFilesTest.php` | Pass |
| F-010 | `Tests.Feature.Assets.Api.AssetFilesTest::test_asset_api_downloads_file` | `tests/Feature/Assets/Api/AssetFilesTest.php` | Pass |
| F-011 | `Tests.Feature.Assets.Api.AssetFilesTest::test_asset_api_deletes_file` | `tests/Feature/Assets/Api/AssetFilesTest.php` | Pass |
| F-012 | `Tests.Feature.Assets.Api.AssetHistoryTest::test_encrypted_custom_field_values_are_visible_to_users_with_encrypted_field_permission` | `tests/Feature/Assets/Api/AssetHistoryTest.php` | Pass |
| F-013 | `Tests.Feature.Assets.Api.AssetHistoryTest::test_encrypted_custom_field_values_are_masked_for_users_without_encrypted_field_permission` | `tests/Feature/Assets/Api/AssetHistoryTest.php` | Pass |
| F-014 | `Tests.Feature.Assets.Api.AssetIndexTest::test_asset_api_index_returns_expected_assets` | `tests/Feature/Assets/Api/AssetIndexTest.php` | Pass |
| F-015 | `Tests.Feature.Assets.Api.AssetIndexTest::test_asset_api_index_returns_display_upcoming_audits_due` | `tests/Feature/Assets/Api/AssetIndexTest.php` | Pass |
| F-016 | `Tests.Feature.Assets.Api.AssetIndexTest::test_asset_api_index_returns_overdue_for_audit` | `tests/Feature/Assets/Api/AssetIndexTest.php` | Pass |
| F-017 | `Tests.Feature.Assets.Api.AssetIndexTest::test_asset_api_index_returns_due_or_overdue_for_audit` | `tests/Feature/Assets/Api/AssetIndexTest.php` | Pass |
| F-018 | `Tests.Feature.Assets.Api.AssetIndexTest::test_asset_api_index_returns_due_for_expected_checkin` | `tests/Feature/Assets/Api/AssetIndexTest.php` | Pass |
| F-019 | `Tests.Feature.Assets.Api.AssetIndexTest::test_asset_api_index_returns_overdue_for_expected_checkin` | `tests/Feature/Assets/Api/AssetIndexTest.php` | Pass |
| F-020 | `Tests.Feature.Assets.Api.AssetIndexTest::test_asset_api_index_returns_due_or_overdue_for_expected_checkin` | `tests/Feature/Assets/Api/AssetIndexTest.php` | Pass |
| F-021 | `Tests.Feature.Assets.Api.AssetIndexTest::test_asset_api_index_adheres_to_company_scoping` | `tests/Feature/Assets/Api/AssetIndexTest.php` | Pass |
| F-022 | `Tests.Feature.Assets.Api.AssetIndexTest::test_assets_can_be_filtered_by_custom_field` | `tests/Feature/Assets/Api/AssetIndexTest.php` | Pass |
| F-023 | `Tests.Feature.Assets.Api.AssetIndexTest::test_gracefully_handles_malformed_filter` | `tests/Feature/Assets/Api/AssetIndexTest.php` | Pass |
| F-024 | `Tests.Feature.Assets.Api.AssetIndexTest::test_returns_result_via_filter` | `tests/Feature/Assets/Api/AssetIndexTest.php` | Pass |
| F-025 | `Tests.Feature.Assets.Api.AssetIndexTest::test_sort_by_location_does_not_fall_through_to_rtd_location` | `tests/Feature/Assets/Api/AssetIndexTest.php` | Pass |
| F-026 | `Tests.Feature.Assets.Api.AssetIndexTest::test_deleted_status_type_returns_soft_deleted_assets` | `tests/Feature/Assets/Api/AssetIndexTest.php` | Pass |
| F-027 | `Tests.Feature.Assets.Api.AssetIndexTest::test_default_index_still_excludes_soft_deleted_assets` | `tests/Feature/Assets/Api/AssetIndexTest.php` | Pass |
| F-028 | `Tests.Feature.Assets.Api.AssetNotesTest::test_that_a_non_existent_asset_id_returns_error` | `tests/Feature/Assets/Api/AssetNotesTest.php` | Pass |
| F-029 | `Tests.Feature.Assets.Api.AssetNotesTest::test_requires_permission_to_add_note_to_asset_asset` | `tests/Feature/Assets/Api/AssetNotesTest.php` | Pass |
| F-030 | `Tests.Feature.Assets.Api.AssetNotesTest::test_asset_note_is_saved` | `tests/Feature/Assets/Api/AssetNotesTest.php` | Pass |
| F-031 | `Tests.Feature.Assets.Api.AssetNotesTest::test_asset_notes_are_retrievable` | `tests/Feature/Assets/Api/AssetNotesTest.php` | Pass |
| F-032 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_response_includes_pagination_fields` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-033 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_default_request_returns_page_one` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-034 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_offset_zero_returns_page_one` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-035 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_offset_derives_correct_page_number` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-036 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_page_one_returns_current_page_one` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-037 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_page_two_returns_current_page_two` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-038 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_page_one_returns_first_items` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-039 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_page_two_returns_second_set_of_items` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-040 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_offset_returns_correct_items` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-041 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_page_param_respects_limit` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-042 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_page_beyond_results_returns_empty_rows` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-043 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_offset_takes_precedence_over_page_when_both_provided` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-044 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_per_page_reflects_the_limit_parameter_as_an_integer` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-045 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_prev_page_url_is_null_on_first_page` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-046 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_next_page_url_is_null_on_last_page` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-047 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_next_page_url_contains_correct_page_number` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-048 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_prev_page_url_contains_correct_page_number` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-049 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_page_urls_do_not_include_limit_when_not_in_original_request` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-050 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_both_page_urls_null_when_single_page` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-051 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_total_pages_is_correct_for_even_division` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-052 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_total_pages_rounds_up_for_uneven_division` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-053 | `Tests.Feature.Assets.Api.AssetPaginationTest::test_total_pages_is_one_when_results_fit_in_single_page` | `tests/Feature/Assets/Api/AssetPaginationTest.php` | Pass |
| F-054 | `Tests.Feature.Assets.Api.AssetsForSelectListTest::test_requires_view_selectlists_permission` | `tests/Feature/Assets/Api/AssetsForSelectListTest.php` | Pass |
| F-055 | `Tests.Feature.Assets.Api.AssetsForSelectListTest::test_assets_can_be_searched_for_by_asset_tag` | `tests/Feature/Assets/Api/AssetsForSelectListTest.php` | Pass |
| F-056 | `Tests.Feature.Assets.Api.AssetsForSelectListTest::test_assets_are_scoped_to_company_when_multiple_company_support_enabled` | `tests/Feature/Assets/Api/AssetsForSelectListTest.php` | Pass |
| F-057 | `Tests.Feature.Assets.Api.AssetsForSelectListTest::test_asset_is_excluded_from_selectlist_when_exclude_id_matches` | `tests/Feature/Assets/Api/AssetsForSelectListTest.php` | Pass |
| F-058 | `Tests.Feature.Assets.Api.AssetsForSelectListTest::test_assets_are_filtered_by_multiple_comma_separated_company_ids_when_full_company_support_is_enabled` | `tests/Feature/Assets/Api/AssetsForSelectListTest.php` | Pass |
| F-059 | `Tests.Feature.Assets.Api.AssetsForSelectListTest::test_superuser_bypasses_company_id_filter_on_assets_selectlist` | `tests/Feature/Assets/Api/AssetsForSelectListTest.php` | Pass |
| F-060 | `Tests.Feature.Assets.Api.AssetsForSelectListTest::test_floater_assets_appear_in_selectlist_under_floater_mode_when_narrowed_by_company` | `tests/Feature/Assets/Api/AssetsForSelectListTest.php` | Pass |
| F-061 | `Tests.Feature.Assets.Api.AssetsForSelectListTest::test_floater_assets_are_hidden_in_selectlist_under_strict_mode_when_narrowed_by_company` | `tests/Feature/Assets/Api/AssetsForSelectListTest.php` | Pass |
| F-062 | `Tests.Feature.Assets.Api.AssignedAccessoriesTest::test_requires_permission` | `tests/Feature/Assets/Api/AssignedAccessoriesTest.php` | Pass |
| F-063 | `Tests.Feature.Assets.Api.AssignedAccessoriesTest::test_adheres_to_company_scoping` | `tests/Feature/Assets/Api/AssignedAccessoriesTest.php` | Pass |
| F-064 | `Tests.Feature.Assets.Api.AssignedAccessoriesTest::test_does_not_leak_accessory_details_when_caller_lacks_accessories_view` | `tests/Feature/Assets/Api/AssignedAccessoriesTest.php` | Pass |
| F-065 | `Tests.Feature.Assets.Api.AssignedAccessoriesTest::test_returns_full_accessory_details_when_caller_has_accessories_view` | `tests/Feature/Assets/Api/AssignedAccessoriesTest.php` | Pass |
| F-066 | `Tests.Feature.Assets.Api.AssignedAssetsTest::test_requires_permission` | `tests/Feature/Assets/Api/AssignedAssetsTest.php` | Pass |
| F-067 | `Tests.Feature.Assets.Api.AssignedAssetsTest::test_adheres_to_company_scoping` | `tests/Feature/Assets/Api/AssignedAssetsTest.php` | Pass |
| F-068 | `Tests.Feature.Assets.Api.AssignedAssetsTest::test_can_get_assets_assigned_to_specific_asset` | `tests/Feature/Assets/Api/AssignedAssetsTest.php` | Pass |
| F-069 | `Tests.Feature.Assets.Api.AssignedAssetsTest::test_adheres_to_offset_and_limit` | `tests/Feature/Assets/Api/AssignedAssetsTest.php` | Pass |
| F-070 | `Tests.Feature.Assets.Api.AssignedComponentsTest::test_requires_permission` | `tests/Feature/Assets/Api/AssignedComponentsTest.php` | Pass |
| F-071 | `Tests.Feature.Assets.Api.AssignedComponentsTest::test_adheres_to_company_scoping` | `tests/Feature/Assets/Api/AssignedComponentsTest.php` | Pass |
| F-072 | `Tests.Feature.Assets.Api.AssignedComponentsTest::test_can_get_components_assigned_to_specific_asset` | `tests/Feature/Assets/Api/AssignedComponentsTest.php` | Pass |
| F-073 | `Tests.Feature.Assets.Api.AssignedComponentsTest::test_does_not_leak_component_details_when_caller_lacks_components_view` | `tests/Feature/Assets/Api/AssignedComponentsTest.php` | Pass |
| F-074 | `Tests.Feature.Assets.Api.AssignedComponentsTest::test_returns_full_component_details_when_caller_has_components_view` | `tests/Feature/Assets/Api/AssignedComponentsTest.php` | Pass |
| F-075 | `Tests.Feature.Assets.Api.AssignedComponentsTest::test_show_asset_with_components_true_query_omits_components_when_denied` | `tests/Feature/Assets/Api/AssignedComponentsTest.php` | Pass |
| F-076 | `Tests.Feature.Assets.Api.AuditAssetTest::test_permission_required_to_bulk_audit_assets` | `tests/Feature/Assets/Api/AuditAssetTest.php` | Pass |
| F-077 | `Tests.Feature.Assets.Api.AuditAssetTest::test_that_a_non_existent_asset_id_returns_error` | `tests/Feature/Assets/Api/AuditAssetTest.php` | Pass |
| F-078 | `Tests.Feature.Assets.Api.AuditAssetTest::test_requires_permission_to_audit_asset` | `tests/Feature/Assets/Api/AuditAssetTest.php` | Pass |
| F-079 | `Tests.Feature.Assets.Api.AuditAssetTest::test_legacy_asset_audit_is_saved` | `tests/Feature/Assets/Api/AuditAssetTest.php` | Pass |
| F-080 | `Tests.Feature.Assets.Api.AuditAssetTest::test_legacy_asset_audit_defaults_to_asset_tag_when_audit_by_field_is_not_sent` | `tests/Feature/Assets/Api/AuditAssetTest.php` | Pass |
| F-081 | `Tests.Feature.Assets.Api.AuditAssetTest::test_legacy_asset_audit_can_find_asset_by_serial_when_selected` | `tests/Feature/Assets/Api/AuditAssetTest.php` | Pass |
| F-082 | `Tests.Feature.Assets.Api.AuditAssetTest::test_asset_audit_is_saved` | `tests/Feature/Assets/Api/AuditAssetTest.php` | Pass |
| F-083 | `Tests.Feature.Assets.Api.AuditAssetTest::test_audit_does_not_set_next_audit_date_if_given_null` | `tests/Feature/Assets/Api/AuditAssetTest.php` | Pass |
| F-084 | `Tests.Feature.Assets.Api.AuditAssetTest::test_asset_name_is_cleared_on_audit_when_clear_name_is_set` | `tests/Feature/Assets/Api/AuditAssetTest.php` | Pass |
| F-085 | `Tests.Feature.Assets.Api.AuditAssetTest::test_asset_name_is_not_cleared_on_audit_when_clear_name_is_not_set` | `tests/Feature/Assets/Api/AuditAssetTest.php` | Pass |
| F-086 | `Tests.Feature.Assets.Api.AuditAssetTest::test_audit_persists_uploaded_image_and_stamps_filename_on_log` | `tests/Feature/Assets/Api/AuditAssetTest.php` | Pass |
| F-087 | `Tests.Feature.Assets.Api.AuditAssetTest::test_audit_without_image_does_not_stamp_filename` | `tests/Feature/Assets/Api/AuditAssetTest.php` | Pass |
| F-088 | `Tests.Feature.Assets.Api.AuditAssetTest::test_audit_rejects_php_payload_in_file_upload` | `tests/Feature/Assets/Api/AuditAssetTest.php` | Pass |
| F-089 | `Tests.Feature.Assets.Api.AuditAssetTest::test_audit_rejects_php_payload_in_legacy_image_field` | `tests/Feature/Assets/Api/AuditAssetTest.php` | Pass |
| F-090 | `Tests.Feature.Assets.Api.AuditAssetTest::test_audit_still_accepts_valid_image_posted_via_legacy_image_field` | `tests/Feature/Assets/Api/AuditAssetTest.php` | Pass |
| F-091 | `Tests.Feature.Assets.Api.BulkAuditAssetsTest::test_audits_all_assets_and_returns_per_row_envelope` | `tests/Feature/Assets/Api/BulkAuditAssetsTest.php` | Pass |
| F-092 | `Tests.Feature.Assets.Api.BulkAuditAssetsTest::test_input_order_is_preserved_in_results` | `tests/Feature/Assets/Api/BulkAuditAssetsTest.php` | Pass |
| F-093 | `Tests.Feature.Assets.Api.BulkAuditAssetsTest::test_nonexistent_id_is_reported_as_row_error` | `tests/Feature/Assets/Api/BulkAuditAssetsTest.php` | Pass |
| F-094 | `Tests.Feature.Assets.Api.BulkAuditAssetsTest::test_all_failures_produce_overall_error_status` | `tests/Feature/Assets/Api/BulkAuditAssetsTest.php` | Pass |
| F-095 | `Tests.Feature.Assets.Api.BulkAuditAssetsTest::test_duplicate_ids_are_only_processed_once` | `tests/Feature/Assets/Api/BulkAuditAssetsTest.php` | Pass |
| F-096 | `Tests.Feature.Assets.Api.BulkAuditAssetsTest::test_missing_ids_field_is_a_request_level_validation_error` | `tests/Feature/Assets/Api/BulkAuditAssetsTest.php` | Pass |
| F-097 | `Tests.Feature.Assets.Api.BulkAuditAssetsTest::test_attaches_uploaded_image_to_every_row_audit_log` | `tests/Feature/Assets/Api/BulkAuditAssetsTest.php` | Pass |
| F-098 | `Tests.Feature.Assets.Api.BulkAuditAssetsTest::test_superuser_bypasses_fmcs_and_audits_assets_in_any_company` | `tests/Feature/Assets/Api/BulkAuditAssetsTest.php` | Pass |
| F-099 | `Tests.Feature.Assets.Api.BulkAuditAssetsTest::test_respects_fmcs_scoping_for_non_superuser` | `tests/Feature/Assets/Api/BulkAuditAssetsTest.php` | Pass |
| F-100 | `Tests.Feature.Assets.Api.BulkSelectableTest::test_clean_unassigned_asset_supports_edit_maintenance_checkout_audit_delete_labels` | `tests/Feature/Assets/Api/BulkSelectableTest.php` | Pass |
| F-101 | `Tests.Feature.Assets.Api.BulkSelectableTest::test_assigned_asset_hides_checkout_and_delete_and_shows_checkin` | `tests/Feature/Assets/Api/BulkSelectableTest.php` | Pass |
| F-102 | `Tests.Feature.Assets.Api.BulkSelectableTest::test_soft_deleted_asset_surfaces_only_restore` | `tests/Feature/Assets/Api/BulkSelectableTest.php` | Pass |
| F-103 | `Tests.Feature.Assets.Api.BulkSelectableTest::test_user_without_edit_permission_gets_edit_and_maintenance_false` | `tests/Feature/Assets/Api/BulkSelectableTest.php` | Pass |
| F-104 | `Tests.Feature.Assets.Api.BulkSelectableTest::test_user_without_checkout_permission_gets_checkout_false` | `tests/Feature/Assets/Api/BulkSelectableTest.php` | Pass |
| F-105 | `Tests.Feature.Assets.Api.BulkSelectableTest::test_user_without_checkin_permission_gets_checkin_false` | `tests/Feature/Assets/Api/BulkSelectableTest.php` | Pass |
| F-106 | `Tests.Feature.Assets.Api.BulkSelectableTest::test_user_without_audit_permission_gets_audit_false` | `tests/Feature/Assets/Api/BulkSelectableTest.php` | Pass |
| F-107 | `Tests.Feature.Assets.Api.BulkSelectableTest::test_user_without_delete_permission_gets_delete_false` | `tests/Feature/Assets/Api/BulkSelectableTest.php` | Pass |
| F-108 | `Tests.Feature.Assets.Api.BulkSelectableTest::test_user_without_create_permission_gets_restore_false` | `tests/Feature/Assets/Api/BulkSelectableTest.php` | Pass |
| F-109 | `Tests.Feature.Assets.Api.BulkUpdateAssetsTest::test_requires_permission` | `tests/Feature/Assets/Api/BulkUpdateAssetsTest.php` | Pass |
| F-110 | `Tests.Feature.Assets.Api.BulkUpdateAssetsTest::test_updates_all_assets_and_returns_per_row_envelope` | `tests/Feature/Assets/Api/BulkUpdateAssetsTest.php` | Pass |
| F-111 | `Tests.Feature.Assets.Api.BulkUpdateAssetsTest::test_input_order_is_preserved_in_results` | `tests/Feature/Assets/Api/BulkUpdateAssetsTest.php` | Pass |
| F-112 | `Tests.Feature.Assets.Api.BulkUpdateAssetsTest::test_nonexistent_id_is_reported_as_row_error` | `tests/Feature/Assets/Api/BulkUpdateAssetsTest.php` | Pass |
| F-113 | `Tests.Feature.Assets.Api.BulkUpdateAssetsTest::test_all_failures_produce_overall_error_status` | `tests/Feature/Assets/Api/BulkUpdateAssetsTest.php` | Pass |
| F-114 | `Tests.Feature.Assets.Api.BulkUpdateAssetsTest::test_duplicate_ids_are_only_processed_once` | `tests/Feature/Assets/Api/BulkUpdateAssetsTest.php` | Pass |
| F-115 | `Tests.Feature.Assets.Api.BulkUpdateAssetsTest::test_invalid_field_fans_the_validation_error_out_to_every_row` | `tests/Feature/Assets/Api/BulkUpdateAssetsTest.php` | Pass |
| F-116 | `Tests.Feature.Assets.Api.BulkUpdateAssetsTest::test_missing_ids_field_is_a_request_level_validation_error` | `tests/Feature/Assets/Api/BulkUpdateAssetsTest.php` | Pass |
| F-117 | `Tests.Feature.Assets.Api.BulkUpdateAssetsTest::test_custom_field_can_be_updated_across_assets_sharing_a_model` | `tests/Feature/Assets/Api/BulkUpdateAssetsTest.php` | Pass |
| F-118 | `Tests.Feature.Assets.Api.BulkUpdateAssetsTest::test_custom_field_absent_from_some_models_is_silently_ignored_on_those_rows` | `tests/Feature/Assets/Api/BulkUpdateAssetsTest.php` | Pass |
| F-119 | `Tests.Feature.Assets.Api.BulkUpdateAssetsTest::test_unknown_custom_field_column_is_still_a_request_level_validation_error` | `tests/Feature/Assets/Api/BulkUpdateAssetsTest.php` | Pass |
| F-120 | `Tests.Feature.Assets.Api.BulkUpdateAssetsTest::test_superuser_bypasses_fmcs_and_updates_assets_in_any_company` | `tests/Feature/Assets/Api/BulkUpdateAssetsTest.php` | Pass |
| F-121 | `Tests.Feature.Assets.Api.BulkUpdateAssetsTest::test_respects_fmcs_scoping_for_non_superuser` | `tests/Feature/Assets/Api/BulkUpdateAssetsTest.php` | Pass |
| F-122 | `Tests.Feature.Assets.Api.DeleteAssetsTest::test_requires_permission` | `tests/Feature/Assets/Api/DeleteAssetsTest.php` | Pass |
| F-123 | `Tests.Feature.Assets.Api.DeleteAssetsTest::test_adheres_to_full_multiple_companies_support_scoping` | `tests/Feature/Assets/Api/DeleteAssetsTest.php` | Pass |
| F-124 | `Tests.Feature.Assets.Api.DeleteAssetsTest::test_cannot_delete_asset_that_is_checked_out` | `tests/Feature/Assets/Api/DeleteAssetsTest.php` | Skipped |
| F-125 | `Tests.Feature.Assets.Api.DeleteAssetsTest::test_can_delete_asset` | `tests/Feature/Assets/Api/DeleteAssetsTest.php` | Pass |
| F-126 | `Tests.Feature.Assets.Api.RequestableAssetTest::test_viewing_requestable_assets_requires_correct_permission` | `tests/Feature/Assets/Api/RequestableAssetTest.php` | Pass |
| F-127 | `Tests.Feature.Assets.Api.RequestableAssetTest::test_returns_requestable_assets` | `tests/Feature/Assets/Api/RequestableAssetTest.php` | Pass |
| F-128 | `Tests.Feature.Assets.Api.RequestableAssetTest::test_requestable_assets_are_scoped_to_company_when_multiple_company_support_enabled` | `tests/Feature/Assets/Api/RequestableAssetTest.php` | Pass |
| F-129 | `Tests.Feature.Assets.Api.StoreAssetCheckoutPermissionTest::test_create_with_assigned_user_without_checkout_permission_creates_asset_but_skips_checkout` | `tests/Feature/Assets/Api/StoreAssetCheckoutPermissionTest.php` | Pass |
| F-130 | `Tests.Feature.Assets.Api.StoreAssetCheckoutPermissionTest::test_create_with_assigned_asset_without_checkout_permission_creates_asset_but_skips_checkout` | `tests/Feature/Assets/Api/StoreAssetCheckoutPermissionTest.php` | Pass |
| F-131 | `Tests.Feature.Assets.Api.StoreAssetCheckoutPermissionTest::test_create_with_assigned_location_without_checkout_permission_creates_asset_but_skips_checkout` | `tests/Feature/Assets/Api/StoreAssetCheckoutPermissionTest.php` | Pass |
| F-132 | `Tests.Feature.Assets.Api.StoreAssetCheckoutPermissionTest::test_create_without_assignment_returns_default_success_message` | `tests/Feature/Assets/Api/StoreAssetCheckoutPermissionTest.php` | Pass |
| F-133 | `Tests.Feature.Assets.Api.StoreAssetCheckoutPermissionTest::test_create_with_assignment_succeeds_and_checks_out_when_checkout_permission_granted` | `tests/Feature/Assets/Api/StoreAssetCheckoutPermissionTest.php` | Pass |
| F-134 | `Tests.Feature.Assets.Api.StoreAssetTest::test_requires_permission_to_create_asset` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-135 | `Tests.Feature.Assets.Api.StoreAssetTest::test_all_asset_attributes_are_stored` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-136 | `Tests.Feature.Assets.Api.StoreAssetTest::test_sets_last_audit_date_to_midnight_of_provided_date` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-137 | `Tests.Feature.Assets.Api.StoreAssetTest::test_last_audit_date_can_be_null` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-138 | `Tests.Feature.Assets.Api.StoreAssetTest::test_non_date_used_for_last_audit_date_returns_validation_error` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-139 | `Tests.Feature.Assets.Api.StoreAssetTest::test_save_with_archived_status_and_user_returns_validation_error` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-140 | `Tests.Feature.Assets.Api.StoreAssetTest::test_save_with_pending_status_and_user_returns_validation_error` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-141 | `Tests.Feature.Assets.Api.StoreAssetTest::test_raw_assigned_to_pair_is_ignored_on_store` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-142 | `Tests.Feature.Assets.Api.StoreAssetTest::test_raw_assigned_to_without_assigned_type_is_ignored_on_store` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-143 | `Tests.Feature.Assets.Api.StoreAssetTest::test_raw_assigned_to_with_bad_assigned_type_is_ignored_on_store` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-144 | `Tests.Feature.Assets.Api.StoreAssetTest::test_raw_assigned_type_without_assigned_to_is_ignored_on_store` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-145 | `Tests.Feature.Assets.Api.StoreAssetTest::test_save_with_pending_status_without_user_is_successful` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-146 | `Tests.Feature.Assets.Api.StoreAssetTest::test_archived_depreciate_and_physical_can_be_null` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-147 | `Tests.Feature.Assets.Api.StoreAssetTest::test_archived_depreciate_and_physical_can_be_empty` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-148 | `Tests.Feature.Assets.Api.StoreAssetTest::test_asset_eol_date_is_calculated_if_purchase_date_set` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-149 | `Tests.Feature.Assets.Api.StoreAssetTest::test_asset_eol_date_is_not_calculated_if_purchase_date_not_set` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-150 | `Tests.Feature.Assets.Api.StoreAssetTest::test_asset_eol_explicit_is_set_if_asset_eol_date_is_explicitly_set` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-151 | `Tests.Feature.Assets.Api.StoreAssetTest::test_asset_gets_asset_tag_with_auto_increment` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-152 | `Tests.Feature.Assets.Api.StoreAssetTest::test_asset_creation_fails_with_no_asset_tag_or_auto_increment` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-153 | `Tests.Feature.Assets.Api.StoreAssetTest::test_stores_period_as_decimal_separator_for_purchase_cost` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-154 | `Tests.Feature.Assets.Api.StoreAssetTest::test_stores_period_as_comma_separator_for_purchase_cost` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-155 | `Tests.Feature.Assets.Api.StoreAssetTest::test_unique_serial_numbers_is_enforced_when_enabled` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-156 | `Tests.Feature.Assets.Api.StoreAssetTest::test_unique_serial_numbers_is_not_enforced_when_disabled` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-157 | `Tests.Feature.Assets.Api.StoreAssetTest::test_asset_tags_must_be_unique_when_undeleted` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-158 | `Tests.Feature.Assets.Api.StoreAssetTest::test_asset_tags_can_be_duplicated_if_deleted` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-159 | `Tests.Feature.Assets.Api.StoreAssetTest::test_an_asset_can_be_checked_out_to_user_on_store` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-160 | `Tests.Feature.Assets.Api.StoreAssetTest::test_store_rejects_cross_company_checkout_target_with_full_company_support_enabled` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-161 | `Tests.Feature.Assets.Api.StoreAssetTest::test_assigned_field_validation_cannot_be_array with data set "Users"` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-162 | `Tests.Feature.Assets.Api.StoreAssetTest::test_assigned_field_validation_cannot_be_array with data set "Locations"` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-163 | `Tests.Feature.Assets.Api.StoreAssetTest::test_assigned_field_validation_cannot_be_array with data set "Assets"` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-164 | `Tests.Feature.Assets.Api.StoreAssetTest::test_an_asset_can_be_checked_out_to_location_on_store` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-165 | `Tests.Feature.Assets.Api.StoreAssetTest::test_an_asset_can_be_checked_out_to_asset_on_store` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-166 | `Tests.Feature.Assets.Api.StoreAssetTest::test_company_id_needs_to_be_integer` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-167 | `Tests.Feature.Assets.Api.StoreAssetTest::test_serial_validation` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-168 | `Tests.Feature.Assets.Api.StoreAssetTest::test_encrypted_custom_field_can_be_stored` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-169 | `Tests.Feature.Assets.Api.StoreAssetTest::test_encrypted_custom_field_validation_passes` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-170 | `Tests.Feature.Assets.Api.StoreAssetTest::test_encrypted_custom_field_validation_fails` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-171 | `Tests.Feature.Assets.Api.StoreAssetTest::test_permission_needed_to_store_encrypted_field` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Incomplete |
| F-172 | `Tests.Feature.Assets.Api.StoreAssetTest::test_base64_asset_images` | `tests/Feature/Assets/Api/StoreAssetTest.php` | Pass |
| F-173 | `Tests.Feature.Assets.Api.StoreAssetWithFullMultipleCompanySupportTest::test_adheres_to_full_multiple_companies_support_scoping with data set "User in a company should result in user's company_id being used"` | `tests/Feature/Assets/Api/StoreAssetWithFullMultipleCompanySupportTest.php` | Pass |
| F-174 | `Tests.Feature.Assets.Api.StoreAssetWithFullMultipleCompanySupportTest::test_adheres_to_full_multiple_companies_support_scoping with data set "User without a company should result in company_id being null"` | `tests/Feature/Assets/Api/StoreAssetWithFullMultipleCompanySupportTest.php` | Pass |
| F-175 | `Tests.Feature.Assets.Api.StoreAssetWithFullMultipleCompanySupportTest::test_adheres_to_full_multiple_companies_support_scoping with data set "Super-User assigning across companies should result in company_id being set to what was provided"` | `tests/Feature/Assets/Api/StoreAssetWithFullMultipleCompanySupportTest.php` | Pass |
| F-176 | `Tests.Feature.Assets.Api.StoreAssetWithFullMultipleCompanySupportTest::test_handles_company_id_being_string with data set "User in a company should result in user's company_id being used"` | `tests/Feature/Assets/Api/StoreAssetWithFullMultipleCompanySupportTest.php` | Pass |
| F-177 | `Tests.Feature.Assets.Api.StoreAssetWithFullMultipleCompanySupportTest::test_handles_company_id_being_string with data set "User without a company should result in company_id being null"` | `tests/Feature/Assets/Api/StoreAssetWithFullMultipleCompanySupportTest.php` | Pass |
| F-178 | `Tests.Feature.Assets.Api.StoreAssetWithFullMultipleCompanySupportTest::test_handles_company_id_being_string with data set "Super-User assigning across companies should result in company_id being set to what was provided"` | `tests/Feature/Assets/Api/StoreAssetWithFullMultipleCompanySupportTest.php` | Pass |
| F-179 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_that_a_non_existent_asset_id_returns_error` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-180 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_requires_permission_to_update_asset` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-181 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_given_permission_update_asset_is_allowed` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-182 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_all_asset_attributes_are_stored` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-183 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_updates_period_as_comma_separator_for_purchase_cost` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-184 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_updates_float_for_purchase_cost` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-185 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_updates_us_decimal_for_purchase_cost` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-186 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_updates_float_us_decimal_for_purchase_cost` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-187 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_asset_eol_date_is_calculated_if_purchase_date_updated` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-188 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_asset_eol_date_is_not_calculated_if_purchase_date_not_set` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-189 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_asset_eol_explicit_is_set_if_asset_eol_date_is_explicitly_set` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-190 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_asset_tag_cannot_update_to_null_value` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-191 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_asset_tag_cannot_update_to_empty_string_value` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-192 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_model_id_cannot_update_to_null_value` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-193 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_model_id_cannot_update_to_empty_string_value` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-194 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_status_id_cannot_update_to_null_value` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-195 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_status_id_cannot_update_to_empty_string_value` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-196 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_if_rtd_location_id_is_set_without_location_id_asset_returns_to_default` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-197 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_if_location_and_rtd_location_are_set_location_id_is_location` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-198 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_encrypted_custom_field_can_be_updated` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-199 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_permission_needed_to_update_encrypted_field` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-200 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_checkout_to_user_on_asset_update` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-201 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_update_rejects_cross_company_checkout_target_with_full_company_support_enabled` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-202 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_raw_assigned_to_pair_is_ignored_on_update` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-203 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_raw_assigned_to_without_assigned_type_is_ignored_on_update` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-204 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_raw_assigned_to_with_bad_assigned_type_is_ignored_on_update` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-205 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_raw_assigned_type_without_assigned_to_is_ignored_on_update` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-206 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_checkout_to_deleted_user_fails_on_asset_update` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-207 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_checkout_to_location_on_asset_update` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-208 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_checkout_to_deleted_location_fails_on_asset_update` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-209 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_checkout_asset_on_asset_update` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-210 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_checkout_to_deleted_asset_fails_on_asset_update` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-211 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_asset_cannot_be_updated_by_user_in_separate_company` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-212 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_custom_field_cannot_be_updated_if_not_on_current_asset_model` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-213 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_updating_next_audit_date_creates_update_log_entry` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-214 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_updating_next_audit_date_with_other_fields_logs_all_changes` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-215 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_update_denies_checkout_when_actor_lacks_checkout_permission` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-216 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_update_denies_reassignment_of_already_checked_out_asset` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-217 | `Tests.Feature.Assets.Api.UpdateAssetTest::test_update_denies_checkout_when_asset_status_is_not_deployable` | `tests/Feature/Assets/Api/UpdateAssetTest.php` | Pass |
| F-218 | `Tests.Feature.Assets.Api.UploadedFilesInlineTest::test_xml_upload_requested_inline_is_forced_to_attachment` | `tests/Feature/Assets/Api/UploadedFilesInlineTest.php` | Pass |
| F-219 | `Tests.Feature.Assets.Api.UploadedFilesInlineTest::test_html_upload_requested_inline_is_forced_to_attachment` | `tests/Feature/Assets/Api/UploadedFilesInlineTest.php` | Pass |
| F-220 | `Tests.Feature.Assets.Api.UploadedFilesInlineTest::test_png_upload_requested_inline_is_served_inline_with_nosniff` | `tests/Feature/Assets/Api/UploadedFilesInlineTest.php` | Pass |
| F-221 | `Tests.Feature.Assets.Api.UploadedFilesInlineTest::test_xml_disguised_as_png_extension_is_forced_to_attachment` | `tests/Feature/Assets/Api/UploadedFilesInlineTest.php` | Pass |
| F-222 | `Tests.Feature.Assets.Api.UploadedFilesNoteSanitizationTest::test_note_containing_html_tags_is_stripped_before_transport` | `tests/Feature/Assets/Api/UploadedFilesNoteSanitizationTest.php` | Pass |
| F-223 | `Tests.Feature.Assets.Api.UploadedFilesNoteSanitizationTest::test_note_containing_script_tag_is_stripped_before_transport` | `tests/Feature/Assets/Api/UploadedFilesNoteSanitizationTest.php` | Pass |
| F-224 | `Tests.Feature.Assets.Api.UploadedFilesNoteSanitizationTest::test_note_with_plain_text_and_punctuation_survives_intact` | `tests/Feature/Assets/Api/UploadedFilesNoteSanitizationTest.php` | Pass |
| F-225 | `Tests.Feature.Assets.Api.UploadedFilesNoteSanitizationTest::test_empty_note_remains_null` | `tests/Feature/Assets/Api/UploadedFilesNoteSanitizationTest.php` | Pass |
| F-226 | `Tests.Feature.Assets.Api.UploadedFilesPaginationTest::test_page_one_returns_first_items` | `tests/Feature/Assets/Api/UploadedFilesPaginationTest.php` | Pass |
| F-227 | `Tests.Feature.Assets.Api.UploadedFilesPaginationTest::test_page_two_returns_second_set_of_items` | `tests/Feature/Assets/Api/UploadedFilesPaginationTest.php` | Pass |
| F-228 | `Tests.Feature.Assets.Api.UploadedFilesPaginationTest::test_offset_returns_correct_items` | `tests/Feature/Assets/Api/UploadedFilesPaginationTest.php` | Pass |
| F-229 | `Tests.Feature.Assets.Api.UploadedFilesPaginationTest::test_page_param_respects_limit` | `tests/Feature/Assets/Api/UploadedFilesPaginationTest.php` | Pass |
| F-230 | `Tests.Feature.Assets.Api.UploadedFilesPaginationTest::test_search_filter_reflects_filtered_total_not_unfiltered_count` | `tests/Feature/Assets/Api/UploadedFilesPaginationTest.php` | Pass |
| F-231 | `Tests.Feature.Assets.Api.UploadedFilesPaginationTest::test_page_beyond_results_returns_empty_rows` | `tests/Feature/Assets/Api/UploadedFilesPaginationTest.php` | Pass |
| F-232 | `Tests.Feature.Assets.AssetBarcodeAuthorizationTest::test_barcode_route_requires_asset_view_permission` | `tests/Feature/Assets/AssetBarcodeAuthorizationTest.php` | Pass |
| F-233 | `Tests.Feature.Assets.AssetBarcodeAuthorizationTest::test_barcode_route_allows_asset_viewer` | `tests/Feature/Assets/AssetBarcodeAuthorizationTest.php` | Pass |
| F-234 | `Tests.Feature.Assets.AssetObserverOrderTest::test_asset_create_with_order_number_writes_matching_order_and_order_item` | `tests/Feature/Assets/AssetObserverOrderTest.php` | Pass |
| F-235 | `Tests.Feature.Assets.AssetObserverOrderTest::test_two_assets_with_same_order_tuple_share_one_order_row` | `tests/Feature/Assets/AssetObserverOrderTest.php` | Pass |
| F-236 | `Tests.Feature.Assets.AssetObserverOrderTest::test_blank_order_number_creates_a_distinct_order_per_asset` | `tests/Feature/Assets/AssetObserverOrderTest.php` | Pass |
| F-237 | `Tests.Feature.Assets.AssetObserverOrderTest::test_asset_purchase_cost_change_syncs_to_linked_order_item` | `tests/Feature/Assets/AssetObserverOrderTest.php` | Pass |
| F-238 | `Tests.Feature.Assets.AssetObserverOrderTest::test_asset_order_number_change_repoints_line_and_cleans_up_orphan` | `tests/Feature/Assets/AssetObserverOrderTest.php` | Pass |
| F-239 | `Tests.Feature.Assets.AssetObserverOrderTest::test_asset_order_number_change_preserves_old_order_when_it_has_siblings` | `tests/Feature/Assets/AssetObserverOrderTest.php` | Pass |
| F-240 | `Tests.Feature.Assets.AssetObserverOrderTest::test_unrelated_save_does_not_touch_the_linked_order_item` | `tests/Feature/Assets/AssetObserverOrderTest.php` | Pass |
| F-241 | `Tests.Feature.Assets.AssetObserverSyncAdapterCleanupTest::test_soft_deleting_an_asset_removes_its_sync_adapter_identity_rows` | `tests/Feature/Assets/AssetObserverSyncAdapterCleanupTest.php` | Pass |
| F-242 | `Tests.Feature.Assets.AssetObserverSyncAdapterCleanupTest::test_deleting_an_asset_with_multiple_source_rows_removes_all_of_them` | `tests/Feature/Assets/AssetObserverSyncAdapterCleanupTest.php` | Pass |
| F-243 | `Tests.Feature.Assets.AssetZeroIdNormalizationTest::test_setting_location_id_to_zero_normalizes_to_null` | `tests/Feature/Assets/AssetZeroIdNormalizationTest.php` | Pass |
| F-244 | `Tests.Feature.Assets.AssetZeroIdNormalizationTest::test_setting_company_id_to_zero_normalizes_to_null` | `tests/Feature/Assets/AssetZeroIdNormalizationTest.php` | Pass |
| F-245 | `Tests.Feature.Assets.AssetZeroIdNormalizationTest::test_legacy_zero_stored_in_db_reads_as_null` | `tests/Feature/Assets/AssetZeroIdNormalizationTest.php` | Pass |
| F-246 | `Tests.Feature.Assets.AssetZeroIdNormalizationTest::test_real_ids_still_persist_and_read_normally` | `tests/Feature/Assets/AssetZeroIdNormalizationTest.php` | Pass |
| F-247 | `Tests.Feature.Assets.Fmcs.CrossCompanyAuditAttemptTest::test_ui_bulk_audit_does_not_audit_asset_in_forbidden_company` | `tests/Feature/Assets/Fmcs/CrossCompanyAuditAttemptTest.php` | Pass |
| F-248 | `Tests.Feature.Assets.Fmcs.CrossCompanyAuditAttemptTest::test_web_single_audit_store_does_not_write_audit_for_forbidden_asset` | `tests/Feature/Assets/Fmcs/CrossCompanyAuditAttemptTest.php` | Pass |
| F-249 | `Tests.Feature.Assets.Fmcs.CrossCompanyAuditAttemptTest::test_api_single_audit_does_not_write_audit_for_forbidden_asset` | `tests/Feature/Assets/Fmcs/CrossCompanyAuditAttemptTest.php` | Pass |
| F-250 | `Tests.Feature.Assets.Fmcs.CrossCompanyAuditAttemptTest::test_api_legacy_body_lookup_audit_does_not_resolve_forbidden_asset` | `tests/Feature/Assets/Fmcs/CrossCompanyAuditAttemptTest.php` | Pass |
| F-251 | `Tests.Feature.Assets.Fmcs.CrossCompanyAuditAttemptTest::test_api_bulk_audit_does_not_audit_forbidden_asset` | `tests/Feature/Assets/Fmcs/CrossCompanyAuditAttemptTest.php` | Pass |
| F-252 | `Tests.Feature.Assets.Ui.AssetBySerialTest::test_redirects_to_asset_when_serial_in_path` | `tests/Feature/Assets/Ui/AssetBySerialTest.php` | Pass |
| F-253 | `Tests.Feature.Assets.Ui.AssetBySerialTest::test_redirects_to_asset_when_serial_in_query_string` | `tests/Feature/Assets/Ui/AssetBySerialTest.php` | Pass |
| F-254 | `Tests.Feature.Assets.Ui.AssetBySerialTest::test_redirects_to_index_when_serial_not_found` | `tests/Feature/Assets/Ui/AssetBySerialTest.php` | Pass |
| F-255 | `Tests.Feature.Assets.Ui.AssetBySerialTest::test_requires_permission` | `tests/Feature/Assets/Ui/AssetBySerialTest.php` | Pass |
| F-256 | `Tests.Feature.Assets.Ui.AssetIndexTest::test_page_renders` | `tests/Feature/Assets/Ui/AssetIndexTest.php` | Pass |
| F-257 | `Tests.Feature.Assets.Ui.AssetIndexTest::test_page_renders_with_array_query_inputs` | `tests/Feature/Assets/Ui/AssetIndexTest.php` | Pass |
| F-258 | `Tests.Feature.Assets.Ui.AssetIndexTest::test_page_renders_when_status_type_is_an_associative_array_probe` | `tests/Feature/Assets/Ui/AssetIndexTest.php` | Pass |
| F-259 | `Tests.Feature.Assets.Ui.AssetLabelTest::test_user_with_permissions_can_access_page` | `tests/Feature/Assets/Ui/AssetLabelTest.php` | Pass |
| F-260 | `Tests.Feature.Assets.Ui.AssetLabelTest::test_redirect_of_no_assets_selected` | `tests/Feature/Assets/Ui/AssetLabelTest.php` | Pass |
| F-261 | `Tests.Feature.Assets.Ui.AuditAssetTest::test_permission_required_to_view_audit_create_page` | `tests/Feature/Assets/Ui/AuditAssetTest.php` | Pass |
| F-262 | `Tests.Feature.Assets.Ui.AuditAssetTest::test_page_can_be_accessed` | `tests/Feature/Assets/Ui/AuditAssetTest.php` | Pass |
| F-263 | `Tests.Feature.Assets.Ui.AuditAssetTest::test_permission_required_to_audit_asset` | `tests/Feature/Assets/Ui/AuditAssetTest.php` | Pass |
| F-264 | `Tests.Feature.Assets.Ui.AuditAssetTest::test_audit_page_is_given_todays_date_when_audit_interval_is_null` | `tests/Feature/Assets/Ui/AuditAssetTest.php` | Pass |
| F-265 | `Tests.Feature.Assets.Ui.AuditAssetTest::test_audit_page_is_given_correct_date_when_audit_interval_is_set` | `tests/Feature/Assets/Ui/AuditAssetTest.php` | Pass |
| F-266 | `Tests.Feature.Assets.Ui.AuditAssetTest::test_asset_can_be_audited` | `tests/Feature/Assets/Ui/AuditAssetTest.php` | Pass |
| F-267 | `Tests.Feature.Assets.Ui.AuditAssetTest::test_asset_location_can_be_updated_when_auditing` | `tests/Feature/Assets/Ui/AuditAssetTest.php` | Pass |
| F-268 | `Tests.Feature.Assets.Ui.AuditAssetTest::test_asset_audit_post_is_redirected_to_asset_index_if_redirect_selection_is_index` | `tests/Feature/Assets/Ui/AuditAssetTest.php` | Pass |
| F-269 | `Tests.Feature.Assets.Ui.AuditAssetTest::test_asset_audit_post_is_redirected_to_asset_page_if_redirect_selection_is_asset` | `tests/Feature/Assets/Ui/AuditAssetTest.php` | Pass |
| F-270 | `Tests.Feature.Assets.Ui.AuditAssetTest::test_asset_audit_post_is_redirected_to_audit_due_page_if_redirect_selection_is_list` | `tests/Feature/Assets/Ui/AuditAssetTest.php` | Pass |
| F-271 | `Tests.Feature.Assets.Ui.BulkAuditAssetsTest::test_permission_required_to_view_page` | `tests/Feature/Assets/Ui/BulkAuditAssetsTest.php` | Pass |
| F-272 | `Tests.Feature.Assets.Ui.BulkAuditAssetsTest::test_can_view_audit_page` | `tests/Feature/Assets/Ui/BulkAuditAssetsTest.php` | Pass |
| F-273 | `Tests.Feature.Assets.Ui.BulkAuditAssetsTest::test_bulk_audit_page_is_given_todays_date_when_audit_interval_is_null` | `tests/Feature/Assets/Ui/BulkAuditAssetsTest.php` | Pass |
| F-274 | `Tests.Feature.Assets.Ui.BulkAuditAssetsTest::test_bulk_audit_page_is_given_correct_date_when_audit_interval_is_set` | `tests/Feature/Assets/Ui/BulkAuditAssetsTest.php` | Pass |
| F-275 | `Tests.Feature.Assets.Ui.BulkAuditSelectedAssetsSubmissionTest::test_permission_required_to_submit` | `tests/Feature/Assets/Ui/BulkAuditSelectedAssetsSubmissionTest.php` | Pass |
| F-276 | `Tests.Feature.Assets.Ui.BulkAuditSelectedAssetsSubmissionTest::test_submitting_without_selected_assets_bounces_back` | `tests/Feature/Assets/Ui/BulkAuditSelectedAssetsSubmissionTest.php` | Pass |
| F-277 | `Tests.Feature.Assets.Ui.BulkAuditSelectedAssetsSubmissionTest::test_bulk_audit_writes_one_audit_actionlog_per_asset` | `tests/Feature/Assets/Ui/BulkAuditSelectedAssetsSubmissionTest.php` | Pass |
| F-278 | `Tests.Feature.Assets.Ui.BulkAuditSelectedAssetsSubmissionTest::test_bulk_audit_updates_next_audit_date_when_provided` | `tests/Feature/Assets/Ui/BulkAuditSelectedAssetsSubmissionTest.php` | Pass |
| F-279 | `Tests.Feature.Assets.Ui.BulkAuditSelectedAssetsSubmissionTest::test_bulk_audit_updates_asset_location_when_update_location_opt_in_is_checked` | `tests/Feature/Assets/Ui/BulkAuditSelectedAssetsSubmissionTest.php` | Pass |
| F-280 | `Tests.Feature.Assets.Ui.BulkAuditSelectedAssetsSubmissionTest::test_bulk_audit_does_not_move_assets_when_update_location_is_unchecked` | `tests/Feature/Assets/Ui/BulkAuditSelectedAssetsSubmissionTest.php` | Pass |
| F-281 | `Tests.Feature.Assets.Ui.BulkAuditSelectedAssetsSubmissionTest::test_bulk_audit_attaches_uploaded_image_to_every_row` | `tests/Feature/Assets/Ui/BulkAuditSelectedAssetsSubmissionTest.php` | Pass |
| F-282 | `Tests.Feature.Assets.Ui.BulkAuditSelectedAssetsSubmissionTest::test_bulk_audit_rejects_unresolvable_location_before_touching_any_asset` | `tests/Feature/Assets/Ui/BulkAuditSelectedAssetsSubmissionTest.php` | Pass |
| F-283 | `Tests.Feature.Assets.Ui.BulkAuditSelectedAssetsTest::test_permission_required_to_view_form` | `tests/Feature/Assets/Ui/BulkAuditSelectedAssetsTest.php` | Pass |
| F-284 | `Tests.Feature.Assets.Ui.BulkAuditSelectedAssetsTest::test_auditor_can_view_form` | `tests/Feature/Assets/Ui/BulkAuditSelectedAssetsTest.php` | Pass |
| F-285 | `Tests.Feature.Assets.Ui.BulkAuditSelectedAssetsTest::test_bulk_actions_dispatcher_redirects_to_show_with_selected_assets_flashed` | `tests/Feature/Assets/Ui/BulkAuditSelectedAssetsTest.php` | Pass |
| F-286 | `Tests.Feature.Assets.Ui.BulkAuditSelectedAssetsTest::test_form_prefills_next_audit_date_from_audit_interval` | `tests/Feature/Assets/Ui/BulkAuditSelectedAssetsTest.php` | Pass |
| F-287 | `Tests.Feature.Assets.Ui.BulkAuditSelectedAssetsTest::test_form_leaves_next_audit_date_blank_when_no_audit_interval_configured` | `tests/Feature/Assets/Ui/BulkAuditSelectedAssetsTest.php` | Pass |
| F-288 | `Tests.Feature.Assets.Ui.BulkAuditSelectedAssetsTest::test_location_controls_render_unscoped_when_location_scoping_is_off` | `tests/Feature/Assets/Ui/BulkAuditSelectedAssetsTest.php` | Pass |
| F-289 | `Tests.Feature.Assets.Ui.BulkAuditSelectedAssetsTest::test_location_controls_scope_to_shared_company_when_selection_is_uniform` | `tests/Feature/Assets/Ui/BulkAuditSelectedAssetsTest.php` | Pass |
| F-290 | `Tests.Feature.Assets.Ui.BulkAuditSelectedAssetsTest::test_location_controls_hide_when_selection_spans_multiple_companies` | `tests/Feature/Assets/Ui/BulkAuditSelectedAssetsTest.php` | Pass |
| F-291 | `Tests.Feature.Assets.Ui.BulkDeleteAssetsTest::test_user_with_permissions_can_access_page` | `tests/Feature/Assets/Ui/BulkDeleteAssetsTest.php` | Pass |
| F-292 | `Tests.Feature.Assets.Ui.BulkDeleteAssetsTest::test_standard_user_cannot_access_page` | `tests/Feature/Assets/Ui/BulkDeleteAssetsTest.php` | Pass |
| F-293 | `Tests.Feature.Assets.Ui.BulkDeleteAssetsTest::test_page_redirect_from_interstitial_if_no_assets_selected_to_delete` | `tests/Feature/Assets/Ui/BulkDeleteAssetsTest.php` | Pass |
| F-294 | `Tests.Feature.Assets.Ui.BulkDeleteAssetsTest::test_page_redirect_from_interstitial_if_no_assets_selected_to_restore` | `tests/Feature/Assets/Ui/BulkDeleteAssetsTest.php` | Pass |
| F-295 | `Tests.Feature.Assets.Ui.BulkDeleteAssetsTest::test_bulk_delete_selected_assets_from_interstitial` | `tests/Feature/Assets/Ui/BulkDeleteAssetsTest.php` | Pass |
| F-296 | `Tests.Feature.Assets.Ui.BulkDeleteAssetsTest::test_bulk_restore_selected_assets_from_interstitial` | `tests/Feature/Assets/Ui/BulkDeleteAssetsTest.php` | Pass |
| F-297 | `Tests.Feature.Assets.Ui.BulkDeleteAssetsTest::test_action_log_created_upon_bulk_delete` | `tests/Feature/Assets/Ui/BulkDeleteAssetsTest.php` | Pass |
| F-298 | `Tests.Feature.Assets.Ui.BulkDeleteAssetsTest::test_action_log_created_upon_bulk_restore` | `tests/Feature/Assets/Ui/BulkDeleteAssetsTest.php` | Pass |
| F-299 | `Tests.Feature.Assets.Ui.BulkDeleteAssetsTest::test_bulk_delete_assigned_asset_triggers_error` | `tests/Feature/Assets/Ui/BulkDeleteAssetsTest.php` | Pass |
| F-300 | `Tests.Feature.Assets.Ui.BulkDeleteAssetsTest::test_bulk_delete_redirects_to_stashed_same_origin_url` | `tests/Feature/Assets/Ui/BulkDeleteAssetsTest.php` | Pass |
| F-301 | `Tests.Feature.Assets.Ui.BulkDeleteAssetsTest::test_bulk_delete_ignores_poisoned_stashed_url` | `tests/Feature/Assets/Ui/BulkDeleteAssetsTest.php` | Pass |
| F-302 | `Tests.Feature.Assets.Ui.BulkEditAssetsTest::test_user_with_permissions_can_access_page` | `tests/Feature/Assets/Ui/BulkEditAssetsTest.php` | Pass |
| F-303 | `Tests.Feature.Assets.Ui.BulkEditAssetsTest::test_handles_model_being_deleted` | `tests/Feature/Assets/Ui/BulkEditAssetsTest.php` | Pass |
| F-304 | `Tests.Feature.Assets.Ui.BulkEditAssetsTest::test_standard_user_cannot_access_page` | `tests/Feature/Assets/Ui/BulkEditAssetsTest.php` | Pass |
| F-305 | `Tests.Feature.Assets.Ui.BulkEditAssetsTest::test_bulk_edit_assets_accepts_all_possible_attributes` | `tests/Feature/Assets/Ui/BulkEditAssetsTest.php` | Pass |
| F-306 | `Tests.Feature.Assets.Ui.BulkEditAssetsTest::test_bulk_edit_assets_nulls_out_fields_if_selected` | `tests/Feature/Assets/Ui/BulkEditAssetsTest.php` | Pass |
| F-307 | `Tests.Feature.Assets.Ui.BulkEditAssetsTest::test_bulk_edit_assets_accepts_and_updates_unencrypted_custom_fields` | `tests/Feature/Assets/Ui/BulkEditAssetsTest.php` | Pass |
| F-308 | `Tests.Feature.Assets.Ui.BulkEditAssetsTest::test_bulk_edit_assets_nulls_custom_fields_if_selected` | `tests/Feature/Assets/Ui/BulkEditAssetsTest.php` | Pass |
| F-309 | `Tests.Feature.Assets.Ui.BulkEditAssetsTest::test_bulk_edit_assets_accepts_and_updates_encrypted_custom_fields` | `tests/Feature/Assets/Ui/BulkEditAssetsTest.php` | Pass |
| F-310 | `Tests.Feature.Assets.Ui.BulkEditAssetsTest::test_bulk_edit_assets_requires_admin_to_update_encrypted_custom_fields` | `tests/Feature/Assets/Ui/BulkEditAssetsTest.php` | Pass |
| F-311 | `Tests.Feature.Assets.Ui.BulkEditAssetsTest::test_same_origin_referer_is_stored_as_bulk_back_url` | `tests/Feature/Assets/Ui/BulkEditAssetsTest.php` | Pass |
| F-312 | `Tests.Feature.Assets.Ui.BulkEditAssetsTest::test_offsite_referer_is_not_stored_as_bulk_back_url` | `tests/Feature/Assets/Ui/BulkEditAssetsTest.php` | Pass |
| F-313 | `Tests.Feature.Assets.Ui.BulkEditAssetsTest::test_bulk_update_redirects_to_stashed_same_origin_url` | `tests/Feature/Assets/Ui/BulkEditAssetsTest.php` | Pass |
| F-314 | `Tests.Feature.Assets.Ui.BulkEditAssetsTest::test_bulk_update_falls_back_to_hardware_index_when_stashed_url_is_missing` | `tests/Feature/Assets/Ui/BulkEditAssetsTest.php` | Pass |
| F-315 | `Tests.Feature.Assets.Ui.BulkEditAssetsTest::test_bulk_update_ignores_poisoned_stashed_url` | `tests/Feature/Assets/Ui/BulkEditAssetsTest.php` | Pass |
| F-316 | `Tests.Feature.Assets.Ui.BulkRestoreAuthorizationTest::test_user_with_edit_but_no_delete_cannot_bulk_restore` | `tests/Feature/Assets/Ui/BulkRestoreAuthorizationTest.php` | Pass |
| F-317 | `Tests.Feature.Assets.Ui.BulkRestoreAuthorizationTest::test_user_with_only_view_cannot_bulk_restore` | `tests/Feature/Assets/Ui/BulkRestoreAuthorizationTest.php` | Pass |
| F-318 | `Tests.Feature.Assets.Ui.BulkRestoreAuthorizationTest::test_user_with_delete_permission_can_bulk_restore` | `tests/Feature/Assets/Ui/BulkRestoreAuthorizationTest.php` | Pass |
| F-319 | `Tests.Feature.Assets.Ui.BulkRestoreAuthorizationTest::test_bulk_restore_skips_nonexistent_ids_instead_of_500` | `tests/Feature/Assets/Ui/BulkRestoreAuthorizationTest.php` | Pass |
| F-320 | `Tests.Feature.Assets.Ui.CheckinAssetLocationScopingTest::test_scoped_admin_can_check_in_when_location_scoping_is_off_and_target_is_null_company` | `tests/Feature/Assets/Ui/CheckinAssetLocationScopingTest.php` | Pass |
| F-321 | `Tests.Feature.Assets.Ui.CheckinAssetLocationScopingTest::test_scoped_admin_can_check_in_when_location_scoping_is_off_and_target_is_cross_company` | `tests/Feature/Assets/Ui/CheckinAssetLocationScopingTest.php` | Pass |
| F-322 | `Tests.Feature.Assets.Ui.CheckinAssetLocationScopingTest::test_scoped_admin_still_blocked_when_location_scoping_is_on_and_target_is_cross_company` | `tests/Feature/Assets/Ui/CheckinAssetLocationScopingTest.php` | Pass |
| F-323 | `Tests.Feature.Assets.Ui.CloneAssetTest::test_permission_required_to_clone_asset` | `tests/Feature/Assets/Ui/CloneAssetTest.php` | Pass |
| F-324 | `Tests.Feature.Assets.Ui.CloneAssetTest::test_page_can_be_accessed` | `tests/Feature/Assets/Ui/CloneAssetTest.php` | Pass |
| F-325 | `Tests.Feature.Assets.Ui.CloneAssetTest::test_asset_can_be_cloned` | `tests/Feature/Assets/Ui/CloneAssetTest.php` | Pass |
| F-326 | `Tests.Feature.Assets.Ui.CloneAssetTest::test_create_permission_alone_is_not_enough_to_clone_asset` | `tests/Feature/Assets/Ui/CloneAssetTest.php` | Pass |
| F-327 | `Tests.Feature.Assets.Ui.DeleteAssetTest::test_permission_needed_to_delete_asset` | `tests/Feature/Assets/Ui/DeleteAssetTest.php` | Pass |
| F-328 | `Tests.Feature.Assets.Ui.DeleteAssetTest::test_can_delete_asset` | `tests/Feature/Assets/Ui/DeleteAssetTest.php` | Pass |
| F-329 | `Tests.Feature.Assets.Ui.DeleteAssetTest::test_action_log_entry_made_when_asset_deleted` | `tests/Feature/Assets/Ui/DeleteAssetTest.php` | Pass |
| F-330 | `Tests.Feature.Assets.Ui.DeleteAssetTest::test_action_logs_action_date_is_populated_when_asset_deleted` | `tests/Feature/Assets/Ui/DeleteAssetTest.php` | Pass |
| F-331 | `Tests.Feature.Assets.Ui.DeleteAssetTest::test_asset_is_checked_in_when_deleted` | `tests/Feature/Assets/Ui/DeleteAssetTest.php` | Pass |
| F-332 | `Tests.Feature.Assets.Ui.DeleteAssetTest::test_image_is_preserved_when_asset_soft_deleted` | `tests/Feature/Assets/Ui/DeleteAssetTest.php` | Pass |
| F-333 | `Tests.Feature.Assets.Ui.EditAssetTest::test_permission_required_to_view_edit_asset_page` | `tests/Feature/Assets/Ui/EditAssetTest.php` | Pass |
| F-334 | `Tests.Feature.Assets.Ui.EditAssetTest::test_page_can_be_accessed` | `tests/Feature/Assets/Ui/EditAssetTest.php` | Pass |
| F-335 | `Tests.Feature.Assets.Ui.EditAssetTest::test_asset_edit_post_is_redirected_if_redirect_selection_is_index` | `tests/Feature/Assets/Ui/EditAssetTest.php` | Pass |
| F-336 | `Tests.Feature.Assets.Ui.EditAssetTest::test_asset_edit_post_is_redirected_if_redirect_selection_is_item` | `tests/Feature/Assets/Ui/EditAssetTest.php` | Pass |
| F-337 | `Tests.Feature.Assets.Ui.EditAssetTest::test_new_checkin_is_logged_if_status_changed_to_undeployable` | `tests/Feature/Assets/Ui/EditAssetTest.php` | Pass |
| F-338 | `Tests.Feature.Assets.Ui.EditAssetTest::test_current_location_is_not_updated_on_edit` | `tests/Feature/Assets/Ui/EditAssetTest.php` | Pass |
| F-339 | `Tests.Feature.Assets.Ui.EditAssetTest::test_handles_model_being_deleted` | `tests/Feature/Assets/Ui/EditAssetTest.php` | Pass |
| F-340 | `Tests.Feature.Assets.Ui.EncryptedCustomFieldFormDisclosureTest::test_edit_form_masks_encrypted_custom_field_for_user_without_view_encrypted with data set "textarea"` | `tests/Feature/Assets/Ui/EncryptedCustomFieldFormDisclosureTest.php` | Pass |
| F-341 | `Tests.Feature.Assets.Ui.EncryptedCustomFieldFormDisclosureTest::test_edit_form_masks_encrypted_custom_field_for_user_without_view_encrypted with data set "markdown-textarea"` | `tests/Feature/Assets/Ui/EncryptedCustomFieldFormDisclosureTest.php` | Pass |
| F-342 | `Tests.Feature.Assets.Ui.EncryptedCustomFieldFormDisclosureTest::test_edit_form_masks_encrypted_custom_field_for_user_without_view_encrypted with data set "date_picker"` | `tests/Feature/Assets/Ui/EncryptedCustomFieldFormDisclosureTest.php` | Pass |
| F-343 | `Tests.Feature.Assets.Ui.EncryptedCustomFieldFormDisclosureTest::test_edit_form_masks_encrypted_custom_field_for_user_without_view_encrypted with data set "datetime_picker"` | `tests/Feature/Assets/Ui/EncryptedCustomFieldFormDisclosureTest.php` | Pass |
| F-344 | `Tests.Feature.Assets.Ui.EncryptedCustomFieldFormDisclosureTest::test_edit_form_shows_encrypted_custom_field_plaintext_for_superuser with data set "textarea"` | `tests/Feature/Assets/Ui/EncryptedCustomFieldFormDisclosureTest.php` | Pass |
| F-345 | `Tests.Feature.Assets.Ui.EncryptedCustomFieldFormDisclosureTest::test_edit_form_shows_encrypted_custom_field_plaintext_for_superuser with data set "markdown-textarea"` | `tests/Feature/Assets/Ui/EncryptedCustomFieldFormDisclosureTest.php` | Pass |
| F-346 | `Tests.Feature.Assets.Ui.EncryptedCustomFieldFormDisclosureTest::test_edit_form_shows_encrypted_custom_field_plaintext_for_superuser with data set "date_picker"` | `tests/Feature/Assets/Ui/EncryptedCustomFieldFormDisclosureTest.php` | Pass |
| F-347 | `Tests.Feature.Assets.Ui.EncryptedCustomFieldFormDisclosureTest::test_edit_form_shows_encrypted_custom_field_plaintext_for_superuser with data set "datetime_picker"` | `tests/Feature/Assets/Ui/EncryptedCustomFieldFormDisclosureTest.php` | Pass |
| F-348 | `Tests.Feature.Assets.Ui.EncryptedCustomFieldFormDisclosureTest::test_checkin_form_masks_encrypted_custom_field_for_user_without_view_encrypted` | `tests/Feature/Assets/Ui/EncryptedCustomFieldFormDisclosureTest.php` | Pass |
| F-349 | `Tests.Feature.Assets.Ui.EncryptedCustomFieldFormDisclosureTest::test_checkout_form_masks_encrypted_custom_field_for_user_without_view_encrypted` | `tests/Feature/Assets/Ui/EncryptedCustomFieldFormDisclosureTest.php` | Pass |
| F-350 | `Tests.Feature.Assets.Ui.EncryptedCustomFieldFormDisclosureTest::test_audit_form_masks_encrypted_custom_field_for_user_without_view_encrypted` | `tests/Feature/Assets/Ui/EncryptedCustomFieldFormDisclosureTest.php` | Pass |
| F-351 | `Tests.Feature.Assets.Ui.ForceCheckinAssetTest::test_permission_required_to_force_checkin_asset` | `tests/Feature/Assets/Ui/ForceCheckinAssetTest.php` | Pass |
| F-352 | `Tests.Feature.Assets.Ui.ForceCheckinAssetTest::test_can_force_checkin_asset_with_orphaned_assigned_to_and_missing_type` | `tests/Feature/Assets/Ui/ForceCheckinAssetTest.php` | Pass |
| F-353 | `Tests.Feature.Assets.Ui.ForceCheckinAssetTest::test_can_force_checkin_asset_with_hard_deleted_assigned_to` | `tests/Feature/Assets/Ui/ForceCheckinAssetTest.php` | Pass |
| F-354 | `Tests.Feature.Assets.Ui.ForceCheckinAssetTest::test_cannot_force_checkin_asset_that_is_not_orphaned` | `tests/Feature/Assets/Ui/ForceCheckinAssetTest.php` | Pass |
| F-355 | `Tests.Feature.Assets.Ui.ForceCheckinAssetTest::test_cannot_force_checkin_asset_with_no_assignment` | `tests/Feature/Assets/Ui/ForceCheckinAssetTest.php` | Pass |
| F-356 | `Tests.Feature.Assets.Ui.ForceCheckinAssetTest::test_force_checkin_button_shown_on_asset_view_when_orphaned` | `tests/Feature/Assets/Ui/ForceCheckinAssetTest.php` | Pass |
| F-357 | `Tests.Feature.Assets.Ui.ForceCheckinAssetTest::test_force_checkin_button_not_shown_when_asset_is_not_orphaned` | `tests/Feature/Assets/Ui/ForceCheckinAssetTest.php` | Pass |
| F-358 | `Tests.Feature.Assets.Ui.ImportAssetHistoryTest::test_legacy_get_history_route_redirects_to_importer` | `tests/Feature/Assets/Ui/ImportAssetHistoryTest.php` | Pass |
| F-359 | `Tests.Feature.Assets.Ui.ImportAssetHistoryTest::test_legacy_post_history_endpoint_is_gone` | `tests/Feature/Assets/Ui/ImportAssetHistoryTest.php` | Pass |
| F-360 | `Tests.Feature.Assets.Ui.ImportAssetHistoryTest::test_process_endpoint_blocked_in_demo_mode_for_non_superadmin` | `tests/Feature/Assets/Ui/ImportAssetHistoryTest.php` | Pass |
| F-361 | `Tests.Feature.Assets.Ui.ImportAssetHistoryTest::test_process_endpoint_allowed_in_demo_mode_for_superadmin` | `tests/Feature/Assets/Ui/ImportAssetHistoryTest.php` | Pass |
| F-362 | `Tests.Feature.Assets.Ui.ImportAssetHistoryTest::test_asset_history_import_requires_import_permission` | `tests/Feature/Assets/Ui/ImportAssetHistoryTest.php` | Pass |
| F-363 | `Tests.Feature.Assets.Ui.ImportAssetHistoryTest::test_asset_history_import_creates_actionlogs_and_assigns_user` | `tests/Feature/Assets/Ui/ImportAssetHistoryTest.php` | Pass |
| F-364 | `Tests.Feature.Assets.Ui.ImportAssetHistoryTest::test_asset_history_import_does_not_reassign_when_checkin_is_past` | `tests/Feature/Assets/Ui/ImportAssetHistoryTest.php` | Pass |
| F-365 | `Tests.Feature.Assets.Ui.ImportAssetHistoryTest::test_asset_history_import_skips_unknown_asset_tag` | `tests/Feature/Assets/Ui/ImportAssetHistoryTest.php` | Pass |
| F-366 | `Tests.Feature.Assets.Ui.ImportAssetHistoryTest::test_asset_history_import_skips_row_when_user_not_matched` | `tests/Feature/Assets/Ui/ImportAssetHistoryTest.php` | Pass |
| F-367 | `Tests.Feature.Assets.Ui.ImportAssetHistoryTest::test_asset_history_import_checks_out_to_location_when_target_type_is_location` | `tests/Feature/Assets/Ui/ImportAssetHistoryTest.php` | Pass |
| F-368 | `Tests.Feature.Assets.Ui.ImportAssetHistoryTest::test_asset_history_import_falls_back_to_user_lookup_when_target_type_absent` | `tests/Feature/Assets/Ui/ImportAssetHistoryTest.php` | Pass |
| F-369 | `Tests.Feature.Assets.Ui.ImportAssetHistoryTest::test_asset_history_import_stamps_csv_note_onto_checkout_actionlog` | `tests/Feature/Assets/Ui/ImportAssetHistoryTest.php` | Pass |
| F-370 | `Tests.Feature.Assets.Ui.ImportAssetHistoryTest::test_asset_history_import_keeps_default_note_when_csv_note_is_absent` | `tests/Feature/Assets/Ui/ImportAssetHistoryTest.php` | Pass |
| F-371 | `Tests.Feature.Assets.Ui.ImportAssetHistoryTest::test_asset_history_import_rejects_row_with_unrecognized_target_type` | `tests/Feature/Assets/Ui/ImportAssetHistoryTest.php` | Pass |
| F-372 | `Tests.Feature.Assets.Ui.ImportAssetHistoryTest::test_asset_history_import_skips_row_when_location_target_not_matched` | `tests/Feature/Assets/Ui/ImportAssetHistoryTest.php` | Pass |
| F-373 | `Tests.Feature.Assets.Ui.ShowAssetTest::test_permission_required_to_view_asset` | `tests/Feature/Assets/Ui/ShowAssetTest.php` | Pass |
| F-374 | `Tests.Feature.Assets.Ui.ShowAssetTest::test_can_view_asset` | `tests/Feature/Assets/Ui/ShowAssetTest.php` | Pass |
| F-375 | `Tests.Feature.Assets.Ui.ShowAssetTest::test_page_renders_when_journal_note_has_no_author` | `tests/Feature/Assets/Ui/ShowAssetTest.php` | Pass |
| F-376 | `Tests.Feature.Assets.Ui.ShowAssetTest::test_maintenance_tab_ships_complete_button_infrastructure` | `tests/Feature/Assets/Ui/ShowAssetTest.php` | Pass |
| F-377 | `Tests.Feature.Assets.Ui.ShowAssetTest::test_info_panel_purchase_date_reflects_the_edited_asset_value_not_the_snapshot_from_create` | `tests/Feature/Assets/Ui/ShowAssetTest.php` | Pass |
| F-378 | `Tests.Feature.Assets.Ui.ShowAssetTest::test_info_panel_unit_cost_reflects_the_edited_asset_value_when_the_order_item_is_out_of_sync` | `tests/Feature/Assets/Ui/ShowAssetTest.php` | Pass |
| F-379 | `Tests.Feature.Assets.Ui.ShowAssetTest::test_page_for_asset_with_missing_model_still_renders` | `tests/Feature/Assets/Ui/ShowAssetTest.php` | Pass |
| F-380 | `Tests.Feature.Assets.Ui.StoreAssetCheckoutPermissionTest::test_web_create_with_assigned_user_without_checkout_permission_creates_asset_and_flags_skipped_checkout` | `tests/Feature/Assets/Ui/StoreAssetCheckoutPermissionTest.php` | Pass |
| F-381 | `Tests.Feature.Assets.Ui.StoreAssetCheckoutPermissionTest::test_web_create_without_assignment_does_not_flag_skipped_checkout` | `tests/Feature/Assets/Ui/StoreAssetCheckoutPermissionTest.php` | Pass |
| F-382 | `Tests.Feature.Assets.Ui.StoreAssetCheckoutPermissionTest::test_web_create_with_assignment_succeeds_when_checkout_permission_granted` | `tests/Feature/Assets/Ui/StoreAssetCheckoutPermissionTest.php` | Pass |
| F-383 | `Tests.Feature.Assets.Ui.StoreAssetWithFullMultipleCompanySupportTest::test_adheres_to_full_multiple_companies_support_scoping with data set "User in a company should result in user's company_id being used"` | `tests/Feature/Assets/Ui/StoreAssetWithFullMultipleCompanySupportTest.php` | Pass |
| F-384 | `Tests.Feature.Assets.Ui.StoreAssetWithFullMultipleCompanySupportTest::test_adheres_to_full_multiple_companies_support_scoping with data set "User without a company should result in company_id being null"` | `tests/Feature/Assets/Ui/StoreAssetWithFullMultipleCompanySupportTest.php` | Pass |
| F-385 | `Tests.Feature.Assets.Ui.StoreAssetWithFullMultipleCompanySupportTest::test_adheres_to_full_multiple_companies_support_scoping with data set "Super-User assigning across companies should result in company_id being set to what was provided"` | `tests/Feature/Assets/Ui/StoreAssetWithFullMultipleCompanySupportTest.php` | Pass |
| F-386 | `Tests.Feature.Assets.Ui.StoreAssetWithFullMultipleCompanySupportTest::test_non_admin_with_pivot_only_company_membership_can_create_asset_in_their_company` | `tests/Feature/Assets/Ui/StoreAssetWithFullMultipleCompanySupportTest.php` | Pass |
| F-387 | `Tests.Feature.Assets.Ui.StoreAssetWithFullMultipleCompanySupportTest::test_allows_creating_asset_with_scoped_location` | `tests/Feature/Assets/Ui/StoreAssetWithFullMultipleCompanySupportTest.php` | Pass |
| F-388 | `Tests.Feature.Assets.Ui.StoreAssetsTest::test_permission_required_to_view_create_asset_page` | `tests/Feature/Assets/Ui/StoreAssetsTest.php` | Pass |
| F-389 | `Tests.Feature.Assets.Ui.StoreAssetsTest::test_permission_required_to_store_asset` | `tests/Feature/Assets/Ui/StoreAssetsTest.php` | Pass |
| F-390 | `Tests.Feature.Assets.Ui.StoreAssetsTest::test_create_asset_page_renders` | `tests/Feature/Assets/Ui/StoreAssetsTest.php` | Pass |
| F-391 | `Tests.Feature.Assets.Ui.StoreAssetsTest::test_asset_can_be_stored_with_serial_required_and_serial_provided` | `tests/Feature/Assets/Ui/StoreAssetsTest.php` | Pass |
| F-392 | `Tests.Feature.Assets.Ui.StoreAssetsTest::test_asset_cannot_be_stored_if_serial_required_and_missing` | `tests/Feature/Assets/Ui/StoreAssetsTest.php` | Pass |
| F-393 | `Tests.Feature.AssetModels.Api.AssetModelFilesPolicyBypassTest::test_asset_viewer_can_list_model_files` | `tests/Feature/AssetModels/Api/AssetModelFilesPolicyBypassTest.php` | Pass |
| F-394 | `Tests.Feature.AssetModels.Api.AssetModelFilesPolicyBypassTest::test_asset_viewer_can_download_model_file` | `tests/Feature/AssetModels/Api/AssetModelFilesPolicyBypassTest.php` | Pass |
| F-395 | `Tests.Feature.AssetModels.Api.AssetModelFilesPolicyBypassTest::test_asset_viewer_cannot_upload_to_model` | `tests/Feature/AssetModels/Api/AssetModelFilesPolicyBypassTest.php` | Pass |
| F-396 | `Tests.Feature.AssetModels.Api.AssetModelFilesPolicyBypassTest::test_asset_viewer_cannot_delete_model_file` | `tests/Feature/AssetModels/Api/AssetModelFilesPolicyBypassTest.php` | Pass |
| F-397 | `Tests.Feature.AssetModels.Api.AssetModelFilesPolicyBypassTest::test_user_with_only_assets_files_cannot_upload_to_model` | `tests/Feature/AssetModels/Api/AssetModelFilesPolicyBypassTest.php` | Pass |
| F-398 | `Tests.Feature.AssetModels.Api.AssetModelFilesPolicyBypassTest::test_user_with_only_assets_files_cannot_delete_model_file` | `tests/Feature/AssetModels/Api/AssetModelFilesPolicyBypassTest.php` | Pass |
| F-399 | `Tests.Feature.AssetModels.Api.AssetModelFilesPolicyBypassTest::test_user_with_models_files_can_upload_to_model` | `tests/Feature/AssetModels/Api/AssetModelFilesPolicyBypassTest.php` | Pass |
| F-400 | `Tests.Feature.AssetModels.Api.AssetModelFilesPolicyBypassTest::test_user_with_models_files_can_delete_model_file` | `tests/Feature/AssetModels/Api/AssetModelFilesPolicyBypassTest.php` | Pass |
| F-401 | `Tests.Feature.AssetModels.Api.AssetModelFilesPolicyBypassTest::test_user_with_no_file_permissions_is_forbidden_on_all_actions` | `tests/Feature/AssetModels/Api/AssetModelFilesPolicyBypassTest.php` | Pass |
| F-402 | `Tests.Feature.AssetModels.Api.AssetModelFilesTest::test_asset_model_api_accepts_file_upload` | `tests/Feature/AssetModels/Api/AssetModelFilesTest.php` | Pass |
| F-403 | `Tests.Feature.AssetModels.Api.AssetModelFilesTest::test_asset_model_api_lists_files` | `tests/Feature/AssetModels/Api/AssetModelFilesTest.php` | Pass |
| F-404 | `Tests.Feature.AssetModels.Api.AssetModelFilesTest::test_asset_model_fails_if_invalid_type_passed_in_url` | `tests/Feature/AssetModels/Api/AssetModelFilesTest.php` | Pass |
| F-405 | `Tests.Feature.AssetModels.Api.AssetModelFilesTest::test_asset_model_fails_if_invalid_id_passed_in_url` | `tests/Feature/AssetModels/Api/AssetModelFilesTest.php` | Pass |
| F-406 | `Tests.Feature.AssetModels.Api.AssetModelFilesTest::test_asset_model_api_downloads_file` | `tests/Feature/AssetModels/Api/AssetModelFilesTest.php` | Pass |
| F-407 | `Tests.Feature.AssetModels.Api.AssetModelFilesTest::test_asset_model_api_deletes_file` | `tests/Feature/AssetModels/Api/AssetModelFilesTest.php` | Pass |
| F-408 | `Tests.Feature.AssetModels.Api.AssetModelFilesTest::test_non_superuser_can_list_model_files_with_fmcs_enabled` | `tests/Feature/AssetModels/Api/AssetModelFilesTest.php` | Pass |
| F-409 | `Tests.Feature.AssetModels.Api.AssetModelPaginationTest::test_page_one_returns_first_items` | `tests/Feature/AssetModels/Api/AssetModelPaginationTest.php` | Pass |
| F-410 | `Tests.Feature.AssetModels.Api.AssetModelPaginationTest::test_page_two_returns_second_set_of_items` | `tests/Feature/AssetModels/Api/AssetModelPaginationTest.php` | Pass |
| F-411 | `Tests.Feature.AssetModels.Api.AssetModelPaginationTest::test_offset_returns_correct_items` | `tests/Feature/AssetModels/Api/AssetModelPaginationTest.php` | Pass |
| F-412 | `Tests.Feature.AssetModels.Api.AssetModelPaginationTest::test_page_param_respects_limit` | `tests/Feature/AssetModels/Api/AssetModelPaginationTest.php` | Pass |
| F-413 | `Tests.Feature.AssetModels.Api.AssetModelPaginationTest::test_page_beyond_results_returns_empty_rows` | `tests/Feature/AssetModels/Api/AssetModelPaginationTest.php` | Pass |
| F-414 | `Tests.Feature.AssetModels.Api.AssetsForAssetModelTest::test_viewing_assets_for_asset_model_requires_authentication` | `tests/Feature/AssetModels/Api/AssetsForAssetModelTest.php` | Pass |
| F-415 | `Tests.Feature.AssetModels.Api.AssetsForAssetModelTest::test_viewing_assets_for_asset_model_requires_permission` | `tests/Feature/AssetModels/Api/AssetsForAssetModelTest.php` | Pass |
| F-416 | `Tests.Feature.AssetModels.Api.AssetsForAssetModelTest::test_models_view_without_assets_view_cannot_read_the_assets_for_a_model` | `tests/Feature/AssetModels/Api/AssetsForAssetModelTest.php` | Pass |
| F-417 | `Tests.Feature.AssetModels.Api.AssetsForAssetModelTest::test_models_view_combined_with_assets_view_still_reaches_the_endpoint` | `tests/Feature/AssetModels/Api/AssetsForAssetModelTest.php` | Pass |
| F-418 | `Tests.Feature.AssetModels.Api.AssetsForAssetModelTest::test_error_returned_if_asset_model_does_not_exist` | `tests/Feature/AssetModels/Api/AssetsForAssetModelTest.php` | Pass |
| F-419 | `Tests.Feature.AssetModels.Api.AssetsForAssetModelTest::test_returns_only_assets_belonging_to_the_named_model` | `tests/Feature/AssetModels/Api/AssetsForAssetModelTest.php` | Pass |
| F-420 | `Tests.Feature.AssetModels.Api.AssetsForAssetModelTest::test_returns_empty_list_when_model_has_no_assets` | `tests/Feature/AssetModels/Api/AssetsForAssetModelTest.php` | Pass |
| F-421 | `Tests.Feature.AssetModels.Api.AssetsForAssetModelTest::test_limit_query_parameter_bounds_the_returned_rows` | `tests/Feature/AssetModels/Api/AssetsForAssetModelTest.php` | Pass |
| F-422 | `Tests.Feature.AssetModels.Api.AssetsForAssetModelTest::test_offset_query_parameter_skips_earlier_rows` | `tests/Feature/AssetModels/Api/AssetsForAssetModelTest.php` | Pass |
| F-423 | `Tests.Feature.AssetModels.Api.BulkSelectableTest::test_clean_model_supports_both_edit_and_delete` | `tests/Feature/AssetModels/Api/BulkSelectableTest.php` | Pass |
| F-424 | `Tests.Feature.AssetModels.Api.BulkSelectableTest::test_soft_deleted_model_supports_neither` | `tests/Feature/AssetModels/Api/BulkSelectableTest.php` | Pass |
| F-425 | `Tests.Feature.AssetModels.Api.BulkSelectableTest::test_model_with_assigned_assets_cannot_be_deleted` | `tests/Feature/AssetModels/Api/BulkSelectableTest.php` | Pass |
| F-426 | `Tests.Feature.AssetModels.Api.BulkSelectableTest::test_user_without_edit_permission_gets_edit_false` | `tests/Feature/AssetModels/Api/BulkSelectableTest.php` | Pass |
| F-427 | `Tests.Feature.AssetModels.Api.BulkSelectableTest::test_user_without_delete_permission_gets_delete_false` | `tests/Feature/AssetModels/Api/BulkSelectableTest.php` | Pass |
| F-428 | `Tests.Feature.AssetModels.Api.CreateAssetModelsTest::test_requires_permission_to_create_asset_model` | `tests/Feature/AssetModels/Api/CreateAssetModelsTest.php` | Pass |
| F-429 | `Tests.Feature.AssetModels.Api.CreateAssetModelsTest::test_can_create_asset_model_with_asset_model_type` | `tests/Feature/AssetModels/Api/CreateAssetModelsTest.php` | Pass |
| F-430 | `Tests.Feature.AssetModels.Api.CreateAssetModelsTest::test_cannot_create_asset_model_without_category` | `tests/Feature/AssetModels/Api/CreateAssetModelsTest.php` | Pass |
| F-431 | `Tests.Feature.AssetModels.Api.CreateAssetModelsTest::test_uniqueness_across_model_name_and_model_number` | `tests/Feature/AssetModels/Api/CreateAssetModelsTest.php` | Pass |
| F-432 | `Tests.Feature.AssetModels.Api.CreateAssetModelsTest::test_uniqueness_across_model_name_and_model_number_with_blank_model_number` | `tests/Feature/AssetModels/Api/CreateAssetModelsTest.php` | Pass |
| F-433 | `Tests.Feature.AssetModels.Api.DeleteAssetModelsTest::test_requires_permission` | `tests/Feature/AssetModels/Api/DeleteAssetModelsTest.php` | Pass |
| F-434 | `Tests.Feature.AssetModels.Api.DeleteAssetModelsTest::test_cannot_delete_asset_model_that_still_has_associated_assets` | `tests/Feature/AssetModels/Api/DeleteAssetModelsTest.php` | Pass |
| F-435 | `Tests.Feature.AssetModels.Api.DeleteAssetModelsTest::test_can_delete_asset_model` | `tests/Feature/AssetModels/Api/DeleteAssetModelsTest.php` | Pass |
| F-436 | `Tests.Feature.AssetModels.Api.IndexAssetModelsTest::test_viewing_asset_model_index_requires_authentication` | `tests/Feature/AssetModels/Api/IndexAssetModelsTest.php` | Pass |
| F-437 | `Tests.Feature.AssetModels.Api.IndexAssetModelsTest::test_viewing_asset_model_index_requires_permission` | `tests/Feature/AssetModels/Api/IndexAssetModelsTest.php` | Pass |
| F-438 | `Tests.Feature.AssetModels.Api.IndexAssetModelsTest::test_asset_model_index_returns_expected_asset_models` | `tests/Feature/AssetModels/Api/IndexAssetModelsTest.php` | Pass |
| F-439 | `Tests.Feature.AssetModels.Api.IndexAssetModelsTest::test_asset_model_index_search_returns_expected_asset_models` | `tests/Feature/AssetModels/Api/IndexAssetModelsTest.php` | Pass |
| F-440 | `Tests.Feature.AssetModels.Api.IndexAssetModelsTest::test_asset_model_index_filter_can_search_computed_count_aliases` | `tests/Feature/AssetModels/Api/IndexAssetModelsTest.php` | Incomplete |
| F-441 | `Tests.Feature.AssetModels.Api.RequestableAssetModelsApiTest::test_returns_only_requestable_models` | `tests/Feature/AssetModels/Api/RequestableAssetModelsApiTest.php` | Pass |
| F-442 | `Tests.Feature.AssetModels.Api.RequestableAssetModelsApiTest::test_row_carries_assigned_to_self_true_when_user_has_open_request` | `tests/Feature/AssetModels/Api/RequestableAssetModelsApiTest.php` | Pass |
| F-443 | `Tests.Feature.AssetModels.Api.RequestableAssetModelsApiTest::test_row_carries_assigned_to_self_false_for_other_users_requests` | `tests/Feature/AssetModels/Api/RequestableAssetModelsApiTest.php` | Pass |
| F-444 | `Tests.Feature.AssetModels.Api.RequestableAssetModelsApiTest::test_canceled_requests_do_not_flip_assigned_to_self` | `tests/Feature/AssetModels/Api/RequestableAssetModelsApiTest.php` | Pass |
| F-445 | `Tests.Feature.AssetModels.Api.RequestableAssetModelsApiTest::test_cross_type_open_requests_do_not_flip_assigned_to_self` | `tests/Feature/AssetModels/Api/RequestableAssetModelsApiTest.php` | Pass |
| F-446 | `Tests.Feature.AssetModels.Api.RequestableAssetModelsApiTest::test_available_actions_view_reflects_caller_permission` | `tests/Feature/AssetModels/Api/RequestableAssetModelsApiTest.php` | Pass |
| F-447 | `Tests.Feature.AssetModels.Api.RequestableAssetModelsApiTest::test_remaining_reflects_deployable_asset_count` | `tests/Feature/AssetModels/Api/RequestableAssetModelsApiTest.php` | Pass |
| F-448 | `Tests.Feature.AssetModels.Api.RestoreAssetModelTest::test_permission_required_to_restore_asset_model` | `tests/Feature/AssetModels/Api/RestoreAssetModelTest.php` | Pass |
| F-449 | `Tests.Feature.AssetModels.Api.RestoreAssetModelTest::test_error_returned_if_asset_model_does_not_exist` | `tests/Feature/AssetModels/Api/RestoreAssetModelTest.php` | Pass |
| F-450 | `Tests.Feature.AssetModels.Api.RestoreAssetModelTest::test_error_returned_if_asset_model_is_not_deleted` | `tests/Feature/AssetModels/Api/RestoreAssetModelTest.php` | Pass |
| F-451 | `Tests.Feature.AssetModels.Api.RestoreAssetModelTest::test_soft_deleted_asset_model_can_be_restored` | `tests/Feature/AssetModels/Api/RestoreAssetModelTest.php` | Pass |
| F-452 | `Tests.Feature.AssetModels.Api.RestoreAssetModelTest::test_restore_writes_action_log_entry` | `tests/Feature/AssetModels/Api/RestoreAssetModelTest.php` | Pass |
| F-453 | `Tests.Feature.AssetModels.Api.RestoreAssetModelTest::test_restore_does_not_write_an_update_action_log` | `tests/Feature/AssetModels/Api/RestoreAssetModelTest.php` | Pass |
| F-454 | `Tests.Feature.AssetModels.Api.UpdateAssetModelsTest::test_requires_permission_to_edit_asset_model` | `tests/Feature/AssetModels/Api/UpdateAssetModelsTest.php` | Pass |
| F-455 | `Tests.Feature.AssetModels.Api.UpdateAssetModelsTest::test_can_update_asset_model_via_patch` | `tests/Feature/AssetModels/Api/UpdateAssetModelsTest.php` | Pass |
| F-456 | `Tests.Feature.AssetModels.Api.UpdateAssetModelsTest::test_cannot_update_asset_model_via_patch_with_accessory_category` | `tests/Feature/AssetModels/Api/UpdateAssetModelsTest.php` | Pass |
| F-457 | `Tests.Feature.AssetModels.Api.UpdateAssetModelsTest::test_cannot_update_asset_model_via_patch_with_license_category` | `tests/Feature/AssetModels/Api/UpdateAssetModelsTest.php` | Pass |
| F-458 | `Tests.Feature.AssetModels.Api.UpdateAssetModelsTest::test_cannot_update_asset_model_via_patch_with_consumable_category` | `tests/Feature/AssetModels/Api/UpdateAssetModelsTest.php` | Pass |
| F-459 | `Tests.Feature.AssetModels.Api.UpdateAssetModelsTest::test_cannot_update_asset_model_via_patch_with_component_category` | `tests/Feature/AssetModels/Api/UpdateAssetModelsTest.php` | Pass |
| F-460 | `Tests.Feature.AssetModels.Ui.BulkDeleteAssetModelsTest::test_shows_confirmation_when_at_least_one_model_is_deletable` | `tests/Feature/AssetModels/Ui/BulkDeleteAssetModelsTest.php` | Pass |
| F-461 | `Tests.Feature.AssetModels.Ui.BulkDeleteAssetModelsTest::test_redirects_to_index_with_error_when_no_selected_models_are_deletable` | `tests/Feature/AssetModels/Ui/BulkDeleteAssetModelsTest.php` | Pass |
| F-462 | `Tests.Feature.AssetModels.Ui.BulkDeleteAssetModelsTest::test_redirects_to_index_when_no_models_selected` | `tests/Feature/AssetModels/Ui/BulkDeleteAssetModelsTest.php` | Pass |
| F-463 | `Tests.Feature.AssetModels.Ui.BulkFulfillAssetModelTest::test_requires_asset_checkout_permission` | `tests/Feature/AssetModels/Ui/BulkFulfillAssetModelTest.php` | Pass |
| F-464 | `Tests.Feature.AssetModels.Ui.BulkFulfillAssetModelTest::test_fulfills_ticked_rows_with_distinct_assets` | `tests/Feature/AssetModels/Ui/BulkFulfillAssetModelTest.php` | Pass |
| F-465 | `Tests.Feature.AssetModels.Ui.BulkFulfillAssetModelTest::test_double_assignment_of_same_asset_wins_first_row_only` | `tests/Feature/AssetModels/Ui/BulkFulfillAssetModelTest.php` | Pass |
| F-466 | `Tests.Feature.AssetModels.Ui.BulkFulfillAssetModelTest::test_asset_from_a_different_model_is_rejected` | `tests/Feature/AssetModels/Ui/BulkFulfillAssetModelTest.php` | Pass |
| F-467 | `Tests.Feature.AssetModels.Ui.BulkMergeAssetModelsTest::test_shows_confirmation_when_at_least_two_models_are_selected` | `tests/Feature/AssetModels/Ui/BulkMergeAssetModelsTest.php` | Pass |
| F-468 | `Tests.Feature.AssetModels.Ui.BulkMergeAssetModelsTest::test_redirects_to_index_when_fewer_than_two_models_selected` | `tests/Feature/AssetModels/Ui/BulkMergeAssetModelsTest.php` | Pass |
| F-469 | `Tests.Feature.AssetModels.Ui.BulkMergeAssetModelsTest::test_merges_assets_from_source_models_into_target` | `tests/Feature/AssetModels/Ui/BulkMergeAssetModelsTest.php` | Pass |
| F-470 | `Tests.Feature.AssetModels.Ui.BulkMergeAssetModelsTest::test_merge_target_is_not_deleted_even_when_listed_in_ids_to_merge` | `tests/Feature/AssetModels/Ui/BulkMergeAssetModelsTest.php` | Pass |
| F-471 | `Tests.Feature.AssetModels.Ui.BulkMergeAssetModelsTest::test_redirects_with_error_when_target_not_picked` | `tests/Feature/AssetModels/Ui/BulkMergeAssetModelsTest.php` | Pass |
| F-472 | `Tests.Feature.AssetModels.Ui.BulkMergeAssetModelsTest::test_merge_logs_actionlog_entries_on_both_source_and_target` | `tests/Feature/AssetModels/Ui/BulkMergeAssetModelsTest.php` | Pass |
| F-473 | `Tests.Feature.AssetModels.Ui.BulkMergeAssetModelsTest::test_non_superusers_without_delete_permission_are_blocked` | `tests/Feature/AssetModels/Ui/BulkMergeAssetModelsTest.php` | Pass |
| F-474 | `Tests.Feature.AssetModels.Ui.CloneAssetModelTest::test_clone_page_preselects_source_fieldset` | `tests/Feature/AssetModels/Ui/CloneAssetModelTest.php` | Pass |
| F-475 | `Tests.Feature.AssetModels.Ui.CloneAssetModelTest::test_livewire_fieldset_picker_receives_source_model_id_on_clone` | `tests/Feature/AssetModels/Ui/CloneAssetModelTest.php` | Pass |
| F-476 | `Tests.Feature.AssetModels.Ui.CloneAssetModelTest::test_livewire_fieldset_picker_leaves_fieldset_empty_when_no_model_id` | `tests/Feature/AssetModels/Ui/CloneAssetModelTest.php` | Pass |
| F-477 | `Tests.Feature.AssetModels.Ui.CloneAssetModelTest::test_create_permission_alone_is_not_enough_to_clone_asset_model` | `tests/Feature/AssetModels/Ui/CloneAssetModelTest.php` | Pass |
| F-478 | `Tests.Feature.AssetModels.Ui.CreateAssetModelsTest::test_permission_required_to_create_asset_model` | `tests/Feature/AssetModels/Ui/CreateAssetModelsTest.php` | Pass |
| F-479 | `Tests.Feature.AssetModels.Ui.CreateAssetModelsTest::test_page_renders` | `tests/Feature/AssetModels/Ui/CreateAssetModelsTest.php` | Pass |
| F-480 | `Tests.Feature.AssetModels.Ui.CreateAssetModelsTest::test_user_can_create_asset_models` | `tests/Feature/AssetModels/Ui/CreateAssetModelsTest.php` | Pass |
| F-481 | `Tests.Feature.AssetModels.Ui.CreateAssetModelsTest::test_user_cannot_use_accessory_category_type_as_asset_model_category_type` | `tests/Feature/AssetModels/Ui/CreateAssetModelsTest.php` | Pass |
| F-482 | `Tests.Feature.AssetModels.Ui.CreateAssetModelsTest::test_uniqueness_across_model_name_and_model_number` | `tests/Feature/AssetModels/Ui/CreateAssetModelsTest.php` | Pass |
| F-483 | `Tests.Feature.AssetModels.Ui.CreateAssetModelsTest::test_uniqueness_across_model_name_and_model_number_without_model_number` | `tests/Feature/AssetModels/Ui/CreateAssetModelsTest.php` | Pass |
| F-484 | `Tests.Feature.AssetModels.Ui.GetCustomFieldsFormAuthTest::test_zero_permission_user_cannot_load_custom_fields_form` | `tests/Feature/AssetModels/Ui/GetCustomFieldsFormAuthTest.php` | Pass |
| F-485 | `Tests.Feature.AssetModels.Ui.GetCustomFieldsFormAuthTest::test_user_with_models_view_can_load_custom_fields_form` | `tests/Feature/AssetModels/Ui/GetCustomFieldsFormAuthTest.php` | Pass |
| F-486 | `Tests.Feature.AssetModels.Ui.GetCustomFieldsFormAuthTest::test_asset_creator_can_load_custom_fields_form` | `tests/Feature/AssetModels/Ui/GetCustomFieldsFormAuthTest.php` | Pass |
| F-487 | `Tests.Feature.AssetModels.Ui.GetCustomFieldsFormAuthTest::test_asset_editor_can_load_custom_fields_form` | `tests/Feature/AssetModels/Ui/GetCustomFieldsFormAuthTest.php` | Pass |
| F-488 | `Tests.Feature.AssetModels.Ui.GetCustomFieldsFormAuthTest::test_superuser_can_load_custom_fields_form` | `tests/Feature/AssetModels/Ui/GetCustomFieldsFormAuthTest.php` | Pass |
| F-489 | `Tests.Feature.AssetModels.Ui.IndexAssetModelsTest::test_permission_required_to_view_asset_model_list` | `tests/Feature/AssetModels/Ui/IndexAssetModelsTest.php` | Pass |
| F-490 | `Tests.Feature.AssetModels.Ui.IndexAssetModelsTest::test_user_can_list_asset_models` | `tests/Feature/AssetModels/Ui/IndexAssetModelsTest.php` | Pass |
| F-491 | `Tests.Feature.AssetModels.Ui.RestoreAssetModelTest::test_permission_required_to_restore_asset_model` | `tests/Feature/AssetModels/Ui/RestoreAssetModelTest.php` | Pass |
| F-492 | `Tests.Feature.AssetModels.Ui.RestoreAssetModelTest::test_create_permission_alone_cannot_restore_asset_model` | `tests/Feature/AssetModels/Ui/RestoreAssetModelTest.php` | Pass |
| F-493 | `Tests.Feature.AssetModels.Ui.RestoreAssetModelTest::test_delete_permission_can_restore_asset_model` | `tests/Feature/AssetModels/Ui/RestoreAssetModelTest.php` | Pass |
| F-494 | `Tests.Feature.AssetModels.Ui.RestoreAssetModelTest::test_soft_deleted_asset_model_can_be_restored_via_web` | `tests/Feature/AssetModels/Ui/RestoreAssetModelTest.php` | Pass |
| F-495 | `Tests.Feature.AssetModels.Ui.RestoreAssetModelTest::test_restore_writes_exactly_one_restore_action_log` | `tests/Feature/AssetModels/Ui/RestoreAssetModelTest.php` | Pass |
| F-496 | `Tests.Feature.AssetModels.Ui.RestoreAssetModelTest::test_restore_does_not_write_an_update_action_log` | `tests/Feature/AssetModels/Ui/RestoreAssetModelTest.php` | Pass |
| F-497 | `Tests.Feature.AssetModels.Ui.ShowAssetModelsTest::test_page_renders` | `tests/Feature/AssetModels/Ui/ShowAssetModelsTest.php` | Pass |
| F-498 | `Tests.Feature.AssetModels.Ui.UpdateAssetModelsTest::test_permission_required_to_update_asset_model` | `tests/Feature/AssetModels/Ui/UpdateAssetModelsTest.php` | Pass |
| F-499 | `Tests.Feature.AssetModels.Ui.UpdateAssetModelsTest::test_page_renders` | `tests/Feature/AssetModels/Ui/UpdateAssetModelsTest.php` | Pass |
| F-500 | `Tests.Feature.AssetModels.Ui.UpdateAssetModelsTest::test_user_can_edit_asset_models` | `tests/Feature/AssetModels/Ui/UpdateAssetModelsTest.php` | Pass |
| F-501 | `Tests.Feature.AssetModels.Ui.UpdateAssetModelsTest::test_user_cannot_change_asset_model_category_type` | `tests/Feature/AssetModels/Ui/UpdateAssetModelsTest.php` | Pass |
| F-502 | `Tests.Feature.AssetModels.Ui.UpdateAssetModelsTest::test_default_values_remain_unchanged_after_validation_error_occurs` | `tests/Feature/AssetModels/Ui/UpdateAssetModelsTest.php` | Pass |
| F-503 | `Tests.Feature.AssetModels.Ui.UpdateAssetModelsTest::test_default_values_can_be_updated` | `tests/Feature/AssetModels/Ui/UpdateAssetModelsTest.php` | Pass |

## 8. Giới hạn và điểm cần xác nhận

- **Truy vết UC:** người giao việc xác nhận không có tài liệu UC/phân công. `S2-*` và test ID là nhãn QA nội bộ; không thể kết luận test nào đáp ứng một UC chính thức cụ thể.
- **Nghiệp vụ cần xác nhận:** quy tắc kế toán, rounding/biên ngày của khấu hao; quy tắc tỷ lệ tồn kho/model; vai trò và luồng quyền; vòng đời tag khi Asset bị xóa mềm. Báo cáo chỉ mô tả hành vi hiện có và đề xuất QA, không biến các điểm này thành yêu cầu sản phẩm.
- **Database:** kết quả 503 Feature test sử dụng SQLite in-memory. Tương thích MySQL chưa được xác minh vì Testcontainers không khởi tạo runtime.
- **Test incomplete/skipped:** quyền lưu encrypted field còn `markTestIncomplete()`; test computed count alias tự đánh dấu không tương thích SQLite; test xóa Asset đã checkout bị source đánh dấu chưa hoạt động.
- **Runtime:** full suite cần `memory_limit=1G` trong lần chạy này. Không thay đổi `php.ini` hoặc config dự án.
- **Log ứng dụng:** các excerpt lỗi fixture/memory trong `app-log-extract.txt` là bản ghi cũ; `storage/logs/laravel.log` gốc được giữ nguyên. Lượt chạy cuối không phát sinh failure mới.

## 9. Kết luận

Trong phạm vi tổng hợp do người giao việc xác nhận, các Unit/Feature test chạy trên SQLite đạt **541 pass, 0 fail, 1 skipped, 2 incomplete** trên 544 lượt test. Regression cho fixture barcode đã pass và toàn bộ hai Feature folder không còn failure. Testcontainers/MySQL integration vẫn **BLOCKED / NOT RUN** do thiếu container runtime; vì vậy chưa có bằng chứng SQL MySQL cho lượt kiểm thử này. Mã UC chính thức không được xác minh hoặc bịa đặt.

## 10. Giao nhận Git

Không commit hoặc push trong lần cập nhật này. Nhánh local `feat/test-quoc` đang tồn tại nhưng nhánh `origin/feat/test-quoc` đã bị xóa; cần rà soát trạng thái nhánh trước khi giao nhận Git. Các file agent/config cá nhân không thuộc phạm vi báo cáo và không được stage.

## 11. Đối chiếu đặc tả chính thức Phân vùng 2 (2.3.3.2)

### 11.1. Căn cứ, mã kịch bản và ma trận Severity/Priority

Phần này giữ các định danh mà người phụ trách xác nhận: `2.3.3.2.1`–`2.3.3.2.4`, `TS-02.1`–`TS-02.3`, `TC-HW-01`–`TC-HW-11`, và `TC-DP-01`–`TC-DP-03`. Tuy nhiên, trong working tree được rà soát, `reports/` không có nội dung đặc tả chứa các định danh đó, tên case, bước/dữ liệu/kết quả mong đợi hoặc giá trị Severity/Priority. Tìm kiếm theo các định danh trong báo cáo và evidence hiện có không trả về bản đặc tả. Vì vậy bảng dưới giữ nguyên mã đã được xác nhận nhưng không tự đặt tên, mức độ hoặc gán test theo suy đoán. Đây là giới hạn truy vết tài liệu, không phải kết luận rằng các test nguồn không tồn tại.

Không có đủ dữ liệu để tái tạo ma trận Severity/Priority hay mô tả chính xác ba luồng `TS-02.1`–`TS-02.3`. Các kết quả cũ ở mục 6 vẫn được giữ như kết quả chạy ở thời điểm đã ghi trong log; chúng không được nâng thành kết quả chạy mới hoặc coi là bằng chứng cho từng TC chính thức.

### 11.2. Bảng truy vết từng test case được yêu cầu

`Không thể xác minh` nghĩa là chưa thể xác định tiêu chí của TC chính thức để kết luận test nguồn hiện có có tương ứng đầy đủ hay không. Cột test nguồn ghi các test gần chủ đề trong code nếu có; đó không phải xác nhận ánh xạ 1:1. Tên chính thức và Severity/Priority được để chưa xác minh thay vì suy đoán.

| Mã TC | Tên chính thức | Severity / Priority | Test nguồn ứng viên (ánh xạ chưa xác nhận) | Bao phủ theo đặc tả | Kết quả và evidence hiện có | Khoảng trống / khác biệt |
|---|---|---|---|---|---|---|
| TC-HW-01 | Chưa xác minh trong đặc tả có trong working tree | Chưa xác minh | Không thể gán 1:1; xem ma trận hành vi đã kiểm chứng ở mục 11.3 | Không thể xác minh | Chưa có kết quả có thể gắn riêng cho TC này | Thiếu tên, tiền điều kiện, expected result và mức Severity/Priority nguyên văn. |
| TC-HW-02 | Chưa xác minh trong đặc tả có trong working tree | Chưa xác minh | Không thể gán 1:1; xem mục 11.3 | Không thể xác minh | Chưa có kết quả có thể gắn riêng cho TC này | Thiếu tiêu chí để phân biệt validation, DB constraint hoặc luồng API/UI cụ thể. |
| TC-HW-03 | Chưa xác minh trong đặc tả có trong working tree | Chưa xác minh | Không thể gán 1:1; xem mục 11.3 | Không thể xác minh | Chưa có kết quả có thể gắn riêng cho TC này | Thiếu tên và bước/dữ liệu chính thức; không suy luận từ các test tự tăng tag. |
| TC-HW-04 | Chưa xác minh trong đặc tả có trong working tree | Chưa xác minh | Không thể gán 1:1; xem mục 11.3 | Không thể xác minh | Chưa có kết quả có thể gắn riêng cho TC này | Thiếu expected result để đối chiếu kiểm tra `model_id`. |
| TC-HW-05 | Chưa xác minh trong đặc tả có trong working tree | Chưa xác minh | Không thể gán 1:1; xem mục 11.3 | Không thể xác minh | Chưa có kết quả có thể gắn riêng cho TC này | Thiếu expected result để xác định warranty boundary/validation được yêu cầu. |
| TC-HW-06 | Chưa xác minh trong đặc tả có trong working tree | Chưa xác minh | Không thể gán 1:1; xem mục 11.3 | Không thể xác minh | Chưa có kết quả có thể gắn riêng cho TC này | Thiếu định dạng tiền và quy tắc locale được nêu trong đặc tả. |
| TC-HW-07 | Chưa xác minh trong đặc tả có trong working tree | Chưa xác minh | Không thể gán 1:1; xem mục 11.3 | Không thể xác minh | Chưa có kết quả có thể gắn riêng cho TC này | Thiếu dữ liệu/expected để xác định phép tính giá trị linh kiện cần kiểm thử. |
| TC-HW-08 | Chưa xác minh trong đặc tả có trong working tree | Chưa xác minh | Không thể gán 1:1; xem mục 11.3 | Không thể xác minh | Chưa có kết quả có thể gắn riêng cho TC này | Thiếu định nghĩa trạng thái xóa mềm/restore và expected result theo đặc tả. |
| TC-HW-09 | Chưa xác minh trong đặc tả có trong working tree | Chưa xác minh | Không thể gán 1:1; xem mục 11.3 | Không thể xác minh | Chưa có kết quả có thể gắn riêng cho TC này | Không thể xác định case này có nhắm tới IMEI, MAC hay quy tắc Custom Field khác. |
| TC-HW-10 | Chưa xác minh trong đặc tả có trong working tree | Chưa xác minh | Không thể gán 1:1; xem mục 11.3 | Không thể xác minh | Chưa có kết quả có thể gắn riêng cho TC này | Không thể xác định dữ liệu và định dạng được mong đợi. |
| TC-HW-11 | Chưa xác minh trong đặc tả có trong working tree | Chưa xác minh | Không thể gán 1:1; xem mục 11.3 | Không thể xác minh | Chưa có kết quả có thể gắn riêng cho TC này | Thiếu nội dung case; không suy diễn từ tên phân vùng. |
| TC-DP-01 | Chưa xác minh trong đặc tả có trong working tree | Chưa xác minh | `DepreciationTest` có các test amount, percentage và quan hệ; không thể xác định case nào ứng với mã này | Không thể xác minh | Các test Unit trong file đã chạy theo evidence cũ; xem mục 6 và `reports/k16-quoc-evidence/unit-run.txt` | Chưa có tiêu chí đặc tả để xác định độ phủ riêng cho TC-DP-01. |
| TC-DP-02 | Chưa xác minh trong đặc tả có trong working tree | Chưa xác minh | `DepreciationTest` có test floor amount/percentage và chặn giá trị âm; không thể xác định case nào ứng với mã này | Không thể xác minh | Các test Unit trong file đã chạy theo evidence cũ; xem mục 6 và `reports/k16-quoc-evidence/unit-run.txt` | Chưa có expected result và mức ưu tiên riêng của TC-DP-02. |
| TC-DP-03 | Chưa xác minh trong đặc tả có trong working tree | Chưa xác minh | `DepreciationTest` có test tính tuyến tính trong kỳ; không thể xác định case nào ứng với mã này | Không thể xác minh | Các test Unit trong file đã chạy theo evidence cũ; xem mục 6 và `reports/k16-quoc-evidence/unit-run.txt` | Chưa có expected result và mức ưu tiên riêng của TC-DP-03. |

### 11.3. Đối chiếu hành vi triển khai với các điểm rủi ro được nêu

Bảng này ghi nhận source và test tìm được theo chủ đề; kết quả chỉ được coi là đã chạy khi có log/JUnit tương ứng ở cột evidence. Nó không thay thế tên, thứ tự hay Severity/Priority của đặc tả chính thức.

| Chủ đề | Hiện trạng trong source | Test/evidence đã tìm thấy | Mức kết luận có căn cứ và khoảng trống |
|---|---|---|---|
| `asset_tag`: DB và validation | `Asset::$rules` dùng `unique_undeleted:assets,asset_tag`. Migration tạo index thường ghép `(deleted_at, asset_tag)`, không phải unique index. | Unit `AssetTest::test_asset_tag_unique_constraint`; Feature `StoreAssetTest::test_asset_tags_must_be_unique_when_undeleted` và `...can_be_duplicated_if_deleted`. Unit/full Feature logs và JUnit hiện có. | Có evidence cũ cho validation khi chưa xóa mềm và cho phép dùng lại tag đã soft-delete. Không có bằng chứng constraint unique ở tầng DB; tên test Unit không chứng minh DB constraint. Không kiểm thử trực tiếp ghi duplicate bằng DB bypass model/validation. |
| `autoincrement_asset`: sinh mã, va chạm, số khuyết | Hàm tạo tag dùng `auto_increment_assets`, `next_auto_tag_base`, prefix và zero-fill. `nextAutoIncrement()` quét hậu tố số của tag. | Unit có `test_auto_increment`, `test_auto_increment_collision`, `test_auto_increment_double`, các test `gap_and_backfill`, prefixless và giá trị lớn. Có trong `unit-run.txt`. | Có kiểm tra tuần tự/va chạm mô phỏng bằng hai model tạo trước khi save và backfill. Không phải kiểm thử hai request/process đồng thời, transaction isolation hay tải concurrency thật; không tuyên bố đã kiểm thử concurrency. |
| `model_id` | Rule của Asset yêu cầu integer và `exists:models,id,deleted_at,NULL`, tức record Model phải tồn tại và chưa soft-delete. | Asset/Feature factories tạo asset với model hợp lệ; các Unit/Feature suite cũ chạy. | Chưa tìm thấy trong evidence đã lưu kết quả của các ca riêng cho thiếu `model_id`, ID không tồn tại, hoặc Model đã soft-delete. Không coi rule trong source là bằng chứng test đã thực thi. |
| Depreciation tuyến tính, `depreciation_min`, phần trăm và không âm | `getLinearDepreciatedValue()` được dùng trong Asset; `Depreciation` hỗ trợ loại amount/percentage và có trường `depreciation_min`. | Unit `DepreciationTest` gồm floor amount, floor percentage, giá trị không âm và tính trong kỳ; 8 test trong file nằm trong lượt Unit 41 test. | Có coverage hành vi cho các ví dụ trong test trên SQLite. Không suy ra MySQL pass; không chứng minh quy tắc kế toán/ngày/rounding ngoài assertion. |
| Custom Field IMEI/MAC và mã hóa | Custom field được validate/lưu theo cấu hình field; test mã hóa dùng Laravel `Crypt`. | Unit có test IMEI/MAC format/storage và encrypted field generic; `unit-run.txt`. Feature encrypted-field tests có trong full Feature JUnit. | Evidence không chứng minh thuật toán AES cụ thể. Test IMEI/MAC format không tự chứng minh giá trị hai trường này được mã hóa; không gộp hai khẳng định đó. Test quyền encrypted field còn incomplete. |
| Warranty months / boundary | Rule cho `warranty_months` là nullable numeric `digits_between:0,240`; accessor expiration tính từ `purchase_date` và số tháng bảo hành. | Unit `test_warranty_expires_attribute` kiểm tra 24 tháng từ 2017-01-01 đến 2019-01-01; `test_warranty_progress_percent_returns_zero_without_required_dates`. Có trong `unit-run.txt`. | Có bằng chứng accessor và thiếu ngày bắt buộc. Không thấy boundary tests cho 0/240, âm, lớn hơn 240, tháng cuối ngày hoặc leap-day. |
| Tiền tệ / `purchase_cost` | Rule Asset là numeric, không âm và có giới hạn max; StoreAsset có xử lý input dấu chấm/dấu phẩy. | Feature `test_stores_period_as_decimal_separator_for_purchase_cost`, `...comma_separator...`; nằm trong full Feature JUnit. | Có evidence theo hai trường hợp test; chưa bao phủ đầy đủ locale, dấu phân cách hàng nghìn, ký hiệu tiền hoặc mọi biên giá trị. |
| Giá trị linh kiện gán cho Asset | Asset tính tổng chi phí linh kiện theo `assigned_qty`; chi phí null được chuẩn hóa thành 0 trong các test hiện có. | Unit `test_asset_components_calculated_total_uses_assigned_quantity` và `...treats_null_purchase_cost_as_zero`; nằm trong Unit run. | Có coverage cho quantity và null cost theo fixtures này; không suy rộng ra mọi hành vi nhập/xuất, soft-delete hoặc khôi phục Component. |
| Asset soft-delete/restore | Có luồng xóa mềm và các endpoint/UI restore trong source. | Feature `DeleteAssetTest`, `BulkDeleteAssetsTest`, restore-related tests nằm trong thư mục Feature Assets được chạy theo JUnit full run. | Có coverage cho các test liệt kê trong JUnit, nhưng không khẳng định tất cả kết hợp permission/restore/dupe-tag được bao phủ. Test API xóa Asset đang checkout bị source đánh dấu skipped. |

### 11.4. Phạm vi kịch bản TS-02

| Kịch bản | Nội dung đặc tả nguyên văn | Tình trạng đối chiếu |
|---|---|---|
| TS-02.1 | Không có trong tài liệu/evidence hiện diện trong checkout | Giữ nguyên mã; không thể khẳng định flow, expected result, test mapping hoặc kết quả. |
| TS-02.2 | Không có trong tài liệu/evidence hiện diện trong checkout | Giữ nguyên mã; không thể khẳng định flow, expected result, test mapping hoặc kết quả. |
| TS-02.3 | Không có trong tài liệu/evidence hiện diện trong checkout | Giữ nguyên mã; không thể khẳng định flow, expected result, test mapping hoặc kết quả. |

## 12. Phân biệt kết quả cũ và lượt cập nhật báo cáo

Các số liệu ở mục 4–6 là kết quả cũ được chép từ artifact đã lưu: 41 Unit test, 503 Feature test của hai thư mục, và regression barcode 2 test; tổng Unit + full Feature là 544 lượt với 541 pass, 0 fail, 1 skipped, 2 incomplete. Đây là số liệu ở cấp suite, không tự chứng minh từng TC-HW/TC-DP.

Trong lượt cập nhật báo cáo này không chạy lại PHPUnit/Testcontainers và không sửa test hoặc application code. Vì vậy không có kết quả chạy mới. Testcontainers/MySQL vẫn BLOCKED/NOT RUN theo `reports/k16-quoc-evidence/testcontainers.txt`: runner không khởi tạo được runtime và dừng trước PHPUnit. Không suy diễn MySQL behavior từ kết quả SQLite.

## 13. Khoảng trống tài liệu và kết luận đối với mã TC chính thức

Các test hành vi hiện có cho tag, auto-increment, depreciation, IMEI/MAC, warranty, currency parsing, component totals và một số luồng Asset delete/restore đã chạy theo log cũ ở mục 11.3. Mức độ phù hợp với từng mã `TC-HW-*`/`TC-DP-*` chưa thể kết luận khi thiếu tên, expected result và Severity/Priority nguyên văn để lập ánh xạ. Vì vậy hiện không thể phân loại chính xác từng mã theo “đầy đủ / một phần / chưa có” mà không suy đoán; các mã ở mục 11.2 được đánh dấu “Không thể xác minh”.

Không tuyên bố đã kiểm thử concurrency thật, warranty boundary, mọi quy tắc currency parsing, `model_id` soft-delete validation, mọi component aggregation, hoặc mọi tổ hợp Asset soft-delete/restore. Những nội dung chỉ có rule trong source hoặc test gần chủ đề được tách biệt khỏi kết quả test có log. Cần lưu bản đặc tả chính thức trong hồ sơ dự án để có thể gắn nguyên văn tên, ưu tiên, điều kiện và kết quả mong đợi cho từng mã mà không thay đổi các kết quả đã ghi nhận.
