    # Báo cáo kiểm thử K16 – Nguyễn Văn Quốc (Phân vùng 2)

    | Mục | Thông tin |
    |---|---|
    | Người thực hiện | Nguyễn Văn Quốc (MSSV 2312729) |
    | Dự án | Snipe-IT |
    | Phạm vi | Asset, Asset Model, depreciation và custom field của Asset |
    | Ngày lập báo cáo | 2026-11-26 |
    | Nguồn kết quả | Log PHPUnit/JUnit và ghi nhận môi trường đã lưu trong `reports/` |

    > Báo cáo này tổng hợp kết quả đã có trong repository; trong lượt hoàn thiện báo cáo này không chạy lại test. Không tìm thấy đặc tả User Case (UC) chính thức cho phần được giao. Các mã `S2-*` và `TC-*` bên dưới là mã truy vết/test nội bộ, không phải mã UC.

    ## 1. Căn cứ và phạm vi kiểm thử

    Phạm vi được tổng hợp từ `milestone-2-onboarding.md`, `milestone-2-report.md`, báo cáo QA trước đó, các test hiện có và kết quả chạy được lưu trong `reports/k16-quoc-evidence/`. Báo cáo trước đó ghi nhận không có tài liệu phân công/UC riêng; vì vậy không gán mã UC và không xem các giả định kiểm thử là yêu cầu nghiệp vụ chính thức.

    | Mã phạm vi nội bộ | Nội dung kiểm thử | Căn cứ trong repo | Giới hạn truy vết |
    |---|---|---|---|
    | S2-ASSET | Asset, tạo `asset_tag`, tự tăng/backfill, tính duy nhất, API tạo Asset | `tests/Unit/AssetTest.php`; `tests/Feature/Assets/` | Chưa có UC chính thức cho vòng đời tag, vai trò hoặc toàn bộ luồng Asset. |
    | S2-CF | Custom field của Asset: lưu/mã hóa/giải mã, định dạng IMEI/MAC, API validation | `AssetTest`; `tests/Feature/Assets/Api/StoreAssetTest.php` và các Feature test Assets | Quy tắc quyền theo từng vai trò và vòng đời dữ liệu chưa được đặc tả UC. |
    | S2-DEPR | Quan hệ Depreciation và tính khấu hao tuyến tính, floor theo amount/percent, không âm | `tests/Unit/DepreciationTest.php`; `AssetTest` | Chưa xác nhận quy tắc kế toán, làm tròn, biên ngày và timezone chính thức. |
    | S2-MODEL | Quan hệ Asset–Asset Model; `percentRemaining()` với count thường/eager-loaded và trường hợp 0 | `tests/Unit/AssetModelTest.php`; `tests/Feature/AssetModels/` | Chưa có UC xác định công thức/bộ lọc nghiệp vụ chính thức. |
    | S2-FEATURE | Regression cho toàn bộ Feature test trong hai thư mục Assets và AssetModels; kiểm tra quyền barcode | `tests/Feature/Assets/`; `tests/Feature/AssetModels/`; `AssetBarcodeAuthorizationTest.php` | Đây là kiểm thử backend trên SQLite, không thay thế kiểm thử UI thủ công. |
    | S2-MYSQL | Thử chạy test tag trùng trên MySQL ephemeral bằng runner Testcontainers của repo | `tests/testcontainers/run.mjs`; lệnh trong `testcontainers.txt` | Runner không tìm được container runtime nên PHPUnit chưa chạy trên MySQL. |

    ## 2. Kế hoạch kiểm thử tổng thể

    | Giai đoạn | Hoạt động | Đầu ra/tiêu chí |
    |---|---|---|
    | 1. Khoanh phạm vi | Đối chiếu tài liệu milestone, source và test; dùng mã phạm vi nội bộ khi không có UC | Phạm vi truy vết được, không tuyên bố có UC chính thức |
    | 2. Kiểm tra môi trường | Ghi nhận PHP, Laravel, PHPUnit, Node, DB test và container runtime | Tách rõ môi trường chạy được và điều kiện bị thiếu |
    | 3. Unit | Chạy ba file Unit Asset, Depreciation, AssetModel | Assertion về model, custom field, tag, quan hệ và phép tính |
    | 4. Feature chọn lọc | Chạy StoreAsset, AssetsForAssetModel và regression barcode | Xác minh API/authorization trọng tâm; giữ nguyên incomplete |
    | 5. Feature regression | Chạy toàn bộ `tests/Feature/Assets` và `tests/Feature/AssetModels`, lưu JUnit XML | Không có failure/error; ghi riêng skipped/incomplete |
    | 6. MySQL ephemeral | Chạy test tag trùng qua runner Testcontainers | Chỉ kết luận MySQL nếu container và PHPUnit thực sự chạy |
    | 7. Đối chiếu và báo cáo | Đối chiếu log, JUnit XML, log ứng dụng trích lọc và giới hạn | Mỗi kết luận có file evidence; phân biệt lỗi test với giới hạn môi trường |

    ## 3. Đặc tả test case

    Mã `TC-*` là mã nội bộ của báo cáo. Các method được gọi đúng theo tên trong source test. Kết quả thực tế bên dưới dựa vào log đã lưu; các test chọn lọc có giao với full suite nên không cộng lặp vào tổng.

    | Test case ID | Chức năng/điều kiện và test nguồn | Bước thực hiện và dữ liệu | Kết quả mong đợi | Kết quả thực tế |
    |---|---|---|---|---|
    | TC-A-01 | S2-ASSET – `AssetTest::test_asset_tag_unique_constraint` | Tạo Asset hợp lệ với tag `UNIQUE-TEST-001`; thử lưu Asset thứ hai cùng tag; kiểm tra kết quả save và lỗi model. | Asset thứ hai không được lưu; lỗi được gắn với `asset_tag`. | Pass; assertion save thất bại và lỗi `asset_tag` đều đạt. |
    | TC-A-02 | S2-ASSET – sinh tag tự tăng/backfill; các method `test_auto_increment*` trong `AssetTest` | Tạo/kiểm tra các trường hợp tag có prefix, va chạm, khoảng trống/backfill và giá trị lớn theo fixture của test. | Tag được sinh theo quy tắc hiện có; xử lý va chạm/khoảng trống đúng assertion. | Pass trong lượt Unit tổng hợp; xem `unit-run.txt`. |
    | TC-CF-01 | S2-CF – `AssetTest::test_encrypted_custom_field_encryption_and_decryption` | Lưu ciphertext của `This is a test encrypted value`; đọc trực tiếp DB và giải mã. | DB không chứa plaintext; ciphertext lưu đúng và giải mã thành giá trị ban đầu. | Pass; các assertion DB/ciphertext/decrypt đạt. |
    | TC-CF-02 | S2-CF – `AssetTest::test_mac_address_custom_field_format_and_storage` | Lưu MAC `00:1A:2B:3C:4D:5E`, sau đó thử `invalid-mac-address`; kiểm tra DB/validation. | MAC hợp lệ được lưu; dữ liệu sai định dạng bị từ chối. | Pass. |
    | TC-CF-03 | S2-CF – `AssetTest::test_imei_custom_field_format_and_storage` | Lưu IMEI `490154203237518`, sau đó thử `not-an-imei`; kiểm tra DB/validation. | IMEI 15 chữ số được lưu; dữ liệu sai bị từ chối. | Pass. |
    | TC-D-01 | S2-DEPR – `DepreciationTest::test_depreciation_amount` | Asset cost 4000, floor amount 1000, kỳ 36 tháng, mua 10 năm trước; gọi `getLinearDepreciatedValue()`. | Giá trị còn lại không thấp hơn floor 1000. | Pass; giá trị bằng floor theo assertion. |
    | TC-D-02 | S2-DEPR – `DepreciationTest::test_depreciation_percentage` | Cost 4000, floor 50%, kỳ 36 tháng, mua 10 năm trước; gọi hàm khấu hao. | Giá trị còn lại bằng 2000. | Pass; assertion 2000 đạt. |
    | TC-D-03 | S2-DEPR – `test_linear_depreciation_never_negative_and_respects_floor` | Cost 4000, floor amount 1000, kỳ 12 tháng, đã 24 tháng; gọi hàm khấu hao. | Kết quả bằng floor và không thấp hơn 1000. | Pass; cả hai assertion đạt. |
    | TC-D-04 | S2-DEPR – `test_linear_depreciation_percentage_never_negative` | Cost 4000, floor 25%, kỳ 12 tháng, đã 24 tháng. | Kết quả bằng 1000 và không âm. | Pass. |
    | TC-D-05 | S2-DEPR – `test_linear_depreciation_early_period_calculates_correctly` | Cost 2400, floor 0, kỳ 24 tháng, đã 6 tháng. | Còn 75%, tương ứng 1800. | Pass; assertion 1800 đạt. |
    | TC-M-01 | S2-MODEL – `AssetModelTest::test_percent_remaining_returns_expected_ratio_for_mixed_availability` | Asset Model có 2 Asset khả dụng trên tổng 5; gọi `percentRemaining()`. | Trả 40.0%. | Pass. |
    | TC-M-02 | S2-MODEL – `test_percent_remaining_skips_queries_when_counts_were_eager_loaded` | Eager-load counts 3/4; gắn DB listener rồi gọi `percentRemaining()`. | Trả 75%; không phát sinh query count bổ sung. | Pass; query count bằng 0 và tỷ lệ đúng. |
    | TC-M-03 | S2-MODEL – `test_percent_remaining_returns_zero_when_eager_loaded_counts_are_string_zero` | Eager-loaded values `remaining='3'`, `assets_count='0'`; gọi `percentRemaining()`. | Trả 0, không chia cho 0. | Pass. |
    | TC-F-A-01 | S2-ASSET – `StoreAssetTest::test_asset_tags_must_be_unique_when_undeleted` | Qua API tạo Asset với tag `1234567890` hai lần dưới điều kiện test superuser/model/status đã tạo. | Lần đầu thành công; lần sau báo validation lỗi tag trùng. | Pass trong full Feature run. |
    | TC-F-A-02 | S2-ASSET – `StoreAssetTest::test_asset_tags_can_be_duplicated_if_deleted` | Tạo Asset tag `1234567890`, soft-delete Asset đó, rồi POST Asset mới cùng tag. | Tạo Asset mới thành công theo quy tắc `unique_undeleted` hiện có. | Pass trong full Feature run. |
    | TC-F-CF-01 | S2-CF – `StoreAssetTest::test_encrypted_custom_field_can_be_stored` | POST plaintext `This is encrypted field`; đọc lại Asset và giải mã giá trị. | API lưu thành công; giá trị giải mã khớp input. | Pass trong full Feature run trên SQLite. |
    | TC-F-CF-02 | S2-CF – `StoreAssetTest::test_encrypted_custom_field_validation_fails` | POST giá trị không khớp rule alpha, `Thisisencryptedfield123`; kiểm tra JSON error. | API từ chối và trả lỗi cho custom field tương ứng. | Pass trong full Feature run trên SQLite. |
    | TC-F-CF-03 | S2-CF – `StoreAssetTest::test_permission_needed_to_store_encrypted_field` | Test hiện có đánh dấu `markTestIncomplete()` trước khi hoàn tất kiểm tra quyền ghi encrypted field. | Cần hoàn tất assertion quyền rồi chạy lại để xác minh behavior. | Incomplete theo source và PHPUnit; chưa có kết luận pass/fail nghiệp vụ. |
    | TC-MYSQL-01 | S2-MYSQL – `StoreAssetTest::test_asset_tags_must_be_unique_when_undeleted` qua Testcontainers | Chạy npm Testcontainers runner với test lọc tag trùng; yêu cầu Docker/Podman runtime khả dụng. | PHPUnit chạy trong MySQL ephemeral và xác nhận lần lưu tag trùng bị từ chối. | Blocked/Not run: runner dừng trước PHPUnit với `Could not find a working container runtime strategy`. |

    ## 4. Tóm tắt lần chạy và trạng thái

    Các thống kê dưới đây lấy từ output có sẵn, không phải kết quả chạy trong lượt viết báo cáo. Cấu hình test được ghi nhận là SQLite `:memory:`; môi trường log ghi PHP 8.5.11, Laravel 12.68.0, PHPUnit 11.5.55, Node 24.21.0.

    | Lệnh/bộ test đã lưu | Kết quả |
    |---|---|
    | Ba Unit file: `AssetTest.php`, `DepreciationTest.php`, `AssetModelTest.php` | 41 tests, 96 assertions; 41 pass, 0 fail, 0 skip, 0 incomplete; exit 0. |
    | Feature chọn lọc: `StoreAssetTest.php` + `AssetsForAssetModelTest.php` | 48 tests, 190 assertions; 47 pass, 1 incomplete, 0 fail; exit 0. Bộ này giao với full Feature run. |
    | `AssetBarcodeAuthorizationTest.php` | 2 tests, 2 assertions; pass; exit 0. |
    | Full `Feature/Assets` + `Feature/AssetModels` với memory limit 1G | 503 tests, 1,864 assertions; 500 pass, 1 skipped, 2 incomplete, 0 failure/error; exit 0. |
    | Tổng Unit + full Feature (không cộng lặp bộ chọn lọc/barcode) | 544 test executions: 541 pass, 0 fail, 1 skipped, 2 incomplete. |
    | Testcontainers, test tag trùng | Exit 1 ở bước khởi tạo container; PHPUnit không được gọi trong container; phân loại Blocked/Not run, không tính là PHPUnit fail. |

    ### Skip và incomplete được xác định

    - `StoreAssetTest::test_permission_needed_to_store_encrypted_field`: source gọi `markTestIncomplete()`; quyền ghi custom field chưa được xác minh.
    - `IndexAssetModelsTest::test_asset_model_index_filter_can_search_computed_count_aliases`: được đánh dấu incomplete vì không tương thích SQLite; cần chạy DB tương thích để xác minh.
    - `DeleteAssetsTest::test_cannot_delete_asset_that_is_checked_out`: source gọi `markTestSkipped('This behavior is not functioning yet.')`; đây là behavior chưa hoạt động theo ghi chú trong test, không phải pass.

    ## 5. Log, bằng chứng và vấn đề được ghi nhận

    - `reports/k16-quoc-evidence/unit-run.txt`: lệnh và thống kê Unit.
    - `reports/k16-quoc-evidence/feature-focused.txt`: Feature chọn lọc, có 1 incomplete.
    - `reports/k16-quoc-evidence/feature-full.txt` và `feature-assets-assetmodels.junit.xml`: full Feature output và JUnit XML chi tiết.
    - `reports/k16-quoc-evidence/barcode-feature.txt`: regression quyền barcode.
    - `reports/k16-quoc-evidence/testcontainers.txt`: lỗi khởi tạo runtime, trước bước PHPUnit.
    - `reports/k16-quoc-evidence/quality-checks.txt`: Pint, PHP syntax và `git diff --check` đã ghi nhận pass.
    - `reports/k16-quoc-evidence/app-log-extract.txt`: hai log cũ, có trước full Feature run thành công cuối cùng: fallback barcode `public/uploads/barcodes/invalid_barcode.gif` bị thiếu và PHP CLI hết memory limit 128 MB khi compile Blade. Đây là sự cố lịch sử trong log, không phải failure còn lại của lượt full Feature cuối.

    Báo cáo QA cũ ghi nhận regression barcode pass sau khi bổ sung fixture GIF 1×1 và chạy full suite với `memory_limit=1G`. Lượt báo cáo này chỉ trích lại bằng chứng đã lưu; không thay đổi fixture, log gốc hoặc cấu hình chạy.

    `reports/k16-testcontainers/test.txt` chỉ có nội dung `hello`, không phải log chạy kiểm thử và không được dùng làm căn cứ kết quả. Không có bằng chứng trong các file đã rà soát về ảnh chụp demo UI hoặc một lượt CI GitHub thành công; báo cáo không tuyên bố hai mục này đã hoàn tất.

    ## 6. Giới hạn kiểm thử

    1. Chưa có UC/phân công chính thức được tìm thấy; phạm vi và mã test là tổng hợp QA nội bộ.
    2. Feature suite chạy SQLite in-memory. Test có ghi rõ không tương thích SQLite chưa được xác minh trên MySQL.
    3. Testcontainers không khởi tạo được do thiếu container runtime khả dụng; không có kết luận từ MySQL ephemeral.
    4. Hai test incomplete và một test skipped giữ nguyên trạng thái như source/output; các test này không được tính pass.
    5. Không có bằng chứng UI thủ công hoặc GitHub Actions green trong các artifact được rà soát.
    6. Log Laravel trích lọc là log lịch sử; bản gốc không được sửa trong quá trình ghi nhận evidence.

    ## 7. Kết luận

    Theo các output hiện có, Unit và full Feature scope trên SQLite có **541 pass, 0 fail, 1 skipped và 2 incomplete trên 544 lượt**. Test regression barcode riêng cũng pass (2/2). Đây là kết quả backend trong phạm vi các thư mục/test nêu trên, không chứng minh toàn bộ ứng dụng, UI hoặc MySQL.

    Kiểm thử MySQL bằng Testcontainers vẫn **Blocked/Not run**, do runner không tìm thấy container runtime trước khi gọi PHPUnit. Cần chạy lại trên máy có Docker Engine/Desktop đang hoạt động để xác minh SQL MySQL. Các mã phạm vi/test trong báo cáo không phải UC chính thức.
