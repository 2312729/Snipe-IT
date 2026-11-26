# Kế hoạch Triển khai Kiểm thử – Phân vùng 2: Asset, Asset Model và Depreciation

> **Thông tin phạm vi:** Nguyễn Văn Quốc – MSSV 2312729 – K16 QA  
> **Phạm vi Phân vùng 2:** Quản lý tài sản phần cứng (Asset), Dòng Model tài sản (Asset Model), và Khấu hao (Depreciation).  
> **Mã đặc tả / Yêu cầu:** TS-02.1 đến TS-02.3, TC-HW-01 đến TC-HW-11, và TC-DP-01 đến TC-DP-03.

---

## 1. Mục tiêu kiểm thử

- Xác minh độ chính xác của các quy tắc nghiệp vụ quản lý tài sản phần cứng (Asset), bao gồm ràng buộc `unique_undeleted` của `asset_tag`, tự động tăng tag, custom fields, IMEI/MAC, warranty và các luồng xóa mềm / khôi phục.
- Kiểm thử các đặc tả dòng tài sản (Asset Model) và tính toán khấu hao tuyến tính (`getLinearDepreciatedValue`) đảm bảo không âm, xử lý đúng giá trị biên giữa kỳ, tỷ lệ phần trăm và floor amount.
- Đối chiếu và bổ sung kịch bản kiểm thử (test cases) theo đúng các mã TS-02.1–TS-02.3, TC-HW-01–11 và TC-DP-01–03.

---

## 2. Phạm vi chi tiết và Kế hoạch thực hiện từng Test Case (Mỗi TC là 1 commit riêng)

| Mã TC / TS | Nội dung chức năng | Phương thức / Test file dự kiến | Kế hoạch commit |
|---|---|---|---|
| **TS-02.1 / TC-HW-01..05** | Quản lý Asset, Asset Tag (`unique_undeleted`), auto-increment, validation custom fields | `tests/Feature/Assets/Api/StoreAssetTest.php`, `tests/Unit/AssetTest.php` | Commit riêng cho kiểm thử Asset Tag & Store |
| **TS-02.2 / TC-HW-06..11** | IMEI/MAC, warranty, delete/restore asset, API/UI phân quyền | `tests/Feature/Assets/` | Commit riêng cho kiểm thử Asset Delete/Restore & Attributes |
| **TS-02.3 / TC-DP-01..03** | Khấu hao tài sản (`DepreciationTest`, `getLinearDepreciatedValue`), tính giá trị còn lại, floor amount/percent | `tests/Unit/DepreciationTest.php` | Commit riêng cho kiểm thử Depreciation |

---

## 3. Nguyên tắc thực hiện
1. Không tác động đến các phân vùng khác hoặc thư mục untracked (`storage/private_uploads/assets/`).
2. Mỗi test case / nhóm thay đổi thực hiện trên nhánh `feat/test-quoc-part2` và commit riêng biệt.
3. Chạy kiểm thử xác thực bằng PHPUnit trước khi kết thúc phân vùng.
