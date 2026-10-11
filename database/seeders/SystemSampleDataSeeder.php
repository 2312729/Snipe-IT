<?php

namespace Database\Seeders;

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\Department;
use App\Models\Depreciation;
use App\Models\Group;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SystemSampleDataSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // ==========================================
        // 1. CÔNG TY (COMPANIES >= 10)
        // ==========================================
        $this->command->info('1. Khởi tạo 10 Công ty (Companies)...');
        $companiesData = [
            ['name' => 'FPT Software Đà Lạt'],
            ['name' => 'Viettel Telecom TP.HCM'],
            ['name' => 'VNG Tech Hà Nội'],
            ['name' => 'VNPT Technology'],
            ['name' => 'MoMo M-Service'],
            ['name' => 'Shopee Việt Nam'],
            ['name' => 'TMA Solutions'],
            ['name' => 'NashTech Vietnam'],
            ['name' => 'Tiki Corporation'],
            ['name' => 'VNPAY Vietnam'],
        ];
        $companies = [];
        foreach ($companiesData as $data) {
            $companies[$data['name']] = Company::firstOrCreate(['name' => $data['name']]);
        }
        $companyFpt = $companies['FPT Software Đà Lạt'];
        $companyViettel = $companies['Viettel Telecom TP.HCM'];
        $companyVng = $companies['VNG Tech Hà Nội'];

        // ==========================================
        // 2. ĐỊA ĐIỂM (LOCATIONS >= 10)
        // ==========================================
        $this->command->info('2. Khởi tạo 10 Địa điểm (Locations)...');
        $locationsData = [
            'locDaLat' => ['name' => 'Trụ sở chính - VP Đà Lạt', 'address' => '01 Phù Đổng Thiên Vương', 'city' => 'Đà Lạt', 'state' => 'Lâm Đồng', 'country' => 'VN', 'zip' => '66000'],
            'locHcm' => ['name' => 'Chi nhánh Quận 1 - TP. Hồ Chí Minh', 'address' => '123 Nguyễn Thị Minh Khai', 'city' => 'Hồ Chí Minh', 'state' => 'Hồ Chí Minh', 'country' => 'VN', 'zip' => '70000'],
            'locHanoi' => ['name' => 'Trung tâm R&D - Cầu Giấy, Hà Nội', 'address' => 'Keangnam Landmark 72', 'city' => 'Hà Nội', 'state' => 'Hà Nội', 'country' => 'VN', 'zip' => '10000'],
            'locWarehouse' => ['name' => 'Kho thiết bị trung tâm - Đà Lạt', 'address' => 'Khu Công Nghệ Cao Đà Lạt', 'city' => 'Đà Lạt', 'state' => 'Lâm Đồng', 'country' => 'VN', 'zip' => '66000'],
            'locDaNang' => ['name' => 'Chi nhánh Đà Nẵng - Hải Châu', 'address' => '36 Bạch Đằng', 'city' => 'Đà Nẵng', 'state' => 'Đà Nẵng', 'country' => 'VN', 'zip' => '55000'],
            'locCanTho' => ['name' => 'Văn phòng Cần Thơ - Ninh Kiều', 'address' => '12 Đại lộ Hòa Bình', 'city' => 'Cần Thơ', 'state' => 'Cần Thơ', 'country' => 'VN', 'zip' => '90000'],
            'locHaiPhong' => ['name' => 'Văn phòng Hải Phòng - Lê Chân', 'address' => '45 Lạch Tray', 'city' => 'Hải Phòng', 'state' => 'Hải Phòng', 'country' => 'VN', 'zip' => '18000'],
            'locHue' => ['name' => 'Trung tâm Đổi mới Sáng tạo Huế', 'address' => '20 Lê Lợi', 'city' => 'Huế', 'state' => 'Thừa Thiên Huế', 'country' => 'VN', 'zip' => '53000'],
            'locBinhDuong' => ['name' => 'Khu Kỹ thuật Bình Dương - VSIP 1', 'address' => 'Đại lộ Hữu Nghị, VSIP 1', 'city' => 'Thuận An', 'state' => 'Bình Dương', 'country' => 'VN', 'zip' => '82000'],
            'locHcmWarehouse' => ['name' => 'Kho hậu cần phía Nam - Thủ Đức', 'address' => 'Khu Công nghệ cao TP.HCM', 'city' => 'Thủ Đức', 'state' => 'Hồ Chí Minh', 'country' => 'VN', 'zip' => '71000'],
        ];
        $locations = [];
        foreach ($locationsData as $key => $data) {
            $locations[$key] = Location::firstOrCreate(['name' => $data['name']], $data);
        }
        $locDaLat = $locations['locDaLat'];
        $locHcm = $locations['locHcm'];
        $locHanoi = $locations['locHanoi'];
        $locWarehouse = $locations['locWarehouse'];
        $locDaNang = $locations['locDaNang'];

        // ==========================================
        // 3. PHÒNG BAN (DEPARTMENTS >= 10)
        // ==========================================
        $this->command->info('3. Khởi tạo 10 Phòng ban (Departments)...');
        $departmentsData = [
            'depQa' => ['name' => 'Phòng Đảm bảo Chất lượng (QA & Testing)', 'company_id' => $companyFpt->id, 'location_id' => $locDaLat->id],
            'depDev' => ['name' => 'Phòng Phát triển Phần mềm (Software Engineering)', 'company_id' => $companyFpt->id, 'location_id' => $locDaLat->id],
            'depIt' => ['name' => 'Phòng IT Vận hành & Hạ tầng (IT Infrastructure)', 'company_id' => $companyViettel->id, 'location_id' => $locHcm->id],
            'depFinance' => ['name' => 'Phòng Tài chính - Kế toán (Finance)', 'company_id' => $companyFpt->id, 'location_id' => $locDaLat->id],
            'depHr' => ['name' => 'Phòng Nhân sự & Đào tạo (HR)', 'company_id' => $companyVng->id, 'location_id' => $locHanoi->id],
            'depSec' => ['name' => 'Phòng An toàn Thông tin (Cyber Security)', 'company_id' => $companyViettel->id, 'location_id' => $locHcm->id],
            'depRd' => ['name' => 'Viện Nghiên cứu Trí tuệ Nhân tạo (AI R&D)', 'company_id' => $companyVng->id, 'location_id' => $locHanoi->id],
            'depMarketing' => ['name' => 'Phòng Truyền thông & Thương hiệu (Marketing)', 'company_id' => $companyFpt->id, 'location_id' => $locDaLat->id],
            'depSales' => ['name' => 'Phòng Quản lý Dự án & Khách hàng (Sales & PM)', 'company_id' => $companyFpt->id, 'location_id' => $locDaNang->id],
            'depBod' => ['name' => 'Ban Giám đốc & Quản trị Chiến lược (BOD)', 'company_id' => $companyFpt->id, 'location_id' => $locDaLat->id],
        ];
        $departments = [];
        foreach ($departmentsData as $key => $data) {
            $departments[$key] = Department::firstOrCreate(['name' => $data['name']], $data);
        }
        $depQa = $departments['depQa'];
        $depDev = $departments['depDev'];
        $depIt = $departments['depIt'];
        $depFinance = $departments['depFinance'];
        $depHr = $departments['depHr'];

        // ==========================================
        // 4. NHÓM QUYỀN (GROUPS / RBAC >= 10)
        // ==========================================
        $this->command->info('4. Khởi tạo 10 Nhóm quyền (Groups/RBAC)...');
        $groupsData = [
            ['name' => 'Superadmin Organization', 'permissions' => '{"superuser":"1","admin":"1"}'],
            ['name' => 'Asset & Hardware Managers', 'permissions' => '{"admin":"1","assets.checkout":"1","assets.checkin":"1","assets.view":"1","assets.create":"1","assets.edit":"1","assets.delete":"1"}'],
            ['name' => 'Standard Employees', 'permissions' => '{"self.two_factor":"1"}'],
            ['name' => 'QA Engineers', 'permissions' => '{"assets.view":"1","licenses.view":"1","reports.view":"1"}'],
            ['name' => 'DevOps & Infrastructure Leads', 'permissions' => '{"assets.view":"1","assets.edit":"1","components.view":"1","components.edit":"1"}'],
            ['name' => 'Department Leads', 'permissions' => '{"assets.view":"1","users.view":"1","reports.view":"1"}'],
            ['name' => 'Software Engineers', 'permissions' => '{"licenses.view":"1","assets.view":"1"}'],
            ['name' => 'Finance & Audit Team', 'permissions' => '{"reports.view":"1","assets.view":"1","depreciations.view":"1"}'],
            ['name' => 'HR Specialists', 'permissions' => '{"users.view":"1","users.create":"1","users.edit":"1"}'],
            ['name' => 'Procurement & Warehouse Staff', 'permissions' => '{"assets.view":"1","accessories.view":"1","consumables.view":"1"}'],
        ];
        $groups = [];
        foreach ($groupsData as $g) {
            $groups[$g['name']] = Group::firstOrCreate(['name' => $g['name']], ['permissions' => $g['permissions']]);
        }
        $groupSuperadmin = $groups['Superadmin Organization'];
        $groupAssetManager = $groups['Asset & Hardware Managers'];
        $groupEmployee = $groups['Standard Employees'];

        // Cập nhật SuperAdmin (ID = 1, Phạm Nguyễn Ngọc Phước)
        $adminUser = User::find(1);
        if ($adminUser) {
            $adminUser->legacy_company_id = $companyFpt->id;
            $adminUser->department_id = $depQa->id;
            $adminUser->location_id = $locDaLat->id;
            $adminUser->permissions = '{"superuser":"1","admin":"1"}';
            $adminUser->save();
            $adminUser->groups()->syncWithoutDetaching([$groupSuperadmin->id]);
            $adminUser->companies()->syncWithoutDetaching([$companyFpt->id]);
        }

        // ==========================================
        // 5. NGƯỜI DÙNG (USERS >= 10, TỔNG 12 USERS)
        // ==========================================
        $this->command->info('5. Khởi tạo 12 Nhân sự mẫu...');
        $samplePassword = Hash::make('Password123@');
        $usersData = [
            'userAn' => ['first_name' => 'An', 'last_name' => 'Nguyễn Văn', 'username' => 'nguyen.van.an', 'email' => 'an.nguyen@fpt-software.com', 'jobtitle' => 'Senior Software Engineer', 'company_id' => $companyFpt->id, 'department_id' => $depDev->id, 'location_id' => $locDaLat->id, 'group' => $groupEmployee],
            'userBich' => ['first_name' => 'Bích', 'last_name' => 'Trần Thị', 'username' => 'tran.thi.bich', 'email' => 'bich.tran@fpt-software.com', 'jobtitle' => 'QA Lead Engineer', 'company_id' => $companyFpt->id, 'department_id' => $depQa->id, 'location_id' => $locDaLat->id, 'group' => $groups['QA Engineers']],
            'userNam' => ['first_name' => 'Nam', 'last_name' => 'Lê Hoàng', 'username' => 'le.hoang.nam', 'email' => 'nam.le@viettel.vn', 'jobtitle' => 'DevOps & IT Support Specialist', 'company_id' => $companyViettel->id, 'department_id' => $depIt->id, 'location_id' => $locHcm->id, 'group' => $groupAssetManager],
            'userTri' => ['first_name' => 'Trí', 'last_name' => 'Phạm Minh', 'username' => 'pham.minh.tri', 'email' => 'tri.pham@fpt-software.com', 'jobtitle' => 'Kế toán trưởng (Senior Accountant)', 'company_id' => $companyFpt->id, 'department_id' => $depFinance->id, 'location_id' => $locDaLat->id, 'group' => $groups['Finance & Audit Team']],
            'userHa' => ['first_name' => 'Hà', 'last_name' => 'Đoàn Thanh', 'username' => 'doan.thanh.ha', 'email' => 'ha.doan@vng.com.vn', 'jobtitle' => 'HR Manager', 'company_id' => $companyVng->id, 'department_id' => $depHr->id, 'location_id' => $locHanoi->id, 'group' => $groups['HR Specialists']],
            'userBao' => ['first_name' => 'Bảo', 'last_name' => 'Vũ Quốc', 'username' => 'vu.quoc.bao', 'email' => 'bao.vu@viettel.vn', 'jobtitle' => 'Security Analyst Lead', 'company_id' => $companyViettel->id, 'department_id' => $departments['depSec']->id, 'location_id' => $locHcm->id, 'group' => $groups['DevOps & Infrastructure Leads']],
            'userYen' => ['first_name' => 'Yến', 'last_name' => 'Đỗ Hải', 'username' => 'do.hai.yen', 'email' => 'yen.do@vng.com.vn', 'jobtitle' => 'AI Research Scientist', 'company_id' => $companyVng->id, 'department_id' => $departments['depRd']->id, 'location_id' => $locHanoi->id, 'group' => $groupEmployee],
            'userKhiem' => ['first_name' => 'Khiêm', 'last_name' => 'Hoàng Gia', 'username' => 'hoang.gia.khiem', 'email' => 'khiem.hoang@fpt-software.com', 'jobtitle' => 'Marketing Specialist', 'company_id' => $companyFpt->id, 'department_id' => $departments['depMarketing']->id, 'location_id' => $locDaLat->id, 'group' => $groupEmployee],
            'userTrang' => ['first_name' => 'Trang', 'last_name' => 'Mai Thu', 'username' => 'mai.thu.trang', 'email' => 'trang.mai@fpt-software.com', 'jobtitle' => 'Project Manager', 'company_id' => $companyFpt->id, 'department_id' => $departments['depSales']->id, 'location_id' => $locDaNang->id, 'group' => $groups['Department Leads']],
            'userLong' => ['first_name' => 'Long', 'last_name' => 'Phan Nhật', 'username' => 'phan.nhat.long', 'email' => 'long.phan@fpt-software.com', 'jobtitle' => 'Software Engineer (Backend)', 'company_id' => $companyFpt->id, 'department_id' => $depDev->id, 'location_id' => $locDaLat->id, 'group' => $groups['Software Engineers']],
            'userQuyet' => ['first_name' => 'Quyết', 'last_name' => 'Đinh Văn', 'username' => 'dinh.van.quyet', 'email' => 'quyet.dinh@viettel.vn', 'jobtitle' => 'Warehouse Logistics Coordinator', 'company_id' => $companyViettel->id, 'department_id' => $depIt->id, 'location_id' => $locations['locHcmWarehouse']->id, 'group' => $groups['Procurement & Warehouse Staff']],
        ];
        $users = [];
        foreach ($usersData as $key => $u) {
            $user = User::firstOrCreate(
                ['username' => $u['username']],
                [
                    'first_name' => $u['first_name'],
                    'last_name' => $u['last_name'],
                    'email' => $u['email'],
                    'password' => $samplePassword,
                    'activated' => 1,
                    'jobtitle' => $u['jobtitle'],
                    'legacy_company_id' => $u['company_id'],
                    'department_id' => $u['department_id'],
                    'location_id' => $u['location_id'],
                ]
            );
            $user->groups()->syncWithoutDetaching([$u['group']->id]);
            $user->companies()->syncWithoutDetaching([$u['company_id']]);
            $users[$key] = $user;
        }
        $userAn = $users['userAn'];
        $userBich = $users['userBich'];
        $userNam = $users['userNam'];

        // ==========================================
        // 6. HÃNG SẢN XUẤT (MANUFACTURERS >= 10)
        // ==========================================
        $this->command->info('6. Khởi tạo 10 Hãng sản xuất (Manufacturers)...');
        $mfData = [
            'mfApple' => ['name' => 'Apple Inc.', 'url' => 'https://apple.com', 'support_url' => 'https://support.apple.com'],
            'mfDell' => ['name' => 'Dell Technologies', 'url' => 'https://dell.com', 'support_url' => 'https://dell.com/support'],
            'mfLenovo' => ['name' => 'Lenovo Group', 'url' => 'https://lenovo.com', 'support_url' => 'https://support.lenovo.com'],
            'mfHp' => ['name' => 'HP Inc.', 'url' => 'https://hp.com', 'support_url' => 'https://support.hp.com'],
            'mfMicrosoft' => ['name' => 'Microsoft Corporation', 'url' => 'https://microsoft.com', 'support_url' => 'https://support.microsoft.com'],
            'mfLogitech' => ['name' => 'Logitech', 'url' => 'https://logitech.com', 'support_url' => 'https://support.logi.com'],
            'mfKingston' => ['name' => 'Kingston Technology', 'url' => 'https://kingston.com', 'support_url' => 'https://kingston.com/support'],
            'mfSamsung' => ['name' => 'Samsung Electronics', 'url' => 'https://samsung.com', 'support_url' => 'https://samsung.com/support'],
            'mfCisco' => ['name' => 'Cisco Systems', 'url' => 'https://cisco.com', 'support_url' => 'https://cisco.com/support'],
            'mfAsus' => ['name' => 'ASUStek Computer Inc.', 'url' => 'https://asus.com', 'support_url' => 'https://asus.com/support'],
        ];
        $manufacturers = [];
        foreach ($mfData as $k => $d) {
            $manufacturers[$k] = Manufacturer::firstOrCreate(['name' => $d['name']], $d);
        }
        $mfApple = $manufacturers['mfApple'];
        $mfDell = $manufacturers['mfDell'];
        $mfLenovo = $manufacturers['mfLenovo'];
        $mfHp = $manufacturers['mfHp'];
        $mfMicrosoft = $manufacturers['mfMicrosoft'];
        $mfLogitech = $manufacturers['mfLogitech'];
        $mfKingston = $manufacturers['mfKingston'];
        $mfSamsung = $manufacturers['mfSamsung'];
        $mfCisco = $manufacturers['mfCisco'];

        // ==========================================
        // 7. NHÀ CUNG CẤP (SUPPLIERS >= 10)
        // ==========================================
        $this->command->info('7. Khởi tạo 10 Nhà cung cấp (Suppliers)...');
        $supData = [
            'supFpt' => ['name' => 'FPT Synnex Distribution', 'contact' => 'Trần Quang Huy', 'phone' => '024-7300-8888', 'email' => 'sales@synnexfpt.com.vn'],
            'supDigiworld' => ['name' => 'Digiworld Corporation (DGW)', 'contact' => 'Nguyễn Bích Thu', 'phone' => '028-3929-0059', 'email' => 'enterprise@digiworld.com.vn'],
            'supPhongVu' => ['name' => 'Công ty Cổ phần Thương mại Dịch vụ Phong Vũ', 'contact' => 'Phạm Anh Tuấn', 'phone' => '1800-6867', 'email' => 'b2b@phongvu.vn'],
            'supTheGioiDiDong' => ['name' => 'Công ty Cổ phần Thế Giới Di Động', 'contact' => 'Đỗ Minh Khang', 'phone' => '1800-1060', 'email' => 'corporate@thegioididong.com'],
            'supPetrosetco' => ['name' => 'Petrosetco PSD Distribution', 'contact' => 'Lê Thanh Bình', 'phone' => '028-3911-5566', 'email' => 'contact@psd.com.vn'],
            'supViettelStore' => ['name' => 'Hệ thống Bán lẻ Viettel Store', 'contact' => 'Hoàng Văn Dũng', 'phone' => '1800-8123', 'email' => 'b2b@viettelstore.vn'],
            'supElite' => ['name' => 'Công ty TNHH Phân Phối Công Nghệ Elite', 'contact' => 'Vũ Thu Phương', 'phone' => '028-3512-8888', 'email' => 'sales@elite-tech.com.vn'],
            'supCmc' => ['name' => 'CMC Distribution', 'contact' => 'Nguyễn Hoàng Long', 'phone' => '024-3795-8888', 'email' => 'info@cmcdistribution.vn'],
            'supHnc' => ['name' => 'Công ty Cổ phần Máy tính Hà Nội (Hanoicomputer)', 'contact' => 'Đinh Đức Mạnh', 'phone' => '1900-1903', 'email' => 'hnc@hacom.vn'],
            'supAnPhat' => ['name' => 'Công ty Cổ phần Thương mại Máy tính An Phát', 'contact' => 'Bùi Gia Khánh', 'phone' => '1900-0323', 'email' => 'kinhdoanh@anphatpc.com.vn'],
        ];
        $suppliers = [];
        foreach ($supData as $k => $d) {
            $suppliers[$k] = Supplier::firstOrCreate(['name' => $d['name']], $d);
        }
        $supFpt = $suppliers['supFpt'];
        $supDigiworld = $suppliers['supDigiworld'];
        $supPhongVu = $suppliers['supPhongVu'];
        $supTheGioiDiDong = $suppliers['supTheGioiDiDong'];
        $supElite = $suppliers['supElite'];
        $supCmc = $suppliers['supCmc'];
        $supHnc = $suppliers['supHnc'];

        // ==========================================
        // 8. THỂ LOẠI (CATEGORIES >= 10)
        // ==========================================
        $this->command->info('8. Khởi tạo 10 Thể loại (Categories)...');
        $catData = [
            'catLaptop' => ['name' => 'Laptops & Ultrabooks', 'category_type' => 'asset'],
            'catDesktop' => ['name' => 'Máy tính để bàn (Workstation/PC)', 'category_type' => 'asset'],
            'catMonitor' => ['name' => 'Màn hình chuyên đồ họa', 'category_type' => 'asset'],
            'catServer' => ['name' => 'Máy chủ trung tâm (Enterprise Servers)', 'category_type' => 'asset'],
            'catNetwork' => ['name' => 'Thiết bị mạng (Routers & Switches)', 'category_type' => 'asset'],
            'catMobile' => ['name' => 'Thiết bị di động (Tablets & Phones)', 'category_type' => 'asset'],
            'catLicense' => ['name' => 'Phần mềm bản quyền (Software Licenses)', 'category_type' => 'license'],
            'catAccessory' => ['name' => 'Phụ kiện ngoại vi (Computer Accessories)', 'category_type' => 'accessory'],
            'catConsumable' => ['name' => 'Vật tư tiêu hao (IT Consumables)', 'category_type' => 'consumable'],
            'catComponent' => ['name' => 'Linh kiện phần cứng (Hardware Components)', 'category_type' => 'component'],
        ];
        $categories = [];
        foreach ($catData as $k => $d) {
            $categories[$k] = Category::firstOrCreate(['name' => $d['name']], $d);
        }
        $catLaptop = $categories['catLaptop'];
        $catDesktop = $categories['catDesktop'];
        $catMonitor = $categories['catMonitor'];
        $catServer = $categories['catServer'];
        $catLicense = $categories['catLicense'];
        $catAccessory = $categories['catAccessory'];
        $catConsumable = $categories['catConsumable'];
        $catComponent = $categories['catComponent'];

        // ==========================================
        // 9. KHẤU HAO (DEPRECIATIONS >= 10)
        // ==========================================
        $this->command->info('9. Khởi tạo 10 Quy tắc khấu hao (Depreciations)...');
        $deprData = [
            'depr12m' => ['name' => 'Khấu hao nhanh thiết bị cầm tay 12 tháng', 'months' => 12, 'depreciation_type' => 'amount', 'depreciation_min' => 0],
            'depr24m' => ['name' => 'Khấu hao thiết bị ngoại vi 24 tháng', 'months' => 24, 'depreciation_type' => 'amount', 'depreciation_min' => 0],
            'depr36m' => ['name' => 'Khấu hao tiêu chuẩn Laptop/Workstation 36 tháng', 'months' => 36, 'depreciation_type' => 'amount', 'depreciation_min' => 1000000],
            'depr48m' => ['name' => 'Khấu hao thiết bị văn phòng 48 tháng', 'months' => 48, 'depreciation_type' => 'amount', 'depreciation_min' => 500000],
            'depr60m' => ['name' => 'Khấu hao máy chủ & hạ tầng mạng 60 tháng', 'months' => 60, 'depreciation_type' => 'amount', 'depreciation_min' => 5000000],
            'depr72m' => ['name' => 'Khấu hao trung tâm dữ liệu 72 tháng', 'months' => 72, 'depreciation_type' => 'amount', 'depreciation_min' => 10000000],
            'depr120m' => ['name' => 'Khấu hao hạ tầng kỹ thuật dài hạn 120 tháng', 'months' => 120, 'depreciation_type' => 'amount', 'depreciation_min' => 20000000],
            'depr10pct' => ['name' => 'Khấu hao đường thẳng 10% mỗi năm', 'months' => 120, 'depreciation_type' => 'percent', 'depreciation_min' => 10],
            'depr20pct' => ['name' => 'Khấu hao đường thẳng 20% mỗi năm', 'months' => 60, 'depreciation_type' => 'percent', 'depreciation_min' => 20],
            'depr25pct' => ['name' => 'Khấu hao đường thẳng 25% mỗi năm', 'months' => 48, 'depreciation_type' => 'percent', 'depreciation_min' => 25],
        ];
        $depreciations = [];
        foreach ($deprData as $k => $d) {
            $depreciations[$k] = Depreciation::firstOrCreate(['name' => $d['name']], $d);
        }
        $depr36m = $depreciations['depr36m'];
        $depr60m = $depreciations['depr60m'];

        // ==========================================
        // 10. NHÃN TRẠNG THÁI (STATUS LABELS >= 10)
        // ==========================================
        $this->command->info('10. Khởi tạo 10 Nhãn trạng thái (Status Labels)...');
        $statusData = [
            'stReady' => ['name' => 'Ready to Deploy (Sẵn sàng cấp phát)', 'deployable' => 1, 'pending' => 0, 'archived' => 0, 'notes' => 'Tài sản trong kho, đạt tiêu chuẩn cấp phát'],
            'stDeployed' => ['name' => 'Deployed (Đang cấp phát sử dụng)', 'deployable' => 1, 'pending' => 0, 'archived' => 0, 'notes' => 'Tài sản đã bàn giao cho nhân sự hoặc địa điểm'],
            'stMaintenance' => ['name' => 'In Maintenance (Đang bảo hành sửa chữa)', 'deployable' => 0, 'pending' => 0, 'archived' => 0, 'notes' => 'Tài sản đang tại trung tâm bảo hành'],
            'stArchived' => ['name' => 'Archived (Thanh lý / Lưu kho lịch sử)', 'deployable' => 0, 'pending' => 0, 'archived' => 1, 'notes' => 'Tài sản đã hết khấu hao hoặc thanh lý'],
            'stPending' => ['name' => 'Pending Inspection (Chờ kiểm tra kỹ thuật)', 'deployable' => 0, 'pending' => 1, 'archived' => 0, 'notes' => 'Mới nhập kho, đang nghiệm thu'],
            'stBroken' => ['name' => 'Broken (Hỏng hóc không thể phục hồi)', 'deployable' => 0, 'pending' => 0, 'archived' => 0, 'notes' => 'Tài sản hư hỏng chờ hội đồng xử lý'],
            'stStolen' => ['name' => 'Lost / Stolen (Thất lạc hoặc báo mất)', 'deployable' => 0, 'pending' => 0, 'archived' => 0, 'notes' => 'Đã lập biên bản sự vụ'],
            'stReserved' => ['name' => 'Reserved for Project (Đã đặt trước cho dự án)', 'deployable' => 0, 'pending' => 1, 'archived' => 0, 'notes' => 'Giữ chỗ cho dự án sắp khởi động'],
            'stEndOfLife' => ['name' => 'End of Life (Hết vòng đời công nghệ)', 'deployable' => 0, 'pending' => 0, 'archived' => 1, 'notes' => 'Hết hỗ trợ từ hãng sản xuất'],
            'stDonated' => ['name' => 'Donated (Thanh lý trao tặng từ thiện)', 'deployable' => 0, 'pending' => 0, 'archived' => 1, 'notes' => 'Trao tặng cơ sở giáo dục'],
        ];
        $statusLabels = [];
        foreach ($statusData as $k => $d) {
            $statusLabels[$k] = Statuslabel::firstOrCreate(['name' => $d['name']], $d);
        }
        $stReady = $statusLabels['stReady'];
        $stDeployed = $statusLabels['stDeployed'];
        $stMaintenance = $statusLabels['stMaintenance'];
        $stArchived = $statusLabels['stArchived'];

        // ==========================================
        // 11. DÒNG MODEL (ASSET MODELS >= 10, TỔNG 12 MODELS)
        // ==========================================
        $this->command->info('11. Khởi tạo 12 Dòng sản phẩm (Asset Models)...');
        $modelsData = [
            'mdlMacbook16' => ['name' => 'MacBook Pro 16 inch M3 Max (36GB/1TB)', 'model_number' => 'MUW63SA/A', 'manufacturer_id' => $mfApple->id, 'category_id' => $catLaptop->id, 'depreciation_id' => $depr36m->id],
            'mdlMacbook14' => ['name' => 'MacBook Pro 14 inch M3 Pro (18GB/512GB)', 'model_number' => 'MRX33SA/A', 'manufacturer_id' => $mfApple->id, 'category_id' => $catLaptop->id, 'depreciation_id' => $depr36m->id],
            'mdlDellXps' => ['name' => 'Dell XPS 15 9530 (i7-13700H/32GB/1TB/RTX 4050)', 'model_number' => 'XPS-9530-2023', 'manufacturer_id' => $mfDell->id, 'category_id' => $catLaptop->id, 'depreciation_id' => $depr36m->id],
            'mdlThinkpad' => ['name' => 'ThinkPad X1 Carbon Gen 11 (i7-1365U/32GB/1TB)', 'model_number' => '21HM0008VA', 'manufacturer_id' => $mfLenovo->id, 'category_id' => $catLaptop->id, 'depreciation_id' => $depr36m->id],
            'mdlHpElite' => ['name' => 'HP EliteBook 840 G9 (i7-1260P/16GB/512GB)', 'model_number' => '6F8E2PA', 'manufacturer_id' => $mfHp->id, 'category_id' => $catLaptop->id, 'depreciation_id' => $depr36m->id],
            'mdlDellPrecision' => ['name' => 'Dell Precision 3660 Tower Workstation (i9-13900/64GB/2TB)', 'model_number' => 'T3660-I9', 'manufacturer_id' => $mfDell->id, 'category_id' => $catDesktop->id, 'depreciation_id' => $depr36m->id],
            'mdlDellScreen' => ['name' => 'Dell UltraSharp 27 4K USB-C Hub Monitor', 'model_number' => 'U2723QE', 'manufacturer_id' => $mfDell->id, 'category_id' => $catMonitor->id, 'depreciation_id' => $depr36m->id],
            'mdlAsusProart' => ['name' => 'ASUS ProArt PA278CV 27 inch 2K WQHD', 'model_number' => 'PA278CV', 'manufacturer_id' => $manufacturers['mfAsus']->id, 'category_id' => $catMonitor->id, 'depreciation_id' => $depr36m->id],
            'mdlServerDell' => ['name' => 'Dell PowerEdge R750xs Rack Server 2U (2x Xeon Silver 4314)', 'model_number' => 'PER750XS-01', 'manufacturer_id' => $mfDell->id, 'category_id' => $catServer->id, 'depreciation_id' => $depr60m->id],
            'mdlServerHp' => ['name' => 'HPE ProLiant DL380 Gen10 Server 2U', 'model_number' => 'P20248-B21', 'manufacturer_id' => $mfHp->id, 'category_id' => $catServer->id, 'depreciation_id' => $depr60m->id],
            'mdlCiscoSwitch' => ['name' => 'Cisco Catalyst 9200L 48-port PoE+ Switch', 'model_number' => 'C9200L-48P-4G-E', 'manufacturer_id' => $mfCisco->id, 'category_id' => $categories['catNetwork']->id, 'depreciation_id' => $depr60m->id],
            'mdlIpadPro' => ['name' => 'Apple iPad Pro 12.9 inch M2 Wi-Fi 256GB', 'model_number' => 'MNXQ3ZA/A', 'manufacturer_id' => $mfApple->id, 'category_id' => $categories['catMobile']->id, 'depreciation_id' => $depreciations['depr24m']->id],
        ];
        $assetModels = [];
        foreach ($modelsData as $k => $d) {
            $assetModels[$k] = AssetModel::firstOrCreate(['name' => $d['name']], $d);
        }

        // ==========================================
        // 12. TÀI SẢN PHẦN CỨNG (ASSETS >= 10, TỔNG 15 ASSETS)
        // ==========================================
        $this->command->info('12. Khởi tạo 15 Tài sản Phần cứng (Assets)...');
        $assetsData = [
            [
                'asset_tag' => 'AST-2026-0001',
                'name' => 'MacBook Pro 16 M3 - Lead QA',
                'model_id' => $assetModels['mdlMacbook16']->id,
                'status_id' => $stDeployed->id,
                'serial' => 'C02G1234MD6P',
                'company_id' => $companyFpt->id,
                'location_id' => $locDaLat->id,
                'rtd_location_id' => $locWarehouse->id,
                'supplier_id' => $supFpt->id,
                'purchase_date' => '2026-01-10',
                'purchase_cost' => 78900000,
                'assigned_to' => 1,
                'assigned_type' => User::class,
            ],
            [
                'asset_tag' => 'AST-2026-0002',
                'name' => 'MacBook Pro 16 M3 - Stock #1',
                'model_id' => $assetModels['mdlMacbook16']->id,
                'status_id' => $stReady->id,
                'serial' => 'C02G5678MD6P',
                'company_id' => $companyFpt->id,
                'location_id' => $locWarehouse->id,
                'rtd_location_id' => $locWarehouse->id,
                'supplier_id' => $supFpt->id,
                'purchase_date' => '2026-01-10',
                'purchase_cost' => 78900000,
                'assigned_to' => null,
                'assigned_type' => null,
            ],
            [
                'asset_tag' => 'AST-2026-0003',
                'name' => 'Dell XPS 15 - Dev Nguyễn Văn An',
                'model_id' => $assetModels['mdlDellXps']->id,
                'status_id' => $stDeployed->id,
                'serial' => 'DELL-XPS-9901',
                'company_id' => $companyFpt->id,
                'location_id' => $locDaLat->id,
                'rtd_location_id' => $locWarehouse->id,
                'supplier_id' => $supPhongVu->id,
                'purchase_date' => '2026-01-15',
                'purchase_cost' => 54500000,
                'assigned_to' => $userAn->id,
                'assigned_type' => User::class,
            ],
            [
                'asset_tag' => 'AST-2026-0004',
                'name' => 'Dell XPS 15 - Stock #2',
                'model_id' => $assetModels['mdlDellXps']->id,
                'status_id' => $stReady->id,
                'serial' => 'DELL-XPS-9902',
                'company_id' => $companyFpt->id,
                'location_id' => $locWarehouse->id,
                'rtd_location_id' => $locWarehouse->id,
                'supplier_id' => $supPhongVu->id,
                'purchase_date' => '2026-01-15',
                'purchase_cost' => 54500000,
                'assigned_to' => null,
                'assigned_type' => null,
            ],
            [
                'asset_tag' => 'AST-2026-0005',
                'name' => 'ThinkPad X1 Carbon - QA Trần Thị Bích',
                'model_id' => $assetModels['mdlThinkpad']->id,
                'status_id' => $stDeployed->id,
                'serial' => 'LNV-X1C-3341',
                'company_id' => $companyFpt->id,
                'location_id' => $locDaLat->id,
                'rtd_location_id' => $locWarehouse->id,
                'supplier_id' => $supDigiworld->id,
                'purchase_date' => '2026-02-01',
                'purchase_cost' => 42000000,
                'assigned_to' => $userBich->id,
                'assigned_type' => User::class,
            ],
            [
                'asset_tag' => 'AST-2026-0006',
                'name' => 'Dell UltraSharp 27 4K - Lê Hoàng Nam',
                'model_id' => $assetModels['mdlDellScreen']->id,
                'status_id' => $stDeployed->id,
                'serial' => 'DELL-MN-8812',
                'company_id' => $companyViettel->id,
                'location_id' => $locHcm->id,
                'rtd_location_id' => $locHcm->id,
                'supplier_id' => $supPhongVu->id,
                'purchase_date' => '2026-02-10',
                'purchase_cost' => 13500000,
                'assigned_to' => $userNam->id,
                'assigned_type' => User::class,
            ],
            [
                'asset_tag' => 'AST-2026-0007',
                'name' => 'Server Core Database R750xs - Đà Lạt',
                'model_id' => $assetModels['mdlServerDell']->id,
                'status_id' => $stDeployed->id,
                'serial' => 'SRV-R750-001',
                'company_id' => $companyFpt->id,
                'location_id' => $locDaLat->id,
                'rtd_location_id' => $locDaLat->id,
                'supplier_id' => $supFpt->id,
                'purchase_date' => '2026-01-05',
                'purchase_cost' => 185000000,
                'assigned_to' => $locDaLat->id,
                'assigned_type' => Location::class,
            ],
            [
                'asset_tag' => 'AST-2026-0008',
                'name' => 'ThinkPad X1 Carbon - Đang thay bàn phím',
                'model_id' => $assetModels['mdlThinkpad']->id,
                'status_id' => $stMaintenance->id,
                'serial' => 'LNV-X1C-MAINT',
                'company_id' => $companyFpt->id,
                'location_id' => $locWarehouse->id,
                'rtd_location_id' => $locWarehouse->id,
                'supplier_id' => $supDigiworld->id,
                'purchase_date' => '2026-02-01',
                'purchase_cost' => 42000000,
                'assigned_to' => null,
                'assigned_type' => null,
            ],
            [
                'asset_tag' => 'AST-2026-0009',
                'name' => 'HP EliteBook 840 G9 - HR Đoàn Thanh Hà',
                'model_id' => $assetModels['mdlHpElite']->id,
                'status_id' => $stDeployed->id,
                'serial' => 'HP-EB-840-009',
                'company_id' => $companyVng->id,
                'location_id' => $locHanoi->id,
                'rtd_location_id' => $locHanoi->id,
                'supplier_id' => $supFpt->id,
                'purchase_date' => '2026-02-15',
                'purchase_cost' => 31500000,
                'assigned_to' => $users['userHa']->id,
                'assigned_type' => User::class,
            ],
            [
                'asset_tag' => 'AST-2026-0010',
                'name' => 'Dell Precision 3660 - AI R&D Đỗ Hải Yến',
                'model_id' => $assetModels['mdlDellPrecision']->id,
                'status_id' => $stDeployed->id,
                'serial' => 'DELL-T3660-010',
                'company_id' => $companyVng->id,
                'location_id' => $locHanoi->id,
                'rtd_location_id' => $locHanoi->id,
                'supplier_id' => $supPhongVu->id,
                'purchase_date' => '2026-02-20',
                'purchase_cost' => 68000000,
                'assigned_to' => $users['userYen']->id,
                'assigned_type' => User::class,
            ],
            [
                'asset_tag' => 'AST-2026-0011',
                'name' => 'Cisco Catalyst 9200L - Core Switch HCM',
                'model_id' => $assetModels['mdlCiscoSwitch']->id,
                'status_id' => $stDeployed->id,
                'serial' => 'CSC-9200L-011',
                'company_id' => $companyViettel->id,
                'location_id' => $locHcm->id,
                'rtd_location_id' => $locHcm->id,
                'supplier_id' => $supElite->id,
                'purchase_date' => '2026-01-20',
                'purchase_cost' => 72000000,
                'assigned_to' => $locHcm->id,
                'assigned_type' => Location::class,
            ],
            [
                'asset_tag' => 'AST-2026-0012',
                'name' => 'ASUS ProArt 27 2K - Security Vũ Quốc Bảo',
                'model_id' => $assetModels['mdlAsusProart']->id,
                'status_id' => $stDeployed->id,
                'serial' => 'ASUS-PA27-012',
                'company_id' => $companyViettel->id,
                'location_id' => $locHcm->id,
                'rtd_location_id' => $locHcm->id,
                'supplier_id' => $supHnc->id,
                'purchase_date' => '2026-02-12',
                'purchase_cost' => 11500000,
                'assigned_to' => $users['userBao']->id,
                'assigned_type' => User::class,
            ],
            [
                'asset_tag' => 'AST-2026-0013',
                'name' => 'MacBook Pro 14 M3 Pro - Stock #3',
                'model_id' => $assetModels['mdlMacbook14']->id,
                'status_id' => $stReady->id,
                'serial' => 'C02G9988MB14',
                'company_id' => $companyFpt->id,
                'location_id' => $locWarehouse->id,
                'rtd_location_id' => $locWarehouse->id,
                'supplier_id' => $supFpt->id,
                'purchase_date' => '2026-03-01',
                'purchase_cost' => 49900000,
                'assigned_to' => null,
                'assigned_type' => null,
            ],
            [
                'asset_tag' => 'AST-2026-0014',
                'name' => 'iPad Pro 12.9 M2 - PM Mai Thu Trang',
                'model_id' => $assetModels['mdlIpadPro']->id,
                'status_id' => $stDeployed->id,
                'serial' => 'DMP9988IPAD',
                'company_id' => $companyFpt->id,
                'location_id' => $locDaNang->id,
                'rtd_location_id' => $locDaNang->id,
                'supplier_id' => $supTheGioiDiDong->id,
                'purchase_date' => '2026-02-25',
                'purchase_cost' => 29500000,
                'assigned_to' => $users['userTrang']->id,
                'assigned_type' => User::class,
            ],
            [
                'asset_tag' => 'AST-2026-0015',
                'name' => 'Server HPE DL380 Gen10 - Backup DC Hà Nội',
                'model_id' => $assetModels['mdlServerHp']->id,
                'status_id' => $stDeployed->id,
                'serial' => 'HPE-DL380-015',
                'company_id' => $companyVng->id,
                'location_id' => $locHanoi->id,
                'rtd_location_id' => $locHanoi->id,
                'supplier_id' => $supCmc->id,
                'purchase_date' => '2026-01-08',
                'purchase_cost' => 165000000,
                'assigned_to' => $locHanoi->id,
                'assigned_type' => Location::class,
            ],
        ];

        foreach ($assetsData as $aData) {
            $asset = Asset::firstOrCreate(['asset_tag' => $aData['asset_tag']], $aData);
            if ($aData['assigned_to']) {
                $asset->assigned_to = $aData['assigned_to'];
                $asset->assigned_type = $aData['assigned_type'];
                $asset->checkout_counter = 1;
                $asset->last_checkout = now();
                $asset->save();

                Actionlog::firstOrCreate(
                    [
                        'item_type' => Asset::class,
                        'item_id' => $asset->id,
                        'action_type' => 'checkout',
                    ],
                    [
                        'target_type' => $aData['assigned_type'],
                        'target_id' => $aData['assigned_to'],
                        'created_by' => 1,
                        'note' => 'Bàn giao thiết bị mẫu',
                        'created_at' => now(),
                    ]
                );
            }
        }

        // ==========================================
        // 13. BẢN QUYỀN PHẦN MỀM (LICENSES >= 10)
        // ==========================================
        $this->command->info('13. Khởi tạo 10 Bản quyền Phần mềm (Licenses & Seats)...');
        $licensesData = [
            ['name' => 'Microsoft 365 E5 Enterprise', 'seats' => 50, 'serial' => 'M365-E5-XXXX-YYYY-ZZZZ-2026', 'cost' => 60000000, 'supplier_id' => $supFpt->id, 'mf_id' => $mfMicrosoft->id],
            ['name' => 'JetBrains All Products Pack', 'seats' => 20, 'serial' => 'JB-APP-DEVELOPER-PACK-2026', 'cost' => 35000000, 'supplier_id' => $supDigiworld->id, 'mf_id' => $mfMicrosoft->id],
            ['name' => 'Adobe Creative Cloud All Apps', 'seats' => 15, 'serial' => 'ADOBE-CC-ENTERPRISE-2026', 'cost' => 45000000, 'supplier_id' => $supPhongVu->id, 'mf_id' => $mfApple->id],
            ['name' => 'GitHub Enterprise Cloud Seats', 'seats' => 40, 'serial' => 'GH-ENT-ORG-FPT-2026', 'cost' => 28000000, 'supplier_id' => $supFpt->id, 'mf_id' => $mfMicrosoft->id],
            ['name' => 'Docker Desktop Business Subscription', 'seats' => 25, 'serial' => 'DOCKER-BIZ-TEAM-2026', 'cost' => 15000000, 'supplier_id' => $supDigiworld->id, 'mf_id' => $mfMicrosoft->id],
            ['name' => 'Slack Enterprise Grid License', 'seats' => 60, 'serial' => 'SLACK-ENT-CORP-2026', 'cost' => 32000000, 'supplier_id' => $supElite->id, 'mf_id' => $mfMicrosoft->id],
            ['name' => 'Windows Server 2022 Datacenter 16-Core', 'seats' => 10, 'serial' => 'WS22-DC-16C-KEY-2026', 'cost' => 85000000, 'supplier_id' => $supFpt->id, 'mf_id' => $mfMicrosoft->id],
            ['name' => 'Atlassian Jira & Confluence Data Center', 'seats' => 50, 'serial' => 'ATLAS-DC-SUITE-2026', 'cost' => 70000000, 'supplier_id' => $supCmc->id, 'mf_id' => $mfMicrosoft->id],
            ['name' => 'VMware vSphere 8 Standard Edition', 'seats' => 12, 'serial' => 'VMW-VSPHERE8-STD-2026', 'cost' => 95000000, 'supplier_id' => $supFpt->id, 'mf_id' => $mfDell->id],
            ['name' => 'Autodesk AutoCAD 2026 Commercial', 'seats' => 10, 'serial' => 'ACAD-2026-COMM-KEY', 'cost' => 55000000, 'supplier_id' => $supDigiworld->id, 'mf_id' => $mfHp->id],
        ];

        foreach ($licensesData as $ld) {
            $lic = License::firstOrCreate(
                ['name' => $ld['name']],
                [
                    'category_id' => $catLicense->id,
                    'company_id' => $companyFpt->id,
                    'manufacturer_id' => $ld['mf_id'],
                    'supplier_id' => $ld['supplier_id'],
                    'seats' => $ld['seats'],
                    'serial' => $ld['serial'],
                    'license_name' => $ld['name'] . ' Org',
                    'license_email' => 'license-admin@fpt-software.com',
                    'purchase_cost' => $ld['cost'],
                    'purchase_date' => '2026-01-01',
                ]
            );

            // Đảm bảo đủ license seats
            $existingSeats = LicenseSeat::where('license_id', $lic->id)->count();
            for ($i = $existingSeats + 1; $i <= $lic->seats; $i++) {
                LicenseSeat::create([
                    'license_id' => $lic->id,
                    'unreassignable_seat' => false,
                ]);
            }
        }

        // Gán 1 số ghế mẫu
        $m365 = License::where('name', 'Microsoft 365 E5 Enterprise')->first();
        if ($m365) {
            $seats = LicenseSeat::where('license_id', $m365->id)->get();
            if ($seats->count() > 0) { $seats[0]->assigned_to = 1; $seats[0]->save(); }
            if ($seats->count() > 1) { $seats[1]->assigned_to = $userAn->id; $seats[1]->save(); }
        }

        // ==========================================
        // 14. PHỤ KIỆN (ACCESSORIES >= 10)
        // ==========================================
        $this->command->info('14. Khởi tạo 10 Phụ kiện (Accessories)...');
        $accessoriesData = [
            ['name' => 'Chuột không dây Logitech MX Master 3S', 'qty' => 20, 'cost' => 2450000, 'mf_id' => $mfLogitech->id],
            ['name' => 'Bàn phím cơ Keychron K2 Pro Wireless', 'qty' => 15, 'cost' => 2150000, 'mf_id' => $mfLogitech->id],
            ['name' => 'Bộ chia cổng Dell USB-C Multiport DA310', 'qty' => 18, 'cost' => 1890000, 'mf_id' => $mfDell->id],
            ['name' => 'Tai nghe chống ồn Jabra Evolve2 65 Flex', 'qty' => 12, 'cost' => 4500000, 'mf_id' => $mfLogitech->id],
            ['name' => 'Webcam họp trực tuyến Logitech Brio 4K Pro', 'qty' => 10, 'cost' => 3800000, 'mf_id' => $mfLogitech->id],
            ['name' => 'Bút cảm ứng Apple Pencil (Thế hệ 2)', 'qty' => 10, 'cost' => 2990000, 'mf_id' => $mfApple->id],
            ['name' => 'Chuột không dây Apple Magic Mouse 2 Black', 'qty' => 14, 'cost' => 2190000, 'mf_id' => $mfApple->id],
            ['name' => 'Bàn phím Apple Magic Keyboard Touch ID', 'qty' => 10, 'cost' => 3490000, 'mf_id' => $mfApple->id],
            ['name' => 'Giá đỡ tản nhiệt hợp kim nhôm Ergo Aluminium', 'qty' => 25, 'cost' => 650000, 'mf_id' => $mfLogitech->id],
            ['name' => 'Bộ củ sạc nhanh Anker 737 GaNPrime 120W', 'qty' => 20, 'cost' => 1750000, 'mf_id' => $mfDell->id],
        ];

        foreach ($accessoriesData as $acc) {
            Accessory::firstOrCreate(
                ['name' => $acc['name']],
                [
                    'category_id' => $catAccessory->id,
                    'company_id' => $companyFpt->id,
                    'manufacturer_id' => $acc['mf_id'],
                    'default_supplier_id' => $supPhongVu->id,
                    'qty' => $acc['qty'],
                    'location_id' => $locWarehouse->id,
                    'default_purchase_cost' => $acc['cost'],
                ]
            );
        }

        // ==========================================
        // 15. VẬT TƯ TIÊU HAO (CONSUMABLES >= 10)
        // ==========================================
        $this->command->info('15. Khởi tạo 10 Vật tư tiêu hao (Consumables)...');
        $consumablesData = [
            ['name' => 'Hộp mực HP LaserJet Pro M404 Toner 58A', 'qty' => 10, 'min_amt' => 2, 'cost' => 1850000],
            ['name' => 'Cuộn cáp mạng CommScope Cat6 UTP 305m', 'qty' => 10, 'min_amt' => 2, 'cost' => 2950000],
            ['name' => 'Thùng giấy in Double A A4 80gsm (5 ram/thùng)', 'qty' => 30, 'min_amt' => 5, 'cost' => 450000],
            ['name' => 'Hộp mực Canon Cartridge 057 (cho LBP223dw)', 'qty' => 12, 'min_amt' => 3, 'cost' => 1790000],
            ['name' => 'Hộp đầu bấm mạng RJ45 Cat6 bọc kim (100 hạt)', 'qty' => 20, 'min_amt' => 4, 'cost' => 380000],
            ['name' => 'Cuộn băng keo thít cáp Velcro cuộn 5m', 'qty' => 25, 'min_amt' => 5, 'cost' => 120000],
            ['name' => 'Vỉ pin sạc AA Panasonic Eneloop Pro 2550mAh (4 viên)', 'qty' => 40, 'min_amt' => 8, 'cost' => 390000],
            ['name' => 'Chai xịt khí nén làm sạch vi mạch Falcon Dust-Off 300ml', 'qty' => 15, 'min_amt' => 3, 'cost' => 180000],
            ['name' => 'Cuộn nhãn in nhiệt Brother TZe-231 (12mm chữ đen nền trắng)', 'qty' => 18, 'min_amt' => 4, 'cost' => 260000],
            ['name' => 'Hộp găng tay chống tĩnh điện phủ PU phòng Lab (10 đôi)', 'qty' => 50, 'min_amt' => 10, 'cost' => 150000],
        ];

        foreach ($consumablesData as $con) {
            Consumable::firstOrCreate(
                ['name' => $con['name']],
                [
                    'category_id' => $catConsumable->id,
                    'company_id' => $companyFpt->id,
                    'default_supplier_id' => $supPhongVu->id,
                    'qty' => $con['qty'],
                    'min_amt' => $con['min_amt'],
                    'location_id' => $locWarehouse->id,
                    'default_purchase_cost' => $con['cost'],
                ]
            );
        }

        // ==========================================
        // 16. LINH KIỆN PHẦN CỨNG (COMPONENTS >= 10)
        // ==========================================
        $this->command->info('16. Khởi tạo 10 Linh kiện phần cứng (Components)...');
        $componentsData = [
            ['name' => 'RAM Kingston Fury Beast 32GB DDR5 5600MHz', 'qty' => 16, 'cost' => 2850000, 'mf_id' => $mfKingston->id],
            ['name' => 'Ổ cứng SSD Samsung 990 Pro 2TB NVMe PCIe 4.0', 'qty' => 12, 'cost' => 4650000, 'mf_id' => $mfSamsung->id],
            ['name' => 'Card mạng Intel X520-DA2 10GbE Dual Port SFP+', 'qty' => 10, 'cost' => 3950000, 'mf_id' => $mfDell->id],
            ['name' => 'Nguồn máy chủ Dell 800W Platinum Hot-Plug Redundant', 'qty' => 10, 'cost' => 4800000, 'mf_id' => $mfDell->id],
            ['name' => 'Quạt tản nhiệt Noctua NF-A12x25 PWM 120mm', 'qty' => 20, 'cost' => 790000, 'mf_id' => $mfDell->id],
            ['name' => 'RAM Crucial 16GB DDR4 3200MHz SODIMM Laptop', 'qty' => 25, 'cost' => 950000, 'mf_id' => $mfKingston->id],
            ['name' => 'Ổ cứng HDD Seagate IronWolf Pro 8TB 7200RPM NAS', 'qty' => 10, 'cost' => 5900000, 'mf_id' => $mfDell->id],
            ['name' => 'Card đồ họa chuyên nghiệp NVIDIA RTX 4000 Ada 20GB', 'qty' => 10, 'cost' => 32500000, 'mf_id' => $mfDell->id],
            ['name' => 'Mô-đun quang Cisco 10GBASE-SR SFP+ Transceiver', 'qty' => 16, 'cost' => 1950000, 'mf_id' => $mfCisco->id],
            ['name' => 'Pin thay thế Laptop Dell XPS 15 9530 86Wh', 'qty' => 10, 'cost' => 1850000, 'mf_id' => $mfDell->id],
        ];

        foreach ($componentsData as $comp) {
            Component::firstOrCreate(
                ['name' => $comp['name']],
                [
                    'category_id' => $catComponent->id,
                    'company_id' => $companyFpt->id,
                    'manufacturer_id' => $comp['mf_id'],
                    'default_supplier_id' => $supPhongVu->id,
                    'qty' => $comp['qty'],
                    'location_id' => $locWarehouse->id,
                    'default_purchase_cost' => $comp['cost'],
                ]
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->command->info('=== HOÀN TẤT: TOÀN BỘ CÁC TABLE ĐỀU CÓ TỪ 10 DỮ LIỆU MẪU TRỞ LÊN! ===');
    }
}
