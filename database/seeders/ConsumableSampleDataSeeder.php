<?php

namespace Database\Seeders;

use App\Models\Actionlog;
use App\Models\Category;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsumableSampleDataSeeder extends Seeder
{
    /**
     * Run database seeds for Consumables and Assignments.
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->command->info('====================================================');
        $this->command->info(' BẮT ĐẦU NẠP DỮ LIỆU MẪU VẬT TƯ TIÊU HAO (CONSUMABLES)');
        $this->command->info('====================================================');

        // 1. Danh mục & Vị trí
        $catConsumable = Category::firstOrCreate(
            ['name' => 'Vật tư tiêu hao (IT Consumables)', 'category_type' => 'consumable']
        );
        $locationCentral = Location::firstOrCreate(['name' => 'Kho Tổng CNTT - Trung tâm Đà Lạt (Central IT Warehouse)']);
        $locationHcm = Location::firstOrCreate(['name' => 'Chi nhánh Quận 1 - TP. Hồ Chí Minh']);
        $supplierPhongVu = Supplier::firstOrCreate(['name' => 'Phong Vũ B2B Technology']);
        $supplierFpt = Supplier::firstOrCreate(['name' => 'FPT IS Enterprise Solutions']);
        $mfCommscope = Manufacturer::firstOrCreate(['name' => 'CommScope']);
        $mfHp = Manufacturer::firstOrCreate(['name' => 'HP Inc.']);
        $mfCanon = Manufacturer::firstOrCreate(['name' => 'Canon Inc.']);
        $mfBrother = Manufacturer::firstOrCreate(['name' => 'Brother International']);

        // 2. Danh sách User để gán
        $admin = User::find(1) ?? User::first();
        $userAn = User::where('username', 'nguyen.van.an')->first() ?? $admin;
        $userBich = User::where('username', 'tran.thi.bich')->first() ?? $admin;
        $userNam = User::where('username', 'le.hoang.nam')->first() ?? $admin;
        $userTri = User::where('username', 'pham.minh.tri')->first() ?? $admin;
        $userHa = User::where('username', 'doan.thanh.ha')->first() ?? $admin;
        $userBao = User::where('username', 'vu.quoc.bao')->first() ?? $admin;

        // 3. Cập nhật và nâng cấp Vật tư số 2: Cuộn cáp mạng CommScope Cat6 UTP 305m
        $consumable2 = Consumable::find(2);
        if ($consumable2) {
            $consumable2->qty = 20; // Nâng tổng số lượng lên 20 cuộn
            $consumable2->min_amt = 3;
            $consumable2->company_id = null; // Hiển thị toàn hệ thống (không bị kẹt bộ lọc)
            $consumable2->location_id = $locationCentral->id;
            $consumable2->save();
        }

        // Cập nhật tất cả các vật tư cũ (IDs 1-10) bỏ company_id cứng để không bị lọc
        Consumable::where('id', '<=', 10)->update(['company_id' => null]);

        // 4. Danh sách các Vật tư tiêu hao mở rộng (IDs 11 - 25)
        $newConsumablesData = [
            [
                'name' => 'Dây nhảy mạng Cat6 Patch Cord UTP 1.5m đúc sẵn (Bịch 10 sợi)',
                'qty' => 50,
                'min_amt' => 10,
                'model_number' => 'PC-CAT6-1.5M-BLU',
                'item_no' => 'CSM-NET-011',
                'legacy_order_number' => 'PO-2026-CSM-001',
                'notes' => 'Kệ Phụ kiện mạng Cabinet M01',
            ],
            [
                'name' => 'Dây nhảy mạng Cat6 Patch Cord UTP 3m đúc sẵn (Bịch 10 sợi)',
                'qty' => 40,
                'min_amt' => 8,
                'model_number' => 'PC-CAT6-3.0M-BLU',
                'item_no' => 'CSM-NET-012',
                'legacy_order_number' => 'PO-2026-CSM-001',
                'notes' => 'Kệ Phụ kiện mạng Cabinet M01',
            ],
            [
                'name' => 'Keo tản nhiệt CPU Arctic MX-4 4g cao cấp cho Server & PC',
                'qty' => 30,
                'min_amt' => 5,
                'model_number' => 'ACT-MX4-4G',
                'item_no' => 'CSM-HW-013',
                'legacy_order_number' => 'PO-2026-CSM-002',
                'notes' => 'Ngăn kéo dụng cụ kỹ thuật bảo trì Tầng 2',
            ],
            [
                'name' => 'Cồn Isopropyl Alcohol 99% chai 500ml lau sợi quang & vi mạch',
                'qty' => 25,
                'min_amt' => 4,
                'model_number' => 'IPA-99-500ML',
                'item_no' => 'CSM-HW-014',
                'legacy_order_number' => 'PO-2026-CSM-002',
                'notes' => 'Tủ hóa chất an toàn phòng Lab Đà Lạt',
            ],
            [
                'name' => 'Dây rút nhựa chống cháy 200mm (Bịch 100 sợi)',
                'qty' => 100,
                'min_amt' => 20,
                'model_number' => 'CT-200MM-BLK',
                'item_no' => 'CSM-ACC-015',
                'legacy_order_number' => 'PO-2026-CSM-003',
                'notes' => 'Thùng phụ liệu thi công cáp kho tổng',
            ],
            [
                'name' => 'Pin CMOS Maxell CR2032 3V (Vỉ 5 viên cho Server/PC)',
                'qty' => 60,
                'min_amt' => 10,
                'model_number' => 'MXL-CR2032-5P',
                'item_no' => 'CSM-HW-016',
                'legacy_order_number' => 'PO-2026-CSM-003',
                'notes' => 'Hộp linh kiện thay thế nhỏ tủ K02',
            ],
            [
                'name' => 'Bút lau đầu quang sợi One-Click Cleaner SC/LC 2.5mm/1.25mm',
                'qty' => 15,
                'min_amt' => 3,
                'model_number' => 'OCC-FIBER-SCLC',
                'item_no' => 'CSM-OPT-017',
                'legacy_order_number' => 'PO-2026-CSM-004',
                'notes' => 'Vali đồ nghề chuyên dụng quang',
            ],
            [
                'name' => 'Túi hạt hút ẩm Silica Gel 100g bảo quản thiết bị lưu kho',
                'qty' => 80,
                'min_amt' => 15,
                'model_number' => 'SG-100G-DRY',
                'item_no' => 'CSM-WH-018',
                'legacy_order_number' => 'PO-2026-CSM-004',
                'notes' => 'Kệ lưu trữ thiết bị điện tử nhạy cảm',
            ],
            [
                'name' => 'Khăn lau phòng sạch không bụi Cleanroom Wiper 1009D (100 miếng)',
                'qty' => 35,
                'min_amt' => 5,
                'model_number' => 'CW-1009D-9IN',
                'item_no' => 'CSM-CL-019',
                'legacy_order_number' => 'PO-2026-CSM-005',
                'notes' => 'Phòng sạch Server Data Center',
            ],
            [
                'name' => 'Bình xịt bôi trơn và phục hồi tiếp xúc DeoxIT D5 Contact Cleaner',
                'qty' => 18,
                'min_amt' => 3,
                'model_number' => 'CAIG-D5S-6',
                'item_no' => 'CSM-HW-020',
                'legacy_order_number' => 'PO-2026-CSM-005',
                'notes' => 'Hộp dụng cụ cứu hộ phần cứng',
            ],
            [
                'name' => 'Tem nhãn mã vạch Decal PVC nhiệt 50x30mm cuộn 1000 tem',
                'qty' => 45,
                'min_amt' => 8,
                'model_number' => 'PVC-LBL-5030-1K',
                'item_no' => 'CSM-LBL-021',
                'legacy_order_number' => 'PO-2026-CSM-006',
                'notes' => 'Khu in tem kiểm định QR Code',
            ],
            [
                'name' => 'Dây nguồn C13-C14 1.8m chuẩn Server PDU lõi đồng 3x1.5mm2',
                'qty' => 60,
                'min_amt' => 10,
                'model_number' => 'PWR-C13C14-1.8M',
                'item_no' => 'CSM-PWR-022',
                'legacy_order_number' => 'PO-2026-CSM-006',
                'notes' => 'Kệ nguồn Rack DataCenter',
            ],
            [
                'name' => 'Bộ ốc vít và tán cài Rack 19 inch M6 (Hộp 50 bộ)',
                'qty' => 40,
                'min_amt' => 8,
                'model_number' => 'RCK-M6-SCREW-50',
                'item_no' => 'CSM-RCK-023',
                'legacy_order_number' => 'PO-2026-CSM-007',
                'notes' => 'Hộp phụ kiện lắp đặt Rack Data Center',
            ],
            [
                'name' => 'Dây nối đất tiếp địa chống sét cho tủ Rack 10AWG (Cuộn 20m)',
                'qty' => 12,
                'min_amt' => 2,
                'model_number' => 'GND-10AWG-20M',
                'item_no' => 'CSM-GND-024',
                'legacy_order_number' => 'PO-2026-CSM-007',
                'notes' => 'Kho điện nguồn kỹ thuật',
            ],
            [
                'name' => 'Cáp chuyển đổi USB-C sang DisplayPort 4K 1.8m cho trạm làm việc',
                'qty' => 30,
                'min_amt' => 5,
                'model_number' => 'ADP-USBC-DP-4K',
                'item_no' => 'CSM-CBL-025',
                'legacy_order_number' => 'PO-2026-CSM-008',
                'notes' => 'Hộc tủ cáp chuyển đổi văn phòng',
            ],
        ];

        foreach ($newConsumablesData as $cData) {
            Consumable::firstOrCreate(
                ['name' => $cData['name']],
                array_merge($cData, [
                    'category_id' => $catConsumable->id,
                    'company_id' => null, // Không khóa company
                    'location_id' => $locationCentral->id,
                    'manufacturer_id' => $mfCommscope->id,
                    'default_supplier_id' => $supplierPhongVu->id,
                    'requestable' => 1,
                ])
            );
        }

        // 5. NẠP DỮ LIỆU XUẤT KHO (CHECKOUTS) CHO VẬT TƯ SỐ 2 (Cuộn cáp mạng CommScope Cat6)
        $this->command->info('-> Đang tạo dữ liệu xuất kho bàn giao cho Vật tư #2 (Cuộn cáp mạng CommScope)...');

        // Xóa checkout cũ nếu có để tránh trùng lặp
        DB::table('consumables_users')->where('consumable_id', 2)->delete();
        Actionlog::where('item_type', Consumable::class)->where('item_id', 2)->delete();

        $checkoutsItem2 = [
            [
                'assigned_to' => $admin->id,
                'created_by' => $admin->id,
                'note' => 'Xuất 1 cuộn thi công đường cáp LAN phòng Lab kiểm thử K16 (Đà Lạt)',
                'days_ago' => 10,
            ],
            [
                'assigned_to' => $userAn->id,
                'created_by' => $admin->id,
                'note' => 'Xuất 1 cuộn kéo cáp đấu nối Switch Core DataCenter Rack A1',
                'days_ago' => 7,
            ],
            [
                'assigned_to' => $userBich->id,
                'created_by' => $admin->id,
                'note' => 'Xuất 1 cuộn thay thế hạ tầng dây mạng khu vực kiểm thử tự động Automation QA',
                'days_ago' => 5,
            ],
            [
                'assigned_to' => $userNam->id,
                'created_by' => $admin->id,
                'note' => 'Xuất 1 cuộn dự phòng kết nối trạm phụ chi nhánh TP. Hồ Chí Minh',
                'days_ago' => 2,
            ],
        ];

        foreach ($checkoutsItem2 as $chk) {
            $checkoutTime = now()->subDays($chk['days_ago']);
            
            DB::table('consumables_users')->insert([
                'consumable_id' => 2,
                'assigned_to' => $chk['assigned_to'],
                'created_by' => $chk['created_by'],
                'note' => $chk['note'],
                'created_at' => $checkoutTime,
                'updated_at' => $checkoutTime,
            ]);

            Actionlog::create([
                'item_type' => Consumable::class,
                'item_id' => 2,
                'action_type' => 'checkout',
                'target_type' => User::class,
                'target_id' => $chk['assigned_to'],
                'created_by' => $chk['created_by'],
                'note' => $chk['note'],
                'created_at' => $checkoutTime,
                'updated_at' => $checkoutTime,
            ]);
        }

        // 6. NẠP DỮ LIỆU XUẤT KHO CHO TẤT CẢ CÁC VẬT TƯ TIÊU HAO KHÁC
        $this->command->info('-> Đang phân bổ lượt xuất kho mẫu cho các vật tư tiêu hao khác...');

        $allConsumables = Consumable::where('id', '!=', 2)->get();
        $sampleUsers = [$admin, $userAn, $userBich, $userNam, $userTri, $userHa, $userBao];

        foreach ($allConsumables as $cItem) {
            $existingCheckouts = DB::table('consumables_users')->where('consumable_id', $cItem->id)->count();
            if ($existingCheckouts === 0) {
                // Tạo ngẫu nhiên 2 - 4 lượt xuất kho cho mỗi vật tư
                $numCheckouts = rand(2, 4);
                for ($j = 0; $j < $numCheckouts; $j++) {
                    $assignedUser = $sampleUsers[$j % count($sampleUsers)];
                    $daysAgo = rand(1, 30);
                    $cTime = now()->subDays($daysAgo);
                    $note = "Cấp phát vật tư cho nhân sự [{$assignedUser->display_name}] phục vụ công tác kỹ thuật - Lần xuất kho #" . ($j + 1);

                    DB::table('consumables_users')->insert([
                        'consumable_id' => $cItem->id,
                        'assigned_to' => $assignedUser->id,
                        'created_by' => $admin->id,
                        'note' => $note,
                        'created_at' => $cTime,
                        'updated_at' => $cTime,
                    ]);

                    Actionlog::create([
                        'item_type' => Consumable::class,
                        'item_id' => $cItem->id,
                        'action_type' => 'checkout',
                        'target_type' => User::class,
                        'target_id' => $assignedUser->id,
                        'created_by' => $admin->id,
                        'note' => $note,
                        'created_at' => $cTime,
                        'updated_at' => $cTime,
                    ]);
                }
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $totalConsumables = Consumable::count();
        $totalCheckouts = DB::table('consumables_users')->count();

        $this->command->info("-> HOÀN TẤT THÀNH CÔNG! ĐÃ CÓ {$totalConsumables} VẬT TƯ TIÊU HAO VÀ {$totalCheckouts} LƯỢT XUẤT KHO BÀN GIAO THỰC TẾ.");
        $this->command->info('====================================================');
    }
}
