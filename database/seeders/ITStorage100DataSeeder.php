<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Depreciation;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ITStorage100DataSeeder extends Seeder
{
    /**
     * Run the database seeds for 100 IT Storage & Warehouse Assets.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->command->info('====================================================');
        $this->command->info(' BẮT ĐẦU KHỞI TẠO 100 DỮ LIỆU MẪU KHO LƯU TRỮ CNTT');
        $this->command->info('====================================================');

        // 1. CÔNG TY SỞ HỮU (COMPANIES)
        $company = Company::firstOrCreate(['name' => 'FPT Software - IT Enterprise Corp']);
        $companyViettel = Company::firstOrCreate(['name' => 'Viettel IDC Cloud Infrastructure']);

        // 2. NHÀ CUNG CẤP THIẾT BỊ CNTT (SUPPLIERS)
        $suppliers = [
            'FPT IS Enterprise' => Supplier::firstOrCreate(['name' => 'FPT IS Enterprise Solutions', 'phone' => '02873000999']),
            'Dell Technologies VN' => Supplier::firstOrCreate(['name' => 'Dell Technologies Vietnam', 'phone' => '1800545455']),
            'Cisco Systems VN' => Supplier::firstOrCreate(['name' => 'Cisco Systems Vietnam Direct', 'phone' => '02439368888']),
            'Synology Authorized Dist' => Supplier::firstOrCreate(['name' => 'Synology Authorized Distributor VN', 'phone' => '02838383838']),
            'Phong Vũ Enterprise' => Supplier::firstOrCreate(['name' => 'Phong Vũ B2B Technology', 'phone' => '18006865']),
        ];

        // 3. NHÀ SẢN XUẤT (MANUFACTURERS)
        $manufacturers = [
            'Dell' => Manufacturer::firstOrCreate(['name' => 'Dell Inc.']),
            'Cisco' => Manufacturer::firstOrCreate(['name' => 'Cisco Systems']),
            'HPE' => Manufacturer::firstOrCreate(['name' => 'Hewlett Packard Enterprise (HPE)']),
            'Synology' => Manufacturer::firstOrCreate(['name' => 'Synology Inc.']),
            'Lenovo' => Manufacturer::firstOrCreate(['name' => 'Lenovo Group Ltd.']),
            'Apple' => Manufacturer::firstOrCreate(['name' => 'Apple Inc.']),
            'Fortinet' => Manufacturer::firstOrCreate(['name' => 'Fortinet Security']),
            'APC' => Manufacturer::firstOrCreate(['name' => 'APC by Schneider Electric']),
        ];

        // 4. DANH MỤC THIẾT BỊ LƯU KHO (CATEGORIES)
        $categories = [
            'Server' => Category::firstOrCreate(['name' => 'Máy chủ Rack & Blade (Enterprise Servers)', 'category_type' => 'asset']),
            'Networking' => Category::firstOrCreate(['name' => 'Thiết bị Mạng & Bảo mật (Switch/Router/Firewall)', 'category_type' => 'asset']),
            'StorageSAN' => Category::firstOrCreate(['name' => 'Hệ thống Lưu trữ SAN/NAS & Disk Arrays', 'category_type' => 'asset']),
            'Laptop' => Category::firstOrCreate(['name' => 'Máy tính xách tay Doanh nghiệp (Laptops)', 'category_type' => 'asset']),
            'Monitor' => Category::firstOrCreate(['name' => 'Màn hình chuyên dụng (Monitors & Displays)', 'category_type' => 'asset']),
            'Power' => Category::firstOrCreate(['name' => 'Bộ lưu điện & Nguồn trung tâm (UPS & PDU)', 'category_type' => 'asset']),
        ];

        // 5. THIẾT LẬP KHẤU HAO (DEPRECIATION)
        $deprecServer = Depreciation::firstOrCreate(['name' => 'Khấu hao Máy chủ & Mạng (36 tháng)', 'months' => 36]);
        $deprecLaptop = Depreciation::firstOrCreate(['name' => 'Khấu hao Laptop & Màn hình (24 tháng)', 'months' => 24]);

        // 6. CÁC PHÂN KHU KHO LƯU TRỮ CNTT (IT WAREHOUSES & STORAGE LOCATIONS)
        $this->command->info('-> Đang tạo các phân khu Kho Lưu trữ CNTT (IT Warehouses)...');
        $itWarehouses = [
            'wh_central' => Location::firstOrCreate([
                'name' => 'Kho Tổng CNTT - Trung tâm Đà Lạt (Central IT Warehouse)',
            ], [
                'address' => '01 Phù Đổng Thiên Vương, Phường 8',
                'city' => 'Đà Lạt',
                'state' => 'Lâm Đồng',
                'country' => 'VN',
                'zip' => '66000',
            ]),
            'wh_datacenter' => Location::firstOrCreate([
                'name' => 'Kho Thiết bị Máy chủ & Hạ tầng Data Center (DC Server Vault)',
            ], [
                'address' => 'Khu Công Nghệ Cao TP.HCM, Quận 9',
                'city' => 'TP. Hồ Chí Minh',
                'state' => 'Hồ Chí Minh',
                'country' => 'VN',
                'zip' => '70000',
            ]),
            'wh_spare_parts' => Location::firstOrCreate([
                'name' => 'Kho Linh kiện Dự phòng & Ổ cứng SAN/NAS (Spare Parts Storage)',
            ], [
                'address' => 'Tầng hầm B2, Tòa nhà Landmark 72',
                'city' => 'Hà Nội',
                'state' => 'Hà Nội',
                'country' => 'VN',
                'zip' => '10000',
            ]),
            'wh_staging' => Location::firstOrCreate([
                'name' => 'Khu Kiểm định & Đóng gói Thiết bị Mới (IT Staging & Testing Bay)',
            ], [
                'address' => 'Khu Công Nghệ Cao Đà Lạt, Lâm Đồng',
                'city' => 'Đà Lạt',
                'state' => 'Lâm Đồng',
                'country' => 'VN',
                'zip' => '66000',
            ]),
            'wh_maintenance' => Location::firstOrCreate([
                'name' => 'Kho Thiết bị Chờ Bảo hành & Thanh lý (RMA & Quarantine Depo)',
            ], [
                'address' => 'Chi nhánh Đà Nẵng, 36 Bạch Đằng',
                'city' => 'Đà Nẵng',
                'state' => 'Đà Nẵng',
                'country' => 'VN',
                'zip' => '55000',
            ]),
        ];

        // 7. TRẠNG THÁI TÀI SẢN (STATUS LABELS)
        $stReady = Statuslabel::firstOrCreate(
            ['name' => 'Lưu kho - Sẵn sàng Cấp phát (Ready to Deploy)'],
            ['deployable' => 1, 'pending' => 0, 'archived' => 0, 'notes' => 'Thiết bị đã kiểm định, sẵn sàng xuất kho']
        );
        $stPending = Statuslabel::firstOrCreate(
            ['name' => 'Lưu kho - Chờ Kiểm tra Kỹ thuật (Pending Inspection)'],
            ['deployable' => 0, 'pending' => 1, 'archived' => 0, 'notes' => 'Vừa nhập kho, đang chờ QA kiểm tra thông số']
        );
        $stMaintenance = Statuslabel::firstOrCreate(
            ['name' => 'Kho Bảo hành - Đang Sửa chữa (Under Maintenance)'],
            ['deployable' => 0, 'pending' => 0, 'archived' => 0, 'notes' => 'Thiết bị lỗi chờ linh kiện thay thế tại kho']
        );
        $stDeployed = Statuslabel::firstOrCreate(
            ['name' => 'Đang Vận hành (Deployed in Operation)'],
            ['deployable' => 1, 'pending' => 0, 'archived' => 0, 'notes' => 'Đã xuất kho lắp đặt cho Data Center / Nhân sự']
        );
        $stArchived = Statuslabel::firstOrCreate(
            ['name' => 'Lưu kho - Chờ Thanh lý (Decommissioned/Scrap)'],
            ['deployable' => 0, 'pending' => 0, 'archived' => 1, 'notes' => 'Hết hạn sử dụng EOL, lưu kho chờ bán đấu giá']
        );

        // 8. CÁC MODEL THIẾT BỊ CNTT TIÊU BIỂU (ASSET MODELS)
        $this->command->info('-> Đang khởi tạo các Model thiết bị CNTT doanh nghiệp...');
        $models = [
            'srv_r750' => AssetModel::firstOrCreate([
                'name' => 'Dell PowerEdge R750xs 2U Rack Server',
            ], [
                'model_number' => 'PE-R750xs-Xeon5318Y',
                'category_id' => $categories['Server']->id,
                'manufacturer_id' => $manufacturers['Dell']->id,
                'depreciation_id' => $deprecServer->id,
                'eol' => 60,
            ]),
            'srv_dl380' => AssetModel::firstOrCreate([
                'name' => 'HPE ProLiant DL380 Gen10 Plus Server',
            ], [
                'model_number' => 'P56961-B21',
                'category_id' => $categories['Server']->id,
                'manufacturer_id' => $manufacturers['HPE']->id,
                'depreciation_id' => $deprecServer->id,
                'eol' => 60,
            ]),
            'sw_c9300' => AssetModel::firstOrCreate([
                'name' => 'Cisco Catalyst 9300 48-Port PoE+ Switch',
            ], [
                'model_number' => 'C9300-48P-A',
                'category_id' => $categories['Networking']->id,
                'manufacturer_id' => $manufacturers['Cisco']->id,
                'depreciation_id' => $deprecServer->id,
                'eol' => 72,
            ]),
            'fw_fg100f' => AssetModel::firstOrCreate([
                'name' => 'Fortinet FortiGate 100F Enterprise Firewall',
            ], [
                'model_number' => 'FG-100F-BDL',
                'category_id' => $categories['Networking']->id,
                'manufacturer_id' => $manufacturers['Fortinet']->id,
                'depreciation_id' => $deprecServer->id,
                'eol' => 60,
            ]),
            'nas_sa3400' => AssetModel::firstOrCreate([
                'name' => 'Synology Enterprise NAS SA3400D (Dual-Controller)',
            ], [
                'model_number' => 'SA3400D-12BAY',
                'category_id' => $categories['StorageSAN']->id,
                'manufacturer_id' => $manufacturers['Synology']->id,
                'depreciation_id' => $deprecServer->id,
                'eol' => 60,
            ]),
            'ups_apc3000' => AssetModel::firstOrCreate([
                'name' => 'APC Smart-UPS On-Line 3000VA 230V Rackmount',
            ], [
                'model_number' => 'SRT3000XLI',
                'category_id' => $categories['Power']->id,
                'manufacturer_id' => $manufacturers['APC']->id,
                'depreciation_id' => $deprecServer->id,
                'eol' => 48,
            ]),
            'nb_thinkpad' => AssetModel::firstOrCreate([
                'name' => 'Lenovo ThinkPad X1 Carbon Gen 11 (i7/32GB/1TB)',
            ], [
                'model_number' => '21HM0049VN',
                'category_id' => $categories['Laptop']->id,
                'manufacturer_id' => $manufacturers['Lenovo']->id,
                'depreciation_id' => $deprecLaptop->id,
                'eol' => 36,
            ]),
            'nb_macbook' => AssetModel::firstOrCreate([
                'name' => 'Apple MacBook Pro 16-inch M3 Pro (36GB/512GB)',
            ], [
                'model_number' => 'MRW13SA/A',
                'category_id' => $categories['Laptop']->id,
                'manufacturer_id' => $manufacturers['Apple']->id,
                'depreciation_id' => $deprecLaptop->id,
                'eol' => 36,
            ]),
            'nb_dellxps' => AssetModel::firstOrCreate([
                'name' => 'Dell XPS 15 9530 (i7-13700H/32GB/RTX4050)',
            ], [
                'model_number' => 'XPS-9530-OLED',
                'category_id' => $categories['Laptop']->id,
                'manufacturer_id' => $manufacturers['Dell']->id,
                'depreciation_id' => $deprecLaptop->id,
                'eol' => 36,
            ]),
            'mon_dellu27' => AssetModel::firstOrCreate([
                'name' => 'Dell UltraSharp 27 4K USB-C Hub Monitor U2723QE',
            ], [
                'model_number' => 'U2723QE-IPS-BLACK',
                'category_id' => $categories['Monitor']->id,
                'manufacturer_id' => $manufacturers['Dell']->id,
                'depreciation_id' => $deprecLaptop->id,
                'eol' => 36,
            ]),
        ];

        // Lấy admin user để gán lịch sử
        $firstUser = User::first();

        // 9. SINH ĐỦ 100 TÀI SẢN KHO LƯU TRỮ CNTT (IT STORAGE ASSETS)
        $this->command->info('-> Đang tiến hành sinh 100 tài sản thiết bị kho lưu trữ CNTT...');

        $assetTemplates = [
            // [Tên định dạng, Model Key, Giá, Vị trí kho chính, Tỷ lệ trạng thái, Note vị trí kho]
            ['Máy chủ Rack Dell PowerEdge R750xs #', 'srv_r750', 165000000, 'wh_datacenter', 'Kệ Rack DataCenter #DC-01, Slot U'],
            ['Máy chủ HPE ProLiant DL380 Gen10+ #', 'srv_dl380', 178000000, 'wh_datacenter', 'Kệ Rack DataCenter #DC-02, Slot U'],
            ['Core Switch Cisco Catalyst 9300 48P #', 'sw_c9300', 88000000, 'wh_central', 'Tủ mạng Cabinet A, Tầng U'],
            ['Tường lửa Doanh nghiệp FortiGate 100F #', 'fw_fg100f', 95000000, 'wh_central', 'Tủ mạng Security Rack Sec-01, Slot U'],
            ['Hệ thống Lưu trữ Synology NAS SA3400D #', 'nas_sa3400', 210000000, 'wh_spare_parts', 'Kệ Lưu trữ SAN/NAS Vault, Ngăn B'],
            ['Bộ Lưu điện APC Smart-UPS RT 3000VA #', 'ups_apc3000', 42000000, 'wh_central', 'Khu Nguồn UPS Backup, Kệ PDU-'],
            ['Laptop Doanh nghiệp ThinkPad X1 Carbon Gen 11 #', 'nb_thinkpad', 42500000, 'wh_staging', 'Kệ Thiết bị Laptop A3, Thùng Lưu kho #'],
            ['Laptop Apple MacBook Pro 16 M3 Pro #', 'nb_macbook', 58900000, 'wh_staging', 'Kệ Thiết bị Cao cấp B1, Thùng Lưu kho #'],
            ['Laptop Đồ họa & Dev Dell XPS 15 9530 #', 'nb_dellxps', 51200000, 'wh_central', 'Kệ Thiết bị Laptop A2, Hộp Số #'],
            ['Màn hình Đồ họa Dell UltraSharp 27 4K #', 'mon_dellu27', 13800000, 'wh_central', 'Kệ Màn hình & Phụ kiện M-01, Tầng '],
        ];

        $createdCount = 0;

        for ($i = 1; $i <= 100; $i++) {
            $templateIndex = ($i - 1) % count($assetTemplates);
            $template = $assetTemplates[$templateIndex];

            $modelKey = $template[1];
            $baseCost = $template[2];
            $warehouseKey = $template[3];
            $noteLocationPrefix = $template[4];

            $tagNumber = str_pad($i, 4, '0', STR_PAD_LEFT);
            $assetTag = "IT-WH-{$tagNumber}";

            // Quyết định trạng thái thiết bị trong kho
            // 60% Sẵn sàng cấp phát trong kho, 15% Chờ kiểm định, 10% Đang chạy, 10% Bảo hành, 5% Thanh lý
            $rand = ($i % 20);
            if ($rand <= 11) {
                $status = $stReady;       // Trong kho - sẵn sàng
                $assignedTo = null;
                $assignedType = null;
            } elseif ($rand <= 14) {
                $status = $stPending;     // Vừa về kho - chờ nghiệm thu
                $assignedTo = null;
                $assignedType = null;
            } elseif ($rand <= 16) {
                $status = $stDeployed;    // Đã bàn giao lắp đặt
                $assignedTo = $firstUser ? $firstUser->id : null;
                $assignedType = $firstUser ? User::class : null;
            } elseif ($rand <= 18) {
                $status = $stMaintenance; // Kho bảo hành
                $assignedTo = null;
                $assignedType = null;
            } else {
                $status = $stArchived;    // Kho thanh lý
                $assignedTo = null;
                $assignedType = null;
            }

            $currentLocation = $itWarehouses[$warehouseKey];
            $rtdLocation = $itWarehouses['wh_central']; // Vị trí kho gốc mặc định khi thu hồi về

            $serialPrefix = match ($modelKey) {
                'srv_r750' => 'DELL-R750-',
                'srv_dl380' => 'HPE-DL380-',
                'sw_c9300' => 'CISCO-C93-',
                'fw_fg100f' => 'FTNT-100F-',
                'nas_sa3400' => 'SYN-SA34-',
                'ups_apc3000' => 'APC-3000-',
                'nb_thinkpad' => 'LNV-X1C11-',
                'nb_macbook' => 'APL-MBP16-',
                'nb_dellxps' => 'DELL-XPS15-',
                default => 'DELL-U274K-',
            };

            $serial = $serialPrefix . strtoupper(substr(md5("SERIAL-{$i}-ITWH"), 0, 8));
            $assetName = $template[0] . str_pad($i, 3, '0', STR_PAD_LEFT);
            $purchaseDate = date('Y-m-d', strtotime("-".($i * 3)." days"));
            $orderNumber = "PO-2026-ITWH-" . str_pad((int)ceil($i / 5), 3, '0', STR_PAD_LEFT);
            $storageNote = "Vị trí lưu kho: " . $noteLocationPrefix . ($i % 12 + 1) . " (Đã dán tem kiểm kê QR Code)";

            Asset::firstOrCreate(
                ['asset_tag' => $assetTag],
                [
                    'name' => $assetName,
                    'model_id' => $models[$modelKey]->id,
                    'status_id' => $status->id,
                    'serial' => $serial,
                    'company_id' => ($i % 3 === 0) ? $companyViettel->id : $company->id,
                    'location_id' => $currentLocation->id,
                    'rtd_location_id' => $rtdLocation->id,
                    'supplier_id' => ($i % 2 === 0) ? $suppliers['Dell Technologies VN']->id : $suppliers['FPT IS Enterprise']->id,
                    'purchase_date' => $purchaseDate,
                    'purchase_cost' => $baseCost,
                    'order_number' => $orderNumber,
                    'notes' => $storageNote,
                    'assigned_to' => $assignedTo,
                    'assigned_type' => $assignedType,
                    'requestable' => ($status->id === $stReady->id) ? 1 : 0,
                    'archived' => ($status->id === $stArchived->id) ? 1 : 0,
                    'warranty_months' => 36,
                ]
            );

            $createdCount++;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info("-> HOÀN TẤT THÀNH CÔNG! ĐÃ KHỞI TẠO {$createdCount} TÀI SẢN KHO LƯU TRỮ CNTT (MÃ: IT-WH-0001 ĐẾN IT-WH-0100).");
        $this->command->info('====================================================');
    }
}
