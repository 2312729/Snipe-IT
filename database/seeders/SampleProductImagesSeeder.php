<?php

namespace Database\Seeders;

use App\Models\AssetModel;
use App\Models\Consumable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleProductImagesSeeder extends Seeder
{
    /**
     * Generate product images for Models and Consumables using GD.
     */
    public function run()
    {
        $this->command->info('====================================================');
        $this->command->info(' BẮT ĐẦU TẠO HÌNH ẢNH MẪU SẢN PHẨM (PRODUCT IMAGES)');
        $this->command->info('====================================================');

        $uploadsBase = public_path('uploads');
        $modelsDir = $uploadsBase . '/models';
        $consumablesDir = $uploadsBase . '/consumables';

        if (!is_dir($modelsDir)) {
            mkdir($modelsDir, 0775, true);
        }
        if (!is_dir($consumablesDir)) {
            mkdir($consumablesDir, 0775, true);
        }

        // Cũng đảm bảo thư mục trong /var/lib/snipeit/data/uploads nếu có
        $altModelsDir = '/var/lib/snipeit/data/uploads/models';
        $altConsumablesDir = '/var/lib/snipeit/data/uploads/consumables';
        if (is_dir('/var/lib/snipeit/data/uploads')) {
            if (!is_dir($altModelsDir)) @mkdir($altModelsDir, 0775, true);
            if (!is_dir($altConsumablesDir)) @mkdir($altConsumablesDir, 0775, true);
        }

        // 1. TẠO HÌNH ẢNH MẪU CHO CÁC ASSET MODELS
        $this->command->info('-> Đang sinh hình ảnh cho các Dòng sản phẩm (Asset Models)...');

        $modelImageDefs = [
            'Dell PowerEdge R750xs' => ['filename' => 'model_dell_r750xs.png', 'badge' => 'DELL 2U', 'name' => 'PowerEdge R750xs', 'color' => [15, 23, 42], 'accent' => [0, 118, 206]],
            'HPE ProLiant DL380' => ['filename' => 'model_hpe_dl380.png', 'badge' => 'HPE SERVER', 'name' => 'ProLiant DL380 Gen10', 'color' => [20, 30, 40], 'accent' => [1, 169, 130]],
            'Cisco Catalyst 9300' => ['filename' => 'model_cisco_c9300.png', 'badge' => 'CISCO CORE', 'name' => 'Catalyst 9300 48P', 'color' => [10, 25, 47], 'accent' => [4, 159, 217]],
            'Fortinet FortiGate 100F' => ['filename' => 'model_fortigate_100f.png', 'badge' => 'FORTINET SEC', 'name' => 'FortiGate 100F', 'color' => [30, 15, 20], 'accent' => [238, 43, 42]],
            'Synology Enterprise NAS SA3400D' => ['filename' => 'model_synology_sa3400.png', 'badge' => 'SYNOLOGY NAS', 'name' => 'NAS SA3400D 12Bay', 'color' => [24, 30, 36], 'accent' => [37, 99, 235]],
            'APC Smart-UPS On-Line 3000VA' => ['filename' => 'model_apc_ups_3000.png', 'badge' => 'APC SCHNEIDER', 'name' => 'Smart-UPS RT 3000VA', 'color' => [20, 25, 25], 'accent' => [22, 163, 74]],
            'Lenovo ThinkPad X1 Carbon' => ['filename' => 'model_thinkpad_x1c.png', 'badge' => 'THINKPAD', 'name' => 'X1 Carbon Gen 11', 'color' => [17, 24, 39], 'accent' => [220, 38, 38]],
            'ThinkPad X1 Carbon' => ['filename' => 'model_thinkpad_x1c.png', 'badge' => 'THINKPAD', 'name' => 'X1 Carbon Gen 11', 'color' => [17, 24, 39], 'accent' => [220, 38, 38]],
            'Apple MacBook Pro 16' => ['filename' => 'model_macbook_pro16.png', 'badge' => 'APPLE SILICON', 'name' => 'MacBook Pro 16 M3', 'color' => [30, 41, 59], 'accent' => [148, 163, 184]],
            'MacBook Pro 16' => ['filename' => 'model_macbook_pro16.png', 'badge' => 'APPLE SILICON', 'name' => 'MacBook Pro 16 M3', 'color' => [30, 41, 59], 'accent' => [148, 163, 184]],
            'MacBook Pro 14' => ['filename' => 'model_macbook_pro14.png', 'badge' => 'APPLE SILICON', 'name' => 'MacBook Pro 14 M3', 'color' => [30, 41, 59], 'accent' => [148, 163, 184]],
            'Dell XPS 15 9530' => ['filename' => 'model_dell_xps15.png', 'badge' => 'DELL XPS', 'name' => 'XPS 15 9530 OLED', 'color' => [15, 23, 42], 'accent' => [59, 130, 246]],
            'HP EliteBook 840 G9' => ['filename' => 'model_hp_elitebook.png', 'badge' => 'HP ENTERPRISE', 'name' => 'EliteBook 840 G9', 'color' => [31, 41, 55], 'accent' => [0, 150, 214]],
            'Dell UltraSharp 27' => ['filename' => 'model_dell_u2723qe.png', 'badge' => 'DELL 4K IPS', 'name' => 'UltraSharp 27 4K', 'color' => [15, 23, 42], 'accent' => [16, 185, 129]],
            'Dell Precision 3660' => ['filename' => 'model_dell_precision3660.png', 'badge' => 'DELL TOWER', 'name' => 'Precision 3660', 'color' => [15, 23, 42], 'accent' => [0, 118, 206]],
            'ASUS ProArt' => ['filename' => 'model_asus_proart27.png', 'badge' => 'ASUS PROART', 'name' => 'ProArt PA278CV 2K', 'color' => [20, 24, 33], 'accent' => [245, 158, 11]],
            'Cisco Catalyst 9200L' => ['filename' => 'model_cisco_c9200l.png', 'badge' => 'CISCO SWITCH', 'name' => 'Catalyst 9200L 48P', 'color' => [10, 25, 47], 'accent' => [4, 159, 217]],
            'iPad Pro' => ['filename' => 'model_ipad_pro129.png', 'badge' => 'APPLE IPAD', 'name' => 'iPad Pro 12.9 M2', 'color' => [28, 35, 48], 'accent' => [168, 85, 247]],
        ];

        $assetsDir = $uploadsBase . '/assets';
        $altAssetsDir = '/var/lib/snipeit/data/uploads/assets';
        if (!is_dir($assetsDir)) @mkdir($assetsDir, 0775, true);
        if (is_dir('/var/lib/snipeit/data/uploads') && !is_dir($altAssetsDir)) @mkdir($altAssetsDir, 0775, true);

        foreach ($modelImageDefs as $key => $def) {
            $this->createHardwareBadgeImage($modelsDir . '/' . $def['filename'], $def['badge'], $def['name'], $def['color'], $def['accent']);
            @copy($modelsDir . '/' . $def['filename'], $assetsDir . '/' . $def['filename']);
            if (is_dir($altModelsDir)) {
                @copy($modelsDir . '/' . $def['filename'], $altModelsDir . '/' . $def['filename']);
                @copy($modelsDir . '/' . $def['filename'], $altAssetsDir . '/' . $def['filename']);
            }

            // Gán ảnh vào database models
            AssetModel::where('name', 'like', "%{$key}%")->update(['image' => $def['filename']]);
        }

        // Cập nhật assets.image cho tất cả thiết bị
        $modelsById = AssetModel::all()->keyBy('id');
        foreach (\App\Models\Asset::all() as $ast) {
            if ($ast->model_id && isset($modelsById[$ast->model_id]) && $modelsById[$ast->model_id]->image) {
                $ast->image = $modelsById[$ast->model_id]->image;
                $ast->save();
            }
        }

        // 2. TẠO HÌNH ẢNH MẪU CHO CÁC VẬT TƯ TIÊU HAO (CONSUMABLES)
        $this->command->info('-> Đang sinh hình ảnh cho các Vật tư tiêu hao (Consumables)...');

        $consumableImageDefs = [
            2 => ['filename' => 'csm_commscope_cat6.png', 'badge' => 'COMMSCOPE', 'name' => 'Cap Mang Cat6 305m', 'color' => [15, 23, 42], 'accent' => [37, 99, 235]],
            1 => ['filename' => 'csm_hp_toner_58a.png', 'badge' => 'HP TONER', 'name' => 'Hop Muc 58A Laser', 'color' => [20, 25, 30], 'accent' => [0, 150, 214]],
            3 => ['filename' => 'csm_double_a_a4.png', 'badge' => 'DOUBLE A', 'name' => 'Giay In A4 80gsm', 'color' => [25, 35, 45], 'accent' => [59, 130, 246]],
            4 => ['filename' => 'csm_canon_057.png', 'badge' => 'CANON TONER', 'name' => 'Hop Muc Cartridge 057', 'color' => [30, 20, 20], 'accent' => [220, 38, 38]],
            5 => ['filename' => 'csm_rj45_cat6.png', 'badge' => 'MODULAR PLUG', 'name' => 'Hat Mang RJ45 Cat6', 'color' => [15, 23, 42], 'accent' => [234, 179, 8]],
            6 => ['filename' => 'csm_velcro_tape.png', 'badge' => 'VELCRO', 'name' => 'Bang Thit Cap 5m', 'color' => [20, 20, 20], 'accent' => [107, 114, 128]],
            7 => ['filename' => 'csm_eneloop_pro.png', 'badge' => 'PANASONIC', 'name' => 'Pin Eneloop Pro AA', 'color' => [15, 23, 42], 'accent' => [16, 185, 129]],
            8 => ['filename' => 'csm_dust_off.png', 'badge' => 'FALCON', 'name' => 'Chai Xit Khi Nen 300ml', 'color' => [30, 40, 50], 'accent' => [14, 165, 233]],
            9 => ['filename' => 'csm_brother_tze.png', 'badge' => 'BROTHER TZe', 'name' => 'Nhan In Nhiet 12mm', 'color' => [20, 30, 40], 'accent' => [249, 115, 22]],
            10 => ['filename' => 'csm_esd_gloves.png', 'badge' => 'CLEANROOM', 'name' => 'Gang Tay PU Chong Tinh', 'color' => [25, 30, 35], 'accent' => [168, 85, 247]],
        ];

        foreach ($consumableImageDefs as $cid => $cDef) {
            $this->createHardwareBadgeImage($consumablesDir . '/' . $cDef['filename'], $cDef['badge'], $cDef['name'], $cDef['color'], $cDef['accent']);
            if (is_dir($altConsumablesDir)) {
                @copy($consumablesDir . '/' . $cDef['filename'], $altConsumablesDir . '/' . $cDef['filename']);
            }

            Consumable::where('id', $cid)->update(['image' => $cDef['filename']]);
        }

        // Tạo ảnh mặc định cho các consumable còn lại (IDs 11 đến 25)
        $extraConsumables = Consumable::where('id', '>', 10)->get();
        foreach ($extraConsumables as $ec) {
            $fn = "csm_item_{$ec->id}.png";
            $shortName = substr($ec->name, 0, 20);
            $this->createHardwareBadgeImage($consumablesDir . '/' . $fn, 'IT CONSUMABLE', $shortName, [15, 23, 42], [59, 130, 246]);
            if (is_dir($altConsumablesDir)) {
                @copy($consumablesDir . '/' . $fn, $altConsumablesDir . '/' . $fn);
            }
            $ec->image = $fn;
            $ec->save();
        }

        $this->command->info('-> HOÀN TẤT THÀNH CÔNG! ĐÃ SINH VÀ CẬP NHẬT ĐẦY ĐỦ ẢNH SẢN PHẨM TRÊN HỆ THỐNG.');
        $this->command->info('====================================================');
    }

    /**
     * Tạo hình ảnh dạng thẻ sản phẩm chuyên nghiệp bằng thư viện GD
     */
    private function createHardwareBadgeImage($filePath, $badgeText, $titleText, $bgRgb, $accentRgb)
    {
        $w = 320;
        $h = 240;
        $img = imagecreatetruecolor($w, $h);

        // Màu sắc
        $bgColor = imagecolorallocate($img, $bgRgb[0], $bgRgb[1], $bgRgb[2]);
        $accentColor = imagecolorallocate($img, $accentRgb[0], $accentRgb[1], $accentRgb[2]);
        $cardBg = imagecolorallocate($img, min(255, $bgRgb[0] + 15), min(255, $bgRgb[1] + 15), min(255, $bgRgb[2] + 20));
        $borderColor = imagecolorallocate($img, min(255, $accentRgb[0] + 50), min(255, $accentRgb[1] + 50), min(255, $accentRgb[2] + 50));
        $white = imagecolorallocate($img, 255, 255, 255);
        $textColor = imagecolorallocate($img, 226, 232, 240);
        $shadowColor = imagecolorallocate($img, max(0, $bgRgb[0] - 10), max(0, $bgRgb[1] - 10), max(0, $bgRgb[2] - 10));

        // Nền chính
        imagefilledrectangle($img, 0, 0, $w, $h, $bgColor);

        // Khung card sản phẩm bên trong
        imagefilledrectangle($img, 12, 12, $w - 12, $h - 12, $cardBg);
        imagerectangle($img, 12, 12, $w - 12, $h - 12, $borderColor);

        // Thanh dải màu điểm nhấn phía trên
        imagefilledrectangle($img, 12, 12, $w - 12, 22, $accentColor);

        // Khung nhãn Badge thương hiệu
        $badgeX = 26;
        $badgeY = 38;
        $badgeW = strlen($badgeText) * 9 + 18;
        $badgeH = 24;
        imagefilledrectangle($img, $badgeX, $badgeY, $badgeX + $badgeW, $badgeY + $badgeH, $accentColor);
        imagestring($img, 3, $badgeX + 8, $badgeY + 5, $badgeText, $white);

        // Biểu tượng icon phần cứng mô phỏng
        $cx = $w / 2;
        $cy = 115;
        imagefilledrectangle($img, $cx - 45, $cy - 25, $cx + 45, $cy + 25, $shadowColor);
        imagerectangle($img, $cx - 45, $cy - 25, $cx + 45, $cy + 25, $accentColor);
        imagefilledellipse($img, $cx - 28, $cy, 8, 8, $accentColor);
        imagefilledellipse($img, $cx + 28, $cy, 8, 8, $accentColor);
        imageline($img, $cx - 15, $cy, $cx + 15, $cy, $accentColor);

        // Tên sản phẩm chính
        $titleY = 175;
        $titleX = max(24, ($w - (strlen($titleText) * 9)) / 2);
        imagestring($img, 4, (int)$titleX, $titleY, $titleText, $white);

        // Mã tem kiểm định
        $subText = "[ VERIFIED HARDWARE ASSET ]";
        $subX = ($w - (strlen($subText) * 6)) / 2;
        imagestring($img, 1, (int)$subX, 205, $subText, $textColor);

        imagepng($img, $filePath);
        imagedestroy($img);
    }
}
