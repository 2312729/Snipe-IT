# PHÂN VÙNG 1: MODULE API CHECKOUT, CHECKIN & QUY TRÌNH CẤP PHÁT / THU HỒI

> **Phạm vi báo cáo:** Kiểm thử API cấp phát và thu hồi tài sản Snipe-IT, tập trung vào tính đúng đắn của trạng thái, phân quyền, dữ liệu kiểm toán và khả năng thực thi trên MySQL dùng một lần với K16 Testcontainers. Báo cáo phân biệt rõ test hiện diện trong mã nguồn với test đã có bằng chứng chạy thành công trên K16.

## 1. TỔNG QUAN PHÂN VÙNG & ĐỐI TƯỢNG KIỂM THỬ (SUT)

### 1.1. Mục tiêu kiểm thử

1. Xác nhận tài sản đang sẵn sàng có thể được cấp phát qua API cho đúng đối tượng nhận được hỗ trợ: người dùng, địa điểm hoặc tài sản khác khi payload và quy tắc nghiệp vụ cho phép.
2. Xác nhận API từ chối thao tác không hợp lệ: thiếu quyền/xác thực, tài sản không tồn tại hoặc không sẵn sàng, tài sản đã được cấp phát, target không hợp lệ và checkin lặp.
3. Đảm bảo request bị từ chối không làm thay đổi assignment, địa điểm, trạng thái, bộ đếm, thời điểm nghiệp vụ, sự kiện hoặc nhật ký ngoài dự kiến.
4. Kiểm chứng luồng checkout–checkin và các dữ liệu liên quan như `assigned_to`, `assigned_type`, `location_id`, `rtd_location_id`, `checkout_counter`, `checkin_counter`, `last_checkout`, `last_checkin`, `expected_checkin`.
5. Kiểm tra tác dụng phụ nghiệp vụ: sự kiện checkout/checkin, `action_logs`, checkout request, license seat và tài sản con trong những kịch bản tương ứng.
6. Sử dụng K16 để chạy các bài kiểm thử tích hợp được chọn trên MySQL 8.0.36 tạm thời, độc lập với cơ sở dữ liệu phát triển.

### 1.2. Danh mục thành phần hệ thống thuộc phạm vi kiểm thử (SUT Components)

| Thành phần | Trách nhiệm trong SUT | Cách kiểm thử |
| --- | --- | --- |
| API routes | Cung cấp endpoint checkout/checkin và yêu cầu mượn trong API v1 | Gọi route name, tiêu biểu `api.asset.checkout`, `api.asset.checkin` |
| API controllers | Điều phối xác thực đầu vào, nghiệp vụ checkout/checkin và phản hồi JSON | Gửi request qua Laravel Feature Test |
| Authentication, middleware và Policies | Xác minh danh tính, quyền checkout/checkin và quyền yêu cầu mượn | Kiểm tra 401 khi chưa xác thực, 403 khi thiếu quyền và luồng được phép |
| Request validation và quy tắc nghiệp vụ | Kiểm tra target, trạng thái deployable, khả năng checkout/checkin | Gửi payload hợp lệ/không hợp lệ và xác nhận response cùng trạng thái sau request |
| `Asset` và quan hệ `assignedTo` | Lưu đối tượng nhận đa hình, vị trí, trạng thái, bộ đếm và thời điểm | Tải lại model từ DB rồi xác minh các cột và quan hệ |
| Events, observers và `action_logs` | Ghi nhận checkout/checkin/request và các tác dụng phụ | Fake/assert event; xác minh log cho các trường hợp được kiểm tra |
| `CheckoutAcceptance` và `LicenseSeat` | Theo dõi chấp nhận checkout và ghế bản quyền gắn với tài sản | Kiểm tra dữ liệu được xóa/giải phóng khi checkin |
| Test harness K16 | Cấp phát MySQL dùng một lần và cấu hình ứng dụng cho PHPUnit | Chạy runner `tests/testcontainers/run.mjs`; xác nhận process trả kết quả và dọn container |

**Tệp kiểm thử chính**

- Checkout: `tests/Feature/Checkouts/Api/AssetCheckoutTest.php`
- Checkin: `tests/Feature/Checkins/Api/AssetCheckinTest.php`
- Runner K16: `tests/testcontainers/run.mjs`
- Workflow CI: `.github/workflows/testcontainers.yml`

## 2. BỐI CẢNH NGHIỆP VỤ & CƠ CHẾ KỸ THUẬT NỀN TẢNG

### 2.1. Vòng đời trạng thái tài sản

Luồng chuẩn:

```text
Sẵn sàng, chưa được gán
        │
        ├── Checkout hợp lệ
        ▼
Đã gán cho User / Location / Asset
        │
        ├── Checkin hợp lệ
        ▼
Đã giải phóng đối tượng nhận, trở về vị trí RTD
```

- Khi checkout thành công, tài sản được gán cho target; quan hệ đa hình được lưu qua `assigned_type` và `assigned_to`. Tùy loại target, `location_id` cũng được đồng bộ. Bộ đếm và thông tin thời gian checkout được cập nhật theo nghiệp vụ.
- Khi checkin thành công, quan hệ gán được giải phóng; các trường checkin, trạng thái, vị trí và thông tin chấp nhận được cập nhật theo payload và quy tắc của ứng dụng. Mặc định tài sản trở về `rtd_location_id` nếu không yêu cầu vị trí khác.
- Khi checkout/checkin không hợp lệ, dữ liệu trước thao tác phải được giữ nguyên. Gửi lại checkin sau khi tài sản đã được thu hồi không được tăng bộ đếm hoặc thay đổi thời điểm checkin lần nữa.
- Checkout request là luồng yêu cầu riêng: test hiện có kiểm tra việc gửi yêu cầu với tài sản cho phép và từ chối tài sản không cho phép; chưa chứng minh bước quản trị viên duyệt yêu cầu.

### 2.2. Các cơ chế kỹ thuật trọng yếu được kiểm tra

#### Xác thực và phân quyền

- Request checkout/checkin không xác thực phải bị chặn ở tầng middleware.
- User đã xác thực nhưng thiếu quyền phù hợp không được thực hiện thao tác.
- Các test được phép phải dùng actor có quyền nghiệp vụ tương ứng.

#### Validation và quy tắc trạng thái

- Tài sản phải tồn tại và ở trạng thái có thể checkout.
- Target nhận phải tồn tại, hợp lệ và phù hợp với loại checkout.
- Tài sản đang được gán không thể được cấp phát lại theo luồng chuẩn trước khi checkin.
- Tài sản chưa checkout không thể được checkin.

#### Giao dịch và khóa hàng

Luồng checkout trong ứng dụng dùng giao dịch DB, lấy khóa hàng bằng `lockForUpdate()` và kiểm tra lại khả năng checkout trên bản ghi đã khóa. Đây là cơ chế cần được kiểm chứng bằng kiểm thử tranh chấp thực sự. Các test tuần tự hiện có **không** chứng minh được hành vi khi hai request checkout đồng thời.

#### Tính toàn vẹn và tác dụng phụ

Kiểm thử xác minh trạng thái model sau khi nạp lại từ DB; ở các ca phù hợp còn kiểm tra counters, timestamps, event, action log, license seat, acceptance và location của asset con. Không được suy ra mọi ca đều kiểm tra toàn bộ các tác dụng phụ này.

#### Phạm vi đa công ty

Một số test checkout kiểm tra quy tắc company/floater và đối tượng nhận thuộc công ty. Phạm vi này là các kịch bản cụ thể trong test suite, không thay thế đánh giá đầy đủ mọi tổ hợp cấu hình đa công ty.

## 3. MÔI TRƯỜNG THỰC THI & CHIẾN LƯỢC KIỂM THỬ TỰ ĐỘNG

### 3.1. Thiết lập hạ tầng cô lập với Testcontainers

Runner `tests/testcontainers/run.mjs` sử dụng `@testcontainers/mysql` để khởi chạy MySQL `8.0.36` tạm thời. Runner tạo thông tin database/credential cho lần chạy, chuẩn bị cấu hình ứng dụng và khóa Passport trong bộ nhớ, gọi PHPUnit với các biến môi trường tương ứng, sau đó dừng container trong `finally`.

**Điều kiện tiên quyết**

- Docker daemon hoạt động và cho phép tải/chạy image MySQL.
- Node.js và npm dependencies của repository, bao gồm package Testcontainers.
- PHP CLI tương thích với dự án, Composer dependencies và extension `pdo_mysql`.
- Cấu hình test của Laravel sẵn sàng; không dùng thông tin xác thực production.

**Chiến lược**

1. Chọn test case checkout/checkin đại diện hoặc test mới cần xác minh.
2. Mỗi lần chạy K16 tạo môi trường DB độc lập, chạy PHPUnit trên MySQL thật thay vì SQLite/mock.
3. Đánh giá đồng thời HTTP response, dữ liệu đã lưu, các event/log và exit code PHPUnit.
4. Lưu kết quả CI/log cần thiết nhưng không đưa password, private key hay dữ liệu bí mật vào artifact.
5. Chỉ báo test là **PASS trên K16** khi có log của lần chạy thực tế với exit code thành công; test có trong source hoặc workflow mới chỉ thể hiện cấu hình, không phải bằng chứng đã chạy.

Ví dụ chạy riêng từng test bằng PowerShell tại thư mục gốc repository:

```powershell
# Checkout rồi checkin khép kín
npm run test:integration:testcontainers -- tests/Feature/Checkouts/Api/AssetCheckoutTest.php --filter=test_asset_can_be_checked_out_and_checked_back_in_through_api

# Checkout target không hợp lệ, không gây thay đổi dữ liệu
npm run test:integration:testcontainers -- tests/Feature/Checkouts/Api/AssetCheckoutTest.php --filter=test_invalid_checkout_target_does_not_mutate_asset_or_create_checkout_log

# Ngăn checkin lặp
npm run test:integration:testcontainers -- tests/Feature/Checkins/Api/AssetCheckinTest.php --filter=test_checkin_cannot_be_repeated_without_mutating_asset
```

## 4. ĐẶC TẢ CHI TIẾT CÁC TEST CASE

> Các mã TC trong báo cáo dùng để tham chiếu nghiệp vụ; tên phương thức là định danh kiểm tra trong mã nguồn. Với các ca lỗi nghiệp vụ, một số endpoint trả HTTP 200 cùng JSON `status: error`; không nên tự gán HTTP 400/422 nếu chưa được assertion hoặc hợp đồng API xác nhận.

### 4.1. Checkout

#### TC-CO-01 — Checkout thành công cho người dùng qua API

- **Phương thức:** `test_asset_can_be_checked_out_through_api`
- **Loại:** Functional / Positive.
- **Tiền điều kiện:** Asset chưa được gán; user nhận tồn tại; admin có quyền checkout; status đích deployable.
- **Thao tác:** Gửi POST tới `api.asset.checkout` với `checkout_to_type=user`, `assigned_user`, `status_id`, ngày checkout và ghi chú.
- **Kỳ vọng:** HTTP 200, JSON status `success`; `assigned_to`, quan hệ `assignedTo`, `status_id` và `checkout_counter` đúng; event `CheckoutableCheckedOut` được phát.
- **Trạng thái K16:** Có trong mã nguồn. Việc chạy riêng test này trên K16 cần có kết quả runner/CI tương ứng.

#### TC-CO-02 — Checkout rồi checkin khép kín qua API

- **Phương thức:** `test_asset_can_be_checked_out_and_checked_back_in_through_api`
- **Loại:** Integration / End-to-End trong phạm vi API.
- **Tiền điều kiện:** Asset có thể checkout; admin có quyền checkout và checkin; user nhận hợp lệ.
- **Thao tác:** Checkout asset, xác minh trạng thái đã gán, sau đó gọi API checkin trên asset đó.
- **Kỳ vọng:** Hai request thành công; sau checkin `assigned_to` và `assigned_type` rỗng; counter/timestamps được cập nhật; vị trí trở về RTD location theo điều kiện test.
- **Trạng thái K16:** Đây là test được workflow cấu hình chạy làm smoke test; chưa khẳng định PASS nếu không có kết quả CI thực tế.

#### TC-CO-03 — Từ chối checkout tài sản không deployable

- **Phương thức:** `test_asset_cannot_be_checked_out_if_not_deployable`
- **Loại:** Negative / Business rule.
- **Tiền điều kiện:** Asset mang status `deployable=0`; user nhận và admin hợp lệ.
- **Thao tác:** Gửi request checkout tới user.
- **Kỳ vọng:** JSON status `error` cùng thông báo checkout không khả dụng; assignment vẫn rỗng; checkout counter không tăng.
- **Trạng thái K16:** Có trong mã nguồn; chưa có bằng chứng chạy K16 kèm theo báo cáo.

#### TC-CO-04 — Từ chối request checkout khi chưa xác thực

- **Phương thức:** `test_checkout_fails_without_oauth_passport_token`
- **Loại:** Security / Authentication.
- **Tiền điều kiện:** Không có bearer token/đăng nhập API.
- **Thao tác:** Gọi endpoint checkout trực tiếp mà không thiết lập actor xác thực.
- **Kỳ vọng:** HTTP 401 Unauthorized; không thực hiện cấp phát.
- **Trạng thái K16:** Có trong mã nguồn; chưa có bằng chứng chạy K16 kèm theo báo cáo.

#### TC-CO-05 — Gửi checkout request cho tài sản requestable

- **Phương thức:** `test_checkout_request`
- **Loại:** Business workflow / Negative + Positive.
- **Tiền điều kiện:** Có asset requestable và non-requestable; requester hợp lệ.
- **Thao tác:** Gửi yêu cầu với asset requestable, sau đó thử yêu cầu với asset non-requestable.
- **Kỳ vọng:** Yêu cầu hợp lệ trả `success`, log có `requested`; tài sản không requestable bị từ chối.
- **Giới hạn:** Test chưa thực hiện bước admin phê duyệt/chuyển trạng thái request sang approved.

#### TC-CO-06 — Ngăn checkout asset đang được gán cho người khác

- **Phương thức:** `test_checkout_fails_if_asset_already_checked_out`
- **Loại:** Negative / State conflict.
- **Tiền điều kiện:** Asset đã được gán cho `initialUser`; admin có quyền checkout; target khác tồn tại.
- **Thao tác:** Gửi request checkout asset cho target khác.
- **Kỳ vọng:** JSON status `error` và thông báo không khả dụng; sau `refresh()`, asset vẫn thuộc `initialUser`.
- **Giới hạn:** Đây là test tuần tự, không phải kiểm thử hai request đồng thời.

#### TC-CO-07 — Checkout trực tiếp cho Location

- **Phương thức:** `test_checkout_to_location_instead_of_user`
- **Loại:** Positive / Polymorphic target.
- **Tiền điều kiện:** Asset chưa gán; location và admin có quyền tồn tại.
- **Thao tác:** POST checkout với `checkout_to_type=location`, `assigned_location` và ghi chú.
- **Kỳ vọng:** Response success; `assigned_type=App\Models\Location`; `assigned_to` và `location_id` bằng ID của location; checkout counter tăng.

#### TC-CO-08 — Target checkout không hợp lệ không làm thay đổi dữ liệu

- **Phương thức:** `test_invalid_checkout_target_does_not_mutate_asset_or_create_checkout_log`
- **Loại:** Negative / Data integrity.
- **Tiền điều kiện:** Asset chưa gán; actor có quyền checkout; ID user target không tồn tại.
- **Thao tác:** Gửi request checkout với target user không hợp lệ và một status deployable.
- **Kỳ vọng:** Response status `error`; `assigned_to`/`assigned_type` vẫn rỗng; location/status/counter không đổi; không tạo action log checkout và không phát event checkout.
- **Trạng thái K16:** Đã thêm vào mã nguồn và có bước dự kiến trong workflow CI; trạng thái PASS cần xác minh từ CI run.

### 4.2. Checkin

#### TC-CI-01 — Checkin thành công và cập nhật thông tin tài sản

- **Phương thức:** `test_asset_can_be_checked_in`
- **Loại:** Functional / Positive.
- **Tiền điều kiện:** Asset đang gán cho user; actor có quyền checkin; location/status hợp lệ.
- **Thao tác:** POST checkin với tên mới, status và location.
- **Kỳ vọng:** Quan hệ gán, expected checkin và accepted được giải phóng; tên/status/location cập nhật; event checkin được phát.

#### TC-CI-02 — Mặc định trả tài sản về RTD location

- **Phương thức:** `test_location_is_set_to_rtd_location_by_default_upon_checkin`
- **Loại:** Functional / Location restoration.
- **Tiền điều kiện:** Asset đang checkout, có `rtd_location_id` khác location hiện tại.
- **Thao tác:** Checkin mà không chỉ định location mới.
- **Kỳ vọng:** `location_id` sau checkin trỏ tới RTD location; log checkin phù hợp với assertion của test.

#### TC-CI-03 — Cập nhật default location khi checkin

- **Phương thức:** `test_default_location_can_be_updated_upon_checkin`
- **Loại:** Functional / Configuration update.
- **Tiền điều kiện:** Asset đang checkout; location đích tồn tại.
- **Thao tác:** Checkin với `location_id` và `update_default_location=true`.
- **Kỳ vọng:** Quan hệ default location sau thao tác là location đã chỉ định.

#### TC-CI-04 — Giải phóng license seat khi checkin

- **Phương thức:** `test_assets_license_seats_are_cleared_upon_checkin`
- **Loại:** Integration / Data consistency.
- **Tiền điều kiện:** Asset đã checkout và có license seat đang gán cho user.
- **Thao tác:** Checkin asset.
- **Kỳ vọng:** License seat không còn user được gán.

#### TC-CI-05 — Cập nhật vị trí các asset con

- **Phương thức:** `test_checking_in_asset_updates_location_of_assets_assigned_to_it`
- **Loại:** Integration / Relationship consistency.
- **Tiền điều kiện:** Parent asset và child asset cùng gắn với location checkout; parent có RTD location.
- **Thao tác:** Checkin parent với location trả về.
- **Kỳ vọng:** Location của parent và child được cập nhật; quan hệ child vẫn trỏ đúng parent.

#### TC-CI-06 — Xử lý dữ liệu location legacy

- **Phương thức:** `test_legacy_location_values_set_to_zero_are_updated`
- **Loại:** Compatibility / Data normalization.
- **Tiền điều kiện:** Asset dùng giá trị location legacy bằng `0`.
- **Thao tác:** Checkin asset.
- **Kỳ vọng:** `rtd_location_id` legacy được chuẩn hóa; location và RTD location nhất quán theo assertions.

#### TC-CI-07 — Xóa checkout acceptance đang chờ khi checkin

- **Phương thức:** `test_pending_checkout_acceptances_are_cleared_upon_checkin`
- **Loại:** Integration / Cleanup.
- **Tiền điều kiện:** Asset checkout có acceptance đang pending.
- **Thao tác:** Checkin asset.
- **Kỳ vọng:** Pending acceptance được xóa.

#### TC-CI-08 — Ghi nhận ngày checkin và ghi chú action

- **Phương thức:** `test_checkin_time_and_action_log_note_can_be_set`
- **Loại:** Functional / Audit data.
- **Tiền điều kiện:** Asset đang checkout; actor có quyền checkin.
- **Thao tác:** Gửi `checkin_at` và `note`.
- **Kỳ vọng:** Event checkin chứa ngày và ghi chú tương ứng.

#### TC-CI-09 — Điều khiển việc xóa tên khi checkin

- **Phương thức:** `test_asset_name_is_cleared_on_checkin_when_clear_name_is_set` và `test_asset_name_is_not_cleared_on_checkin_when_clear_name_is_not_set`
- **Loại:** Functional / Boundary behavior.
- **Tiền điều kiện:** Asset có tên trước khi checkin.
- **Thao tác:** So sánh checkin có và không có `clear_name`.
- **Kỳ vọng:** Tên chỉ bị xóa khi yêu cầu xóa được gửi.

#### TC-CI-10 — Không cho checkin lặp gây thay đổi lần hai

- **Phương thức:** `test_checkin_cannot_be_repeated_without_mutating_asset`
- **Loại:** Negative / Idempotency guard.
- **Tiền điều kiện:** Asset đang checkout; actor có quyền checkin.
- **Thao tác:** Checkin lần đầu thành công; lưu `checkin_counter` và `last_checkin`; gửi checkin lần hai.
- **Kỳ vọng:** Lần đầu `success`, lần hai `error`; assignment vẫn rỗng; counter và timestamp giữ nguyên so với sau lần đầu; event `CheckoutableCheckedIn` chỉ được phát một lần.
- **Trạng thái K16:** Đã thêm vào mã nguồn và có bước dự kiến trong workflow CI; trạng thái PASS cần xác minh từ CI run.

### 4.3. Nhóm kiểm thử bổ sung đang có trong hai tệp

Ngoài các trường hợp mô tả ở trên, test suite còn có kiểm tra quyền checkout/checkin, asset không tồn tại, asset không sẵn sàng/chưa checkout, các biến thể target và payload, quy tắc company/floater, location khi checkin, license seats, asset con, acceptance và action event. Cần xem trực tiếp từng method/assertion trước khi đưa một test cụ thể vào danh sách “đã chạy K16”.

## 5. TIÊU CHÍ PASS/FAIL, KẾT QUẢ VÀ GIỚI HẠN

### 5.1. Tiêu chí nghiệm thu

- **PASS:** PHPUnit exit code `0`; response đúng hợp đồng API; dữ liệu nạp lại từ MySQL đáp ứng assertions; event/log đúng theo ca; runner kết thúc và dọn container.
- **FAIL:** PHPUnit báo assertion/test error, tiến trình trả exit code khác `0`, dữ liệu bị thay đổi sai khi request lỗi, hoặc container không được dọn theo quy trình.
- Mỗi lần chạy cần lưu commit SHA, phiên bản PHP/Node, image MySQL, lệnh thực thi, thời lượng và kết quả CI. Không lưu secret trong log/artifact.

### 5.2. Trạng thái bằng chứng hiện có

- Hai test mới — checkout target không hợp lệ và checkin lặp — đã được thêm vào mã nguồn.
- Workflow K16 có cấu hình bước chạy smoke test checkout–checkin và các test mới.
- Không có kết quả PHPUnit/Testcontainers/CI run được đính kèm trong báo cáo này. Vì vậy, các test mới được ghi nhận là **đã cài đặt trong source**, không phải **đã xác nhận PASS trên K16**.
- Test checkout request hiện chưa bao phủ bước admin phê duyệt.
- Chưa có kiểm thử concurrency với hai request/worker độc lập. Việc gọi hai request lần lượt không đủ để chứng minh chống race condition.

### 5.3. Công việc tiếp theo

1. Chạy từng test được chọn trên máy có Docker hoạt động và lưu log kết quả cùng commit SHA.
2. Xác nhận workflow GitHub Actions thực sự nhận và truyền chính xác các đối số PHPUnit trước khi xem kết quả CI là bằng chứng.
3. Bổ sung concurrency test checkout bằng hai kết nối/worker độc lập, fixture dùng chung nhìn thấy được và đồng bộ thời điểm gửi request; xác nhận chỉ một request có thể cấp phát asset.
4. Nếu phạm vi yêu cầu kiểm thử toàn bộ request approval, bổ sung bước admin duyệt và assertion chuyển trạng thái thay vì suy rộng từ test gửi request hiện tại.
