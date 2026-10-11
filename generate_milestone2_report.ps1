$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$reportRoot = Join-Path $projectRoot 'reports'
$outputPath = Join-Path $reportRoot 'Bao_cao_giua_ky_Snipe-IT_K16.docx'
$temporaryOutputPath = $outputPath + '.tmp'
$workPath = Join-Path $reportRoot ('milestone2-docx-' + [Guid]::NewGuid().ToString('N'))
$utf8 = New-Object System.Text.UTF8Encoding($false)

function ConvertTo-XmlText {
    param([AllowEmptyString()][string]$Text)

    return [System.Security.SecurityElement]::Escape($Text)
}

function New-WordRun {
    param(
        [AllowEmptyString()][string]$Text,
        [string]$RunProperties = ''
    )

    $escaped = ConvertTo-XmlText $Text

    return "<w:r>$RunProperties<w:t xml:space=`"preserve`">$escaped</w:t></w:r>"
}

function New-WordParagraph {
    param(
        [AllowEmptyString()][string]$Text,
        [string]$Style = 'BodyText',
        [string]$RunProperties = ''
    )

    $run = New-WordRun -Text $Text -RunProperties $RunProperties

    return "<w:p><w:pPr><w:pStyle w:val=`"$Style`"/></w:pPr>$run</w:p>"
}

function New-WordTable {
    param(
        [string[]]$Headers,
        [object[]]$Rows
    )

    $columnWidth = [Math]::Floor(9360 / $Headers.Count)
    $xml = [System.Text.StringBuilder]::new()
    $initialTableXml = @'
<w:tbl>
<w:tblPr><w:tblW w:w="9360" w:type="dxa"/><w:tblBorders>
<w:top w:val="single" w:sz="4" w:color="B7C9D6"/>
<w:left w:val="single" w:sz="4" w:color="B7C9D6"/>
<w:bottom w:val="single" w:sz="4" w:color="B7C9D6"/>
<w:right w:val="single" w:sz="4" w:color="B7C9D6"/>
<w:insideH w:val="single" w:sz="4" w:color="B7C9D6"/>
<w:insideV w:val="single" w:sz="4" w:color="B7C9D6"/>
</w:tblBorders></w:tblPr>
'@
    [void]$xml.Append($initialTableXml)
    $headerCells = [System.Collections.Generic.List[string]]::new()

    foreach ($header in $Headers) {
        $headerRun = New-WordRun -Text $header -RunProperties '<w:rPr><w:b/><w:color w:val="FFFFFF"/></w:rPr>'
        $headerCells.Add("<w:tc><w:tcPr><w:tcW w:w=`"$columnWidth`" w:type=`"dxa`"/><w:shd w:fill=`"1F4E78`"/></w:tcPr><w:p><w:pPr><w:pStyle w:val=`"TableText`"/></w:pPr>$headerRun</w:p></w:tc>")
    }

    [void]$xml.Append('<w:tr>' + ($headerCells -join '') + '</w:tr>')

    foreach ($row in $Rows) {
        $cells = @()
        foreach ($cell in $row.Cells) {
            $cellRun = New-WordRun -Text ([string]$cell)
            $cells += "<w:tc><w:tcPr><w:tcW w:w=`"$columnWidth`" w:type=`"dxa`"/></w:tcPr><w:p><w:pPr><w:pStyle w:val=`"TableText`"/></w:pPr>$cellRun</w:p></w:tc>"
        }
        [void]$xml.Append('<w:tr>' + ($cells -join '') + '</w:tr>')
    }

    [void]$xml.Append('</w:tbl>')

    return $xml.ToString()
}

$content = [System.Collections.Generic.List[string]]::new()
$content.Add((New-WordParagraph -Text 'BÁO CÁO GIỮA KỲ (MILESTONE 2)' -Style 'Title'))
$content.Add((New-WordParagraph -Text 'MÔN KIỂM THỬ PHẦN MỀM' -Style 'Subtitle'))
$content.Add((New-WordParagraph -Text 'PHÂN TÍCH KIẾN TRÚC VÀ TRIỂN KHAI KIỂM THỬ NÂNG CAO HỆ THỐNG SNIPE-IT' -Style 'Subtitle'))
$content.Add((New-WordParagraph -Text 'Mã đề tài: R03 | Kỹ thuật trọng tâm giai đoạn tiếp theo: K16 – Testcontainers' -Style 'Subtitle'))
$content.Add((New-WordParagraph -Text 'Kho mã nguồn: Bee-Max-Ne/Snipe-IT (bản sao làm việc được cung cấp)' -Style 'Subtitle'))
$content.Add((New-WordParagraph -Text 'Nhóm thực hiện: [Bổ sung tên nhóm]    Giảng viên hướng dẫn: Nguyễn Thế Lâm' -Style 'Subtitle'))
$content.Add((New-WordParagraph -Text 'Mốc báo cáo: 20/10/2026 (theo lịch đề bài)    Ngày biên soạn: 11/10/2026' -Style 'Subtitle'))
$content.Add((New-WordParagraph -Text 'Lưu ý về tính xác thực: Báo cáo phân biệt rõ chức năng/mã nguồn đã quan sát được với kết quả vận hành cần bổ sung bằng chứng. Các trường SHA/tag, ảnh chụp và kết quả CI chưa có dữ liệu xác minh trong hồ sơ hiện tại nên được đánh dấu cần hoàn thiện, không tự suy đoán.' -Style 'Callout'))

$content.Add((New-WordParagraph -Text 'TÓM TẮT ĐIỀU HÀNH' -Style 'Heading1'))
$content.Add((New-WordParagraph -Text 'Snipe-IT là ứng dụng quản lý tài sản CNTT doanh nghiệp xây dựng trên Laravel. Hệ thống cung cấp giao diện web và REST API để quản lý tài sản, người dùng, phân quyền, cấp phát/thu hồi, license, phụ kiện, vật tư, linh kiện, bảo trì, kiểm kê, báo cáo và nhật ký kiểm toán. Phân tích trong báo cáo dựa trên mã nguồn và cấu hình của bản sao dự án hiện có, không thay thế việc nghiệm thu trên máy triển khai.' -Style 'BodyText'))
$content.Add((New-WordParagraph -Text 'Kết quả onboarding đã nhận diện được các lớp chính của kiến trúc Laravel, luồng web/API, mô hình Eloquent, Policy/RBAC, phạm vi đa công ty và hệ thống test PHPUnit. Hạ tầng K16 hiện có bộ chạy Node.js dùng @testcontainers/mysql để khởi tạo MySQL 8.0.36 tạm thời, cấp thông tin kết nối và khóa Passport cho tiến trình PHPUnit, sau đó dừng container. Workflow GitHub Actions đã được khai báo nhưng chỉ chạy một ca checkout/checkin mục tiêu; không có cơ sở để kết luận toàn bộ test suite hoặc các phân vùng kiểm thử khác đã chạy thành công.' -Style 'BodyText'))
$content.Add((New-WordParagraph -Text 'Báo cáo tập trung các phần A–C và định hướng D theo đề cương giữa kỳ. Các nhận định đã đối chiếu được với tệp cấu hình/mã nguồn được liệt kê tại Phần F; những minh chứng phụ thuộc vào máy chạy hoặc GitHub cần được nhóm bổ sung trước khi nộp.' -Style 'BodyText'))

$content.Add((New-WordParagraph -Text 'PHẦN A. MỤC TIÊU HỆ THỐNG, ACTOR, MODULE VÀ LUỒNG DỮ LIỆU' -Style 'Heading1'))
$content.Add((New-WordParagraph -Text 'A.1. Mục tiêu và phạm vi hệ thống' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text 'Snipe-IT hỗ trợ quản lý vòng đời tài sản CNTT: khai báo danh mục và thiết bị; theo dõi tình trạng, vị trí và đơn vị sở hữu; cấp phát cho người dùng/địa điểm; thu hồi và đưa về kho; kiểm kê, bảo trì, báo cáo và lưu lịch sử thao tác. Các phân hệ liên quan gồm tài sản phần cứng, model, trạng thái, công ty, địa điểm, phòng ban, người dùng/nhóm quyền, license/license seat, accessories, consumables, components, kits, imports, orders và action logs.' -Style 'BodyText'))
$content.Add((New-WordParagraph -Text 'Ứng dụng có hai bề mặt sử dụng song song: giao diện web Blade và REST API phiên bản v1. API được tiêu thụ bởi ứng dụng tích hợp và các giao diện dữ liệu như datatables/select2. Laravel Mix đóng gói frontend; mã nguồn/package.json được kiểm tra không cho thấy Vue/TypeScript là tầng giao diện chính. Livewire được dùng cho một số chức năng riêng, không thay thế kiến trúc Blade/controller.' -Style 'BodyText'))

$content.Add((New-WordParagraph -Text 'A.2. Actor và ca sử dụng tiêu biểu' -Style 'Heading2'))
$content.Add((New-WordTable -Headers @('Actor', 'Trách nhiệm', 'Ca sử dụng tiêu biểu') -Rows @(
    [pscustomobject]@{ Cells = @('Super Admin / Admin', 'Quản trị cấu hình, tài khoản, nhóm quyền và dữ liệu tổ chức.', 'Quản lý người dùng; cấu hình hệ thống; quản lý danh mục và báo cáo.') },
    [pscustomobject]@{ Cells = @('Asset Manager / nhân viên được cấp quyền', 'Quản lý tài sản và thực hiện nghiệp vụ theo Policy/quyền được cấp.', 'Tạo/cập nhật tài sản; checkout/checkin; kiểm kê; quản lý license, phụ kiện, vật tư.') },
    [pscustomobject]@{ Cells = @('End User', 'Tra cứu tài sản được phép xem và gửi yêu cầu cấp phát khi loại tài sản hỗ trợ.', 'Xem tài sản đang được gán; gửi/hủy yêu cầu tài sản.') },
    [pscustomobject]@{ Cells = @('API Client / ứng dụng tích hợp', 'Gọi API v1 theo cơ chế xác thực và giới hạn tốc độ đã cấu hình.', 'Tra cứu/đồng bộ đối tượng; thao tác trên endpoint được cấp quyền.') }
)))

$content.Add((New-WordParagraph -Text 'A.3. Cây thư mục và module mã nguồn' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text 'app/Http/Controllers/         Controller giao diện web; có nhánh Api/ trả JSON' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'app/Http/Transformers/        Chuẩn hóa cấu trúc dữ liệu API' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'app/Models/                   Model Eloquent, quan hệ, scope và quy tắc miền' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'app/Policies/                 Phân quyền theo tài nguyên và hành động' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'app/Actions/, app/Services/   Tác vụ nghiệp vụ và dịch vụ dùng chung' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'app/Observers/, app/Events/   Tác dụng phụ, sự kiện và nhật ký' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'resources/views/              Giao diện Blade' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'routes/web.php, routes/web/    Định tuyến giao diện theo nhóm chức năng' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'routes/api.php                 Định tuyến API dưới tiền tố v1' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'database/migrations/           Thay đổi schema; database/seeders/ khởi tạo dữ liệu' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'tests/Unit/, tests/Feature/    PHPUnit; tests/testcontainers/ điều phối DB tích hợp' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'config/, bootstrap/, public/   Cấu hình ứng dụng, khởi động Laravel và tài nguyên công khai' -Style 'Code'))

$content.Add((New-WordParagraph -Text 'A.4. Dependency và nền tảng kỹ thuật đã đối chiếu' -Style 'Heading2'))
$content.Add((New-WordTable -Headers @('Thành phần', 'Thông tin trong dự án', 'Ghi chú') -Rows @(
    [pscustomobject]@{ Cells = @('Backend', 'PHP ^8.2; Laravel ^12.0', 'Khai báo trong composer.json; phiên bản thực tế cần xác minh trên máy chạy.') },
    [pscustomobject]@{ Cells = @('Xác thực API', 'Laravel Passport; có cấu hình mở rộng OIDC', 'Không nên mô tả luồng API là Sanctum nếu chưa thay đổi mã nguồn.') },
    [pscustomobject]@{ Cells = @('Giao diện', 'Blade/AdminLTE 2, Bootstrap 3; Laravel Mix; Livewire cho thành phần rời', 'Không dùng Vite/Vue làm giả định cho hướng dẫn này.') },
    [pscustomobject]@{ Cells = @('Kiểm thử', 'PHPUnit 11; Unit và Feature suites', 'phpunit.xml nạp tests/Unit và tests/Feature.') },
    [pscustomobject]@{ Cells = @('K16 database', 'Node.js runner + @testcontainers/mysql + mysql:8.0.36', 'Docker daemon phải hoạt động; CI cấu hình Node 22.') },
    [pscustomobject]@{ Cells = @('Lưu trữ', 'CSDL quan hệ qua PDO; cấu hình kết nối theo Laravel', 'Runner K16 hiện cấu hình MySQL; không suy rộng thành PostgreSQL đã được kiểm thử.') }
)))

$content.Add((New-WordParagraph -Text 'A.5. Ba luồng dữ liệu/nghiệp vụ cơ bản' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text 'Luồng 1 – Đăng nhập và phân quyền: Người dùng gửi thông tin đăng nhập → middleware/guard xác thực phiên web hoặc token API → Policy/Gate và quyền nhóm quyết định truy cập → controller web trả view hoặc API controller trả dữ liệu đã định dạng. Cấu hình thực tế sử dụng Laravel và Passport cho API; tên guard/middleware cụ thể còn tùy endpoint.' -Style 'BodyText'))
$content.Add((New-WordParagraph -Text 'Luồng 2 – Cấp phát (checkout): Người có quyền chọn tài sản và đối tượng nhận → request được kiểm tra dữ liệu, trạng thái sẵn sàng và quyền → logic checkout cập nhật liên kết gán/tình trạng và bộ đếm liên quan → observer/action log ghi lịch sử; thông báo có thể phát sinh theo nghiệp vụ. Test tích hợp hiện có bao gồm cấp phát qua API và xác nhận tài sản được gán, bộ đếm checkout tăng.' -Style 'BodyText'))
$content.Add((New-WordParagraph -Text 'Luồng 3 – Thu hồi (checkin): Người có quyền chọn tài sản đã cấp phát → hệ thống xử lý thu hồi, giải phóng đối tượng gán và cập nhật vị trí/trạng thái theo request → cập nhật bộ đếm và thời điểm checkin → ghi lịch sử thao tác. Ca checkout/checkin tích hợp hiện có kiểm tra tài sản trở về vị trí kho và các trường gán được xóa.' -Style 'BodyText'))

$content.Add((New-WordParagraph -Text 'PHẦN B. KIẾN TRÚC COMPONENT/CONTAINER VÀ MODULE KIỂM THỬ' -Style 'Heading1'))
$content.Add((New-WordParagraph -Text 'B.1. Sơ đồ kiến trúc logic' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text '[Trình duyệt / API Client]' -Style 'Code'))
$content.Add((New-WordParagraph -Text '                ↓ HTTP' -Style 'Code'))
$content.Add((New-WordParagraph -Text '[Laravel: routes → middleware/auth → Web Controllers | API Controllers]' -Style 'Code'))
$content.Add((New-WordParagraph -Text '                ↓                         ↓' -Style 'Code'))
$content.Add((New-WordParagraph -Text '[Blade / Livewire views]     [Policies + API Transformers]' -Style 'Code'))
$content.Add((New-WordParagraph -Text '                └──────────┬──────────────┘' -Style 'Code'))
$content.Add((New-WordParagraph -Text '                           ↓' -Style 'Code'))
$content.Add((New-WordParagraph -Text '[Actions/Services → Eloquent Models → Observers/Events/Action logs]' -Style 'Code'))
$content.Add((New-WordParagraph -Text '                           ↓' -Style 'Code'))
$content.Add((New-WordParagraph -Text '[CSDL cấu hình cho ứng dụng]   [Tệp/queue/cache theo cấu hình triển khai]' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'Kiểm thử K16: runner Node.js → MySQL 8.0.36 container tạm → PHPUnit/Laravel → dừng container' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'Đây là sơ đồ component logic rút gọn dựa trên mã nguồn đã xem, không khẳng định hạ tầng production luôn gồm Nginx, Redis hoặc một kiểu container ứng dụng cụ thể. Những dịch vụ phụ thuộc được bật tùy cấu hình triển khai.' -Style 'Callout'))

$content.Add((New-WordParagraph -Text 'B.2. Các module liên quan trực tiếp đến kiểm thử' -Style 'Heading2'))
$content.Add((New-WordTable -Headers @('Module', 'Thành phần liên quan', 'Rủi ro/điểm kiểm thử') -Rows @(
    [pscustomobject]@{ Cells = @('Checkout / Checkin tài sản', 'Asset model, controllers, routes, quyền checkout/checkin, observer/action logs.', 'Tài sản không sẵn sàng, thiếu quyền/đối tượng, gán sai người/vị trí, cập nhật bộ đếm hoặc nhật ký không nhất quán.') },
    [pscustomobject]@{ Cells = @('Người dùng và RBAC', 'User, Group, Gate, Policies, Passport/API middleware.', 'Truy cập trái quyền, xác thực thiếu/không hợp lệ, lộ API hoặc tạo token trái phép.') },
    [pscustomobject]@{ Cells = @('Đa công ty/FMCS', 'CompanyableTrait, global scopes, liên kết công ty/địa điểm/người dùng.', 'Lộ dữ liệu chéo công ty hoặc cho phép checkout sai phạm vi.') },
    [pscustomobject]@{ Cells = @('Test harness K16', 'tests/testcontainers/run.mjs, PHPUnit, MySQL container, GitHub Actions workflow.', 'Docker/PHP/Node không sẵn sàng, cấu hình DB sai, lỗi dọn dẹp container hoặc CI không bao phủ đủ suite.') }
)))
$content.Add((New-WordParagraph -Text 'B.3. Phân quyền, đa công ty và dấu vết kiểm toán' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text 'AuthServiceProvider đăng ký ánh xạ Policy và Gate; các model công ty áp dụng CompanyableScope/CompanyableTrait. Asset kế thừa model miền dùng trait như Loggable, Requestable, HasOrders và CompanyableTrait. AssetObserver xử lý một số thay đổi và ghi action log. Vì các thành phần này tác động xuyên request và dữ liệu, kiểm thử cần xác nhận cả phản hồi API lẫn trạng thái DB/nhật ký sau thao tác, thay vì chỉ kiểm tra mã HTTP.' -Style 'BodyText'))

$content.Add((New-WordParagraph -Text 'PHẦN C. MÔI TRƯỜNG, CÀI ĐẶT, DỮ LIỆU MẪU VÀ BẰNG CHỨNG' -Style 'Heading1'))
$content.Add((New-WordParagraph -Text 'C.1. Môi trường và phiên bản' -Style 'Heading2'))
$content.Add((New-WordTable -Headers @('Hạng mục', 'Yêu cầu/giá trị đối chiếu', 'Trạng thái xác nhận') -Rows @(
    [pscustomobject]@{ Cells = @('PHP / Laravel', 'PHP >= 8.2; Laravel 12.x', 'Đã đối chiếu manifest; chưa đo phiên bản runtime của máy triển khai.') },
    [pscustomobject]@{ Cells = @('Composer', 'Cần cài Composer 2.x tương thích dự án', 'Phiên bản local cần ghi lại trong biên bản chạy.') },
    [pscustomobject]@{ Cells = @('Node.js', '>=18 cho runner theo kiểm tra trong run.mjs; CI dùng Node 22', 'Đã đối chiếu mã runner/workflow.') },
    [pscustomobject]@{ Cells = @('Docker', 'Docker Engine/Desktop hoạt động để Testcontainers khởi chạy MySQL', 'Bắt buộc cho K16; trạng thái daemon trên máy nhóm chưa có bằng chứng.') },
    [pscustomobject]@{ Cells = @('Cơ sở dữ liệu ứng dụng', 'Cấu hình theo .env và hệ quản trị được dự án hỗ trợ', 'Chỉ runner K16 được xác nhận cấu hình MySQL 8.0.36.') },
    [pscustomobject]@{ Cells = @('CI', 'GitHub Actions ubuntu-latest; PHP 8.2; Node 22', 'Workflow được khai báo; kết quả chạy cần đính kèm.') }
)))

$content.Add((New-WordParagraph -Text 'C.2. Hướng dẫn khởi chạy local trên Windows/PowerShell' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text '1) Clone đúng repository, checkout commit/tag đã được nhóm chốt (hiện SHA/tag chưa được xác minh trong báo cáo).' -Style 'BodyText'))
$content.Add((New-WordParagraph -Text '2) Cài dependency: composer install; npm install.' -Style 'Code'))
$content.Add((New-WordParagraph -Text '3) Tạo cấu hình: Copy-Item .env.example .env; điền DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME và DB_PASSWORD trỏ tới cơ sở dữ liệu phát triển dùng riêng.' -Style 'Code'))
$content.Add((New-WordParagraph -Text '4) Tạo APP_KEY: php artisan key:generate. Chạy migration: php artisan migrate. Chỉ chạy seed trên database dùng thử: php artisan db:seed; nếu cần bộ dữ liệu đồ án riêng, dùng php artisan db:seed --class=SystemSampleDataSeeder sau khi xác nhận kết nối đúng DB thử nghiệm.' -Style 'Code'))
$content.Add((New-WordParagraph -Text '5) Khởi chạy tài nguyên giao diện khi cần: npm run dev. Khởi chạy web: php artisan serve --host=127.0.0.1 --port=8000; mở http://127.0.0.1:8000.' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'Không chạy migrate/seed trên database production hoặc database chứa dữ liệu cần giữ. Không xem `php artisan serve` là cấu hình production. Các yêu cầu bổ sung như extension PHP, SMTP, LDAP, S3 hay queue phụ thuộc cấu hình tính năng được bật.' -Style 'Callout'))

$content.Add((New-WordParagraph -Text 'C.3. Hướng dẫn chạy kiểm thử K16 Testcontainers' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text 'Điều kiện: Docker daemon đang chạy; Node.js >=18; PHP CLI có pdo_mysql và các extension dự án cần; vendor/ được cài qua Composer; dependency Node đã cài. Runner sử dụng .env.testing nếu có, hoặc sao chép .env.testing.example tạm thời và xóa bản sao do nó tạo ra sau lượt chạy.' -Style 'BodyText'))
$content.Add((New-WordParagraph -Text 'Lệnh chạy ca checkout/checkin đang được CI chọn:' -Style 'BodyText'))
$content.Add((New-WordParagraph -Text 'npm run test:integration:testcontainers -- tests/Feature/Checkouts/Api/AssetCheckoutTest.php --filter=test_asset_can_be_checked_out_and_checked_back_in_through_api' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'Runner tạo database và mật khẩu ngẫu nhiên, khởi chạy image mysql:8.0.36, sinh cặp khóa RSA Passport trong tiến trình, truyền thông tin qua biến môi trường cho PHPUnit và dừng container trong finally. Nếu PHP không có trong PATH trên Windows, có thể đặt PHP_BINARY tới đường dẫn php.exe trước khi chạy. Không dùng database dùng chung thay cho container nếu mục tiêu là tái lập K16.' -Style 'BodyText'))
$content.Add((New-WordParagraph -Text 'C.4. Seed data và cách ly dữ liệu test' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text 'Dự án có SystemSampleDataSeeder.php dành cho dữ liệu minh họa nghiệp vụ. TestCase tự chuẩn bị settings và adapter cần thiết; các test nghiệp vụ sử dụng factories để tạo fixture. Runner Testcontainers tạo database mới cho mỗi lần gọi runner, còn LazilyRefreshDatabase hỗ trợ làm mới DB trong vòng đời test theo Laravel. Không nên nhầm seeder demo với fixture mà CI K16 hiện đang dùng.' -Style 'BodyText'))
$content.Add((New-WordParagraph -Text 'C.5. Ba luồng demo và bằng chứng nghiệm thu cần thu thập' -Style 'Heading2'))
$content.Add((New-WordTable -Headers @('Luồng demo', 'Thao tác cần trình diễn', 'Bằng chứng phải lưu') -Rows @(
    [pscustomobject]@{ Cells = @('Xác thực/RBAC', 'Đăng nhập bằng tài khoản thử nghiệm; đối chiếu menu hoặc endpoint khi quyền được cấp/không cấp.', 'Ảnh trang đăng nhập/dashboard; tài khoản đã che thông tin nhạy cảm; log/response cho trường hợp được phép và bị từ chối.') },
    [pscustomobject]@{ Cells = @('Checkout tài sản', 'Chọn tài sản sẵn sàng; cấp cho user/location hợp lệ; kiểm tra trạng thái và người nhận.', 'Ảnh trước/sau; Asset ID đã ẩn nếu cần; bản ghi action_logs; timestamp và commit chạy.') },
    [pscustomobject]@{ Cells = @('Checkin tài sản', 'Thu hồi tài sản đã checkout; xác nhận giải phóng đối tượng gán và vị trí lưu kho.', 'Ảnh trước/sau; action log checkin; kết quả test và log ứng dụng.') }
)))
$content.Add((New-WordParagraph -Text 'Tại thời điểm biên soạn chưa có ảnh chụp màn hình, video demo, log ứng dụng hoặc kết quả CI đính kèm; các dòng trên là checklist thu thập minh chứng, không phải tuyên bố đã nghiệm thu runtime.' -Style 'Callout'))

$content.Add((New-WordParagraph -Text 'PHẦN D. ĐỊNH HƯỚNG KỸ THUẬT KIỂM THỬ NÂNG CAO K16' -Style 'Heading1'))
$content.Add((New-WordParagraph -Text 'D.1. Nguyên lý và lý do lựa chọn' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text 'Testcontainers cấp phát dịch vụ phụ thuộc thật trong container tạm thời theo nhu cầu test. Với Snipe-IT, MySQL thật giúp kiểm tra migrations, kiểu dữ liệu, ràng buộc khóa ngoại, transaction và hành vi SQL gần môi trường MySQL hơn mock hoặc SQLite. Lựa chọn K16 phù hợp với rủi ro dữ liệu quan hệ của checkout/checkin và nhật ký. K16 là môi trường integration; bản thân việc dùng container không tự chứng minh độ bao phủ, tính đúng của mọi test hay khả năng production.' -Style 'BodyText'))
$content.Add((New-WordParagraph -Text 'D.2. Phạm vi đề xuất và ngoài phạm vi' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text 'Trong phạm vi đợt đầu: checkout/checkin API end-to-end; quyền checkout/checkin; kiểm tra đầu vào và trạng thái tài sản; cập nhật các trường gán/bộ đếm; action logs; yêu cầu cấp phát tài sản; chạy lặp lại trên MySQL ephemeral ở local và CI.' -Style 'Bullet'))
$content.Add((New-WordParagraph -Text 'Ngoài phạm vi nghiệm thu giữa kỳ hiện tại: toàn bộ PHPUnit suite trên MySQL; race condition đồng thời nhiều tiến trình; kiểm thử tất cả module license/hardware/RBAC/FMCS; DAST, mutation testing, fuzzing hoặc ngưỡng coverage 80%. Có thể lập kế hoạch bổ sung sau khi test model và tài nguyên CI được thống nhất.' -Style 'Bullet'))
$content.Add((New-WordParagraph -Text 'D.3. Giả định và rủi ro' -Style 'Heading2'))
$content.Add((New-WordTable -Headers @('Rủi ro/giả định', 'Tác động', 'Biện pháp') -Rows @(
    [pscustomobject]@{ Cells = @('Docker daemon/registry không sẵn sàng', 'Không khởi tạo được container hoặc kéo image chậm.', 'Kiểm tra Docker trước chạy; pin image; lưu log và timeout rõ ràng.') },
    [pscustomobject]@{ Cells = @('MySQL container khởi tạo/migration chậm', 'Tăng thời gian CI, gây flaky test.', 'Đo startup/migration/test riêng; tránh retry che lỗi; giới hạn suite theo mục tiêu.') },
    [pscustomobject]@{ Cells = @('Khác biệt cấu hình môi trường và .env.testing', 'Test kết nối nhầm DB hoặc phụ thuộc secret local.', 'Xác nhận biến DB do runner truyền; không ghi credential vào log; giữ env thử nghiệm riêng.') },
    [pscustomobject]@{ Cells = @('Coverage/CI chưa chứng minh đầy đủ', 'Có thể diễn giải quá mức kết quả giữa kỳ.', 'Báo cáo riêng test pass, assertion, coverage và CI run; không ghi “100%” nếu thiếu số liệu.') },
    [pscustomobject]@{ Cells = @('Chưa khóa commit SHA/tag', 'Không tái lập được chính xác phiên bản đã báo cáo.', 'Ghi SHA, tag, branch và trạng thái dirty tại lúc chốt nộp.') }
)))
$content.Add((New-WordParagraph -Text 'D.4. Tiêu chí Pass/Fail đề xuất' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text 'PASS cho ca mục tiêu khi runner khởi tạo DB tạm thành công, migrations/test hoàn tất với exit code 0, các assertion API và trạng thái DB/action log đạt yêu cầu, container được dừng và không có thông tin xác thực bị ghi vào artifact. FAIL nếu thiếu Docker/PHP/schema, assertion sai, test flaky, tiến trình kết thúc khác 0 hoặc tài nguyên test không được dọn dẹp. Mục tiêu coverage (nếu nhóm chọn >=80%) phải ghi rõ phạm vi class/branch và đính kèm báo cáo đo thực tế; chưa xem đó là kết quả hiện tại.' -Style 'BodyText'))

$content.Add((New-WordParagraph -Text 'PHẦN E. TEST MODEL, SCENARIO, CASE VÀ INVARIANT' -Style 'Heading1'))
$content.Add((New-WordParagraph -Text 'E.1. Tiền điều kiện và mô hình dữ liệu' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text 'Fixture tối thiểu: actor có quyền checkout/checkin; user nhận tài sản; location kho; asset hợp lệ ở trạng thái có thể triển khai và gắn location. Với ca âm, thay đổi một điều kiện tại một thời điểm (thiếu quyền, tài sản không tồn tại, đã checkout, nhận chính nó, thiếu trường mục tiêu). Dùng factory để tránh phụ thuộc seed dữ liệu bên ngoài.' -Style 'BodyText'))
$content.Add((New-WordParagraph -Text 'Invariant sau checkout: tài sản liên kết đúng người nhận; assigned_type/assigned_to nhất quán; checkout_counter tăng một lần; lịch sử phản ánh thao tác thành công. Invariant sau checkin: assigned_to và assigned_type trở về null; checkin_counter tăng; thời điểm checkout/checkin hợp lệ; location quay về kho theo cấu hình fixture; lịch sử ghi nhận thu hồi. Với request bị từ chối, không được phát sinh trạng thái cấp phát thành công hoặc nhật ký thành công sai.' -Style 'BodyText'))
$content.Add((New-WordTable -Headers @('ID', 'Scenario', 'Điều kiện/đầu vào', 'Kỳ vọng') -Rows @(
    [pscustomobject]@{ Cells = @('K16-CO-01', 'Checkout rồi checkin khép kín qua API', 'Actor đủ quyền; asset sẵn sàng; user nhận hợp lệ.', 'API thành công; asset gán đúng; checkout/checkin counter và timestamp đúng; sau checkin asset không còn assigned và trở về location kho.') },
    [pscustomobject]@{ Cells = @('K16-CO-02', 'Thiếu quyền checkout', 'User thường gọi endpoint checkout.', 'Bị từ chối; không thay đổi assignment và không ghi nhận checkout thành công.') },
    [pscustomobject]@{ Cells = @('K16-CO-03', 'Tài sản không tồn tại', 'Gửi ID không tồn tại.', 'Request lỗi theo hợp đồng API; DB không đổi.') },
    [pscustomobject]@{ Cells = @('K16-CO-04', 'Tài sản đã được cấp phát', 'Asset assigned; actor thử cấp cho người khác.', 'Không cho checkout lần hai; assignment hiện tại được giữ nguyên.') },
    [pscustomobject]@{ Cells = @('K16-CO-05', 'Không cho cấp tài sản cho chính nó', 'Target type asset, assigned_asset trùng asset đang thao tác.', 'Request bị từ chối; dữ liệu không đổi.') },
    [pscustomobject]@{ Cells = @('K16-CO-06', 'Thiếu target', 'Payload checkout không có đối tượng nhận hợp lệ.', 'Validation/error theo hợp đồng endpoint; không phát event checkout thành công.') },
    [pscustomobject]@{ Cells = @('K16-REQ-01', 'Yêu cầu tài sản requestable', 'User gửi yêu cầu asset requestable; đối chứng asset non-requestable.', 'Yêu cầu hợp lệ được tạo; yêu cầu không hợp lệ bị từ chối; action logs đúng loại sự kiện.') }
)))
$content.Add((New-WordParagraph -Text 'Các scenario trên phản ánh test hiện có và invariant cần quan sát; mã test trong repository có thể trả cấu trúc status/message cụ thể khác với mã HTTP chuẩn. Cần giữ assertion khớp hợp đồng API thật, không áp một mã HTTP giả định.' -Style 'Callout'))

$content.Add((New-WordParagraph -Text 'PHẦN F. MÃ NGUỒN, HARNESS VÀ HƯỚNG DẪN TÁI CHẠY' -Style 'Heading1'))
$content.Add((New-WordParagraph -Text 'F.1. Tệp liên quan trong repository' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text 'tests/testcontainers/run.mjs – khởi tạo/dọn MySQL container, tạo thông tin DB ngẫu nhiên và gọi PHPUnit.' -Style 'Bullet'))
$content.Add((New-WordParagraph -Text 'tests/Feature/Checkouts/Api/AssetCheckoutTest.php – các ca API checkout, quyền, validation, request và ca checkout/checkin khép kín.' -Style 'Bullet'))
$content.Add((New-WordParagraph -Text 'tests/TestCase.php – chuẩn bị môi trường test, settings và guard tránh chạy khi thiếu .env.testing.' -Style 'Bullet'))
$content.Add((New-WordParagraph -Text '.env.testing.example – giá trị cấu hình mẫu cho môi trường thử nghiệm.' -Style 'Bullet'))
$content.Add((New-WordParagraph -Text '.github/workflows/testcontainers.yml – workflow tích hợp chạy ca checkout/checkin mục tiêu.' -Style 'Bullet'))
$content.Add((New-WordParagraph -Text 'F.2. Lệnh tái chạy' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text 'composer install' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'npm install' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'npm run test:integration:testcontainers -- tests/Feature/Checkouts/Api/AssetCheckoutTest.php --filter=test_asset_can_be_checked_out_and_checked_back_in_through_api' -Style 'Code'))
$content.Add((New-WordParagraph -Text 'Lưu lại: thời điểm chạy, commit SHA, OS, phiên bản PHP/Composer/Node/Docker, image MySQL, lệnh nguyên văn, exit code, stdout/stderr, PHPUnit log và artifact CI. Không đính kèm password, private key, .env hoặc dữ liệu người dùng thật.' -Style 'BodyText'))

$content.Add((New-WordParagraph -Text 'PHẦN G. LOG, METRICS VÀ TÌNH TRẠNG DEFECT/COVERAGE' -Style 'Heading1'))
$content.Add((New-WordParagraph -Text 'G.1. Tình trạng đối chiếu tại thời điểm lập báo cáo' -Style 'Heading2'))
$content.Add((New-WordTable -Headers @('Hạng mục', 'Bằng chứng mã nguồn', 'Kết quả báo cáo') -Rows @(
    [pscustomobject]@{ Cells = @('Harness Testcontainers', 'run.mjs dùng MySqlContainer(mysql:8.0.36), truyền cấu hình cho PHPUnit và dừng container trong finally.', 'Đã xác nhận có triển khai trong repository.') },
    [pscustomobject]@{ Cells = @('Test nghiệp vụ trọng điểm', 'Feature test chứa test_asset_can_be_checked_out_and_checked_back_in_through_api.', 'Đã xác nhận ca kiểm thử tồn tại; chưa có log chạy đính kèm ở báo cáo này.') },
    [pscustomobject]@{ Cells = @('Tự động hóa CI', 'Workflow testcontainers.yml chọn chạy một test method trên push master/develop và pull request.', 'Đã xác nhận cấu hình workflow; trạng thái run/green chưa được xác minh.') },
    [pscustomobject]@{ Cells = @('Toàn bộ suite/coverage', 'Workflow hiện tại không chạy toàn bộ suite và chưa cấu hình báo cáo coverage.', 'Chưa có số đo; không tuyên bố đạt ngưỡng coverage.') },
    [pscustomobject]@{ Cells = @('Defect', 'Chưa có defect log/triage được cung cấp trong hồ sơ này.', 'Chưa thể kết luận không có defect; cần ghi defect thực tế sau demo/chạy test.') }
)))
$content.Add((New-WordParagraph -Text 'G.2. Metrics cần ghi ở lần chạy nghiệm thu' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text 'Số test chạy/pass/fail/skip; số assertion; thời gian pull/startup MySQL; thời gian migration; thời gian test; tổng thời gian; tỷ lệ lặp ổn định qua ít nhất 3 lượt; kết quả dọn container; code coverage theo class/branch nếu có; lỗi phát hiện, mức độ, bước tái hiện, nguyên nhân gốc và commit sửa.' -Style 'BodyText'))
$content.Add((New-WordParagraph -Text 'G.3. Mẫu RCA ngắn' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text 'Defect ID / mức độ: [bổ sung] | Môi trường và SHA: [bổ sung] | Bước tái hiện: [bổ sung] | Kết quả mong đợi/thực tế: [bổ sung] | Log/assertion: [đính kèm đã loại bỏ bí mật] | Nguyên nhân gốc: [bổ sung] | Phạm vi ảnh hưởng: [bổ sung] | Hướng sửa và test hồi quy: [bổ sung].' -Style 'BodyText'))

$content.Add((New-WordParagraph -Text 'PHẦN H. COMMIT/TAG, TIẾN ĐỘ VÀ TÁI LẬP TRÊN MÁY MỚI' -Style 'Heading1'))
$content.Add((New-WordParagraph -Text 'H.1. Khóa phiên bản nộp' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text 'Repository tham chiếu: Bee-Max-Ne/Snipe-IT. Tag v7.x chỉ là nhãn nhánh phiên bản nêu trong đề cương, chưa phải tag/commit bất biến đã xác minh. Commit SHA, branch, tag và trạng thái working tree: [NHÓM BỔ SUNG SAU KHI CHỐT]. Có thể thu thập bằng `git rev-parse HEAD`, `git describe --tags --always` và `git status --short` tại bản sao dự án dùng để demo.' -Style 'BodyText'))
$content.Add((New-WordParagraph -Text 'H.2. Tái lập trên máy mới' -Style 'Heading2'))
$content.Add((New-WordParagraph -Text '1) Cài Git, PHP >=8.2 và extension theo composer/platform requirements, Composer, Node.js >=18, Docker Desktop/Engine và bật daemon.' -Style 'Bullet'))
$content.Add((New-WordParagraph -Text '2) Clone repository; checkout SHA đã khóa; lưu lại `git rev-parse HEAD` và đảm bảo working tree sạch.' -Style 'Bullet'))
$content.Add((New-WordParagraph -Text '3) Cài dependency Composer/Node; chuẩn bị .env.testing từ example nếu runner chưa tạo; không đưa secrets vào version control.' -Style 'Bullet'))
$content.Add((New-WordParagraph -Text '4) Chạy đúng lệnh K16 ở Phần F; chờ test hoàn tất; xác nhận exit code; ghi phiên bản môi trường và lưu log đã lọc bí mật.' -Style 'Bullet'))
$content.Add((New-WordParagraph -Text '5) Xác nhận runner dừng container sau test; chạy lại tối thiểu 3 lượt để đánh giá tính ổn định; đính kèm URL/run ID của CI nếu có.' -Style 'Bullet'))

$content.Add((New-WordParagraph -Text 'H.3. Mốc tiến độ và phân công đề xuất' -Style 'Heading2'))
$content.Add((New-WordTable -Headers @('Mốc', 'Mục tiêu', 'Đầu ra/Trạng thái') -Rows @(
    [pscustomobject]@{ Cells = @('18/09 – đăng ký đề tài', 'Đăng ký đề tài, khóa repo/SHA và tạo bảng công việc.', 'Hạn theo đề cương; SHA/Trello/Jira cần nhóm điền bằng chứng.') },
    [pscustomobject]@{ Cells = @('20/10 – giữa kỳ', 'Demo local/Docker, kiến trúc, onboarding, seed data, 3 luồng cơ bản và kế hoạch test.', 'Trọng tâm báo cáo này; ảnh/log demo và kết quả runtime còn cần đính kèm.') },
    [pscustomobject]@{ Cells = @('Tháng 12 – cuối kỳ', 'Demo automation, báo cáo tổng kết trước một tuần, peer review.', 'Ngày thi chính xác cần xác nhận với giảng viên; mở rộng suite và metrics.') }
)))
$content.Add((New-WordParagraph -Text 'Phân công cần được xác nhận bởi nhóm. README hiện liệt kê các thành viên Phạm Nguyễn Ngọc Phước (2312718), Nguyễn Văn Quốc (2312729), Nguyễn Đình Thạch (2314506) và Nguyễn Thành Minh (2312691), nhưng vai trò trong đề cương gửi kèm chưa hoàn chỉnh/khác cách mô tả ở README. Đề xuất chia việc: (1) khóa phạm vi, điều phối và review; (2) test API checkout/checkin; (3) harness/CI và môi trường; (4) RBAC/FMCS, log/metrics và peer review. Nhóm cần gán tên cụ thể, người duyệt và ngày hoàn thành trước khi nộp.' -Style 'BodyText'))

$content.Add((New-WordParagraph -Text 'KẾT LUẬN VÀ CÁC VIỆC CẦN HOÀN THIỆN TRƯỚC KHI NỘP' -Style 'Heading1'))
$content.Add((New-WordParagraph -Text 'Repository đã có nền tảng phù hợp để trình bày kiến trúc Laravel và một luồng kiểm thử integration K16 có thể tái lập: runner MySQL ephemeral, test checkout/checkin và workflow CI mục tiêu. Báo cáo không khẳng định toàn hệ thống đã chạy trên máy nhóm, toàn bộ suite đã pass, coverage đạt 80% hay CI đã xanh, vì chưa có bằng chứng runtime tương ứng trong hồ sơ được kiểm tra.' -Style 'BodyText'))
$content.Add((New-WordParagraph -Text 'Checklist cần hoàn tất: chốt SHA/tag và trạng thái working tree; ghi phiên bản môi trường thực tế; chạy và lưu log lệnh K16; đính kèm run ID/CI; quay/chụp 3 luồng demo; đưa số test/assertion/thời gian/coverage thực tế; hoàn thiện tên nhóm, vai trò và xác nhận lịch thi; rà soát, loại bỏ mọi credential/dữ liệu cá nhân khỏi báo cáo.' -Style 'Bullet'))

$stylesXml = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Aptos" w:hAnsi="Aptos" w:eastAsia="Aptos"/><w:sz w:val="22"/></w:rPr></w:rPrDefault><w:pPrDefault><w:pPr><w:spacing w:after="120" w:line="276" w:lineRule="auto"/></w:pPr></w:pPrDefault></w:docDefaults>
  <w:style w:type="paragraph" w:default="1" w:styleId="BodyText"><w:name w:val="Body Text"/><w:qFormat/><w:pPr><w:spacing w:after="120" w:line="276" w:lineRule="auto"/></w:pPr><w:rPr><w:rFonts w:ascii="Aptos" w:hAnsi="Aptos"/><w:sz w:val="22"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:basedOn w:val="BodyText"/><w:pPr><w:jc w:val="center"/><w:spacing w:before="240" w:after="240"/></w:pPr><w:rPr><w:b/><w:color w:val="1F4E78"/><w:sz w:val="36"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Subtitle"><w:name w:val="Subtitle"/><w:basedOn w:val="BodyText"/><w:pPr><w:jc w:val="center"/><w:spacing w:after="180"/></w:pPr><w:rPr><w:color w:val="404040"/><w:sz w:val="24"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="BodyText"/><w:next w:val="BodyText"/><w:qFormat/><w:pPr><w:keepNext/><w:outlineLvl w:val="0"/><w:spacing w:before="300" w:after="160"/></w:pPr><w:rPr><w:b/><w:color w:val="1F4E78"/><w:sz w:val="28"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><w:basedOn w:val="BodyText"/><w:next w:val="BodyText"/><w:qFormat/><w:pPr><w:keepNext/><w:outlineLvl w:val="1"/><w:spacing w:before="220" w:after="100"/></w:pPr><w:rPr><w:b/><w:color w:val="365F91"/><w:sz w:val="24"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Bullet"><w:name w:val="Bullet"/><w:basedOn w:val="BodyText"/><w:pPr><w:ind w:left="360" w:hanging="180"/></w:pPr></w:style>
  <w:style w:type="paragraph" w:styleId="Code"><w:name w:val="Code"/><w:basedOn w:val="BodyText"/><w:pPr><w:spacing w:after="40" w:line="240" w:lineRule="auto"/></w:pPr><w:rPr><w:rFonts w:ascii="Consolas" w:hAnsi="Consolas"/><w:sz w:val="18"/><w:color w:val="333333"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Callout"><w:name w:val="Callout"/><w:basedOn w:val="BodyText"/><w:pPr><w:ind w:left="180" w:right="180"/><w:spacing w:before="120" w:after="180"/></w:pPr><w:rPr><w:i/><w:color w:val="7F6000"/><w:sz w:val="20"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="TableText"><w:name w:val="Table Text"/><w:basedOn w:val="BodyText"/><w:pPr><w:spacing w:after="40" w:line="240" w:lineRule="auto"/></w:pPr><w:rPr><w:sz w:val="18"/></w:rPr></w:style>
</w:styles>
'@

$documentXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:body>$($content -join "`n")
    <w:sectPr><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="1080" w:right="900" w:bottom="1080" w:left="900" w:header="540" w:footer="540" w:gutter="0"/></w:sectPr>
  </w:body>
</w:document>
"@

$contentTypesXml = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
'@
$packageRelsXml = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
'@
$documentRelsXml = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
'@
$timestamp = [DateTime]::UtcNow.ToString("yyyy-MM-ddTHH:mm:ssZ")
$corePropsXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:title>Báo cáo giữa kỳ Snipe-IT K16 Testcontainers</dc:title>
  <dc:creator>Nhóm QA Team</dc:creator>
  <cp:lastModifiedBy>Nhóm QA Team</cp:lastModifiedBy>
  <dcterms:created xsi:type="dcterms:W3CDTF">$timestamp</dcterms:created>
  <dcterms:modified xsi:type="dcterms:W3CDTF">$timestamp</dcterms:modified>
</cp:coreProperties>
"@
$appPropsXml = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">
  <Application>Microsoft Word</Application>
  <Company>Nhóm QA Team</Company>
</Properties>
'@

try {
    New-Item -Path $reportRoot -ItemType Directory -Force | Out-Null
    New-Item -Path (Join-Path $workPath 'word\_rels'), (Join-Path $workPath 'docProps'), (Join-Path $workPath '_rels') -ItemType Directory -Force | Out-Null

    [System.IO.File]::WriteAllText((Join-Path $workPath '[Content_Types].xml'), $contentTypesXml, $utf8)
    [System.IO.File]::WriteAllText((Join-Path $workPath '_rels\.rels'), $packageRelsXml, $utf8)
    [System.IO.File]::WriteAllText((Join-Path $workPath 'word\document.xml'), $documentXml, $utf8)
    [System.IO.File]::WriteAllText((Join-Path $workPath 'word\styles.xml'), $stylesXml, $utf8)
    [System.IO.File]::WriteAllText((Join-Path $workPath 'word\_rels\document.xml.rels'), $documentRelsXml, $utf8)
    [System.IO.File]::WriteAllText((Join-Path $workPath 'docProps\core.xml'), $corePropsXml, $utf8)
    [System.IO.File]::WriteAllText((Join-Path $workPath 'docProps\app.xml'), $appPropsXml, $utf8)

    if (Test-Path -LiteralPath $temporaryOutputPath) {
        Remove-Item -LiteralPath $temporaryOutputPath -Force
    }

    Add-Type -AssemblyName System.IO.Compression.FileSystem
    [System.IO.Compression.ZipFile]::CreateFromDirectory(
        $workPath,
        $temporaryOutputPath,
        [System.IO.Compression.CompressionLevel]::Optimal,
        $false
    )
    Move-Item -LiteralPath $temporaryOutputPath -Destination $outputPath -Force

    Write-Host "Đã tạo báo cáo Word: $outputPath"
} finally {
    if (Test-Path -LiteralPath $temporaryOutputPath) {
        Remove-Item -LiteralPath $temporaryOutputPath -Force
    }
    if (Test-Path -LiteralPath $workPath) {
        Remove-Item -LiteralPath $workPath -Recurse -Force
    }
}
