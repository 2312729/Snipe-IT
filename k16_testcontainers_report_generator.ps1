$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$reportRoot = Join-Path $root 'reports/k16-testcontainers'
New-Item -Path $reportRoot -ItemType Directory -Force | Out-Null

$members = @(
    @{
        Name = 'Thành viên 1';
        FileName = 'K16_ThanhVien_1_Nghiên_Cứu_kiến_trúc.docx';
        Title = 'Kế hoạch triển khai K16 Testcontainers - Thành viên 1';
        Summary = 'Nghiên cứu tổng quan, phạm vi, mục tiêu và lợi ích của Testcontainers trong hệ thống Snipe-IT.';
        Body = @(
            '1. Mục tiêu và phạm vi triển khai',
            ' - Nghiên cứu cách Testcontainers cung cấp môi trường kiểm thử tự động cho MySQL/MariaDB bằng Docker.',
            ' - Thiết lập cơ chế tái tạo môi trường test ở mức dự án mà không phụ thuộc vào cấu hình cứng trên máy local.',
            ' - Đảm bảo các module chính như authentication, asset lifecycle và API có thể được kiểm thử theo kịch bản tương tự production.',
            '2. Lợi ích dự án',
            ' - Tăng tính nhất quán giữa môi trường dev, CI/CD và local.',
            ' - Giảm rủi ro sai lệch schema và config khi chạy migration trên hệ thống thật.',
            ' - Cho phép mô phỏng DB, queue, cache và service phụ thuộc trong môi trường cô lập.',
            '3. Kết luận',
            ' - Testcontainers là lựa chọn phù hợp cho Snipe-IT khi cần kiểm thử integration mạnh hơn unit test thuần túy.'
        )
    },
    @{
        Name = 'Thành viên 2';
        FileName = 'K16_ThanhVien_2_Thiết_kế_môi_trường.docx';
        Title = 'Kế hoạch triển khai K16 Testcontainers - Thành viên 2';
        Summary = 'Thiết kế kiến trúc Testcontainers cho kiểm thử tích hợp Snipe-IT, cách cấu hình Laravel kết nối database động, quy trình khởi tạo và dọn dẹp môi trường.';
        Body = @(
            '1. Mục tiêu và phạm vi',
            ' - Cung cấp database dùng riêng cho kiểm thử tích hợp Snipe-IT, được tạo mới và hủy tự động bởi Testcontainers.',
            ' - Giữ dữ liệu kiểm thử tách biệt hoàn toàn với database local, staging và production.',
            ' - Giai đoạn đầu chỉ khởi chạy dịch vụ cần thiết; queue, cache và dịch vụ ngoài chỉ thêm container khi có test yêu cầu.',
            '2. Kiến trúc môi trường',
            ' - Runner hiện có tại tests/testcontainers/run.mjs dùng Node.js và package @testcontainers/mysql để khởi tạo database MySQL cho PHPUnit/Laravel.',
            ' - Image hiện được ghim là mysql:8.0.36, không dùng tag latest; cần đối chiếu phiên bản này với MySQL của môi trường mục tiêu trước khi chốt cấu hình.',
            ' - Mỗi lần chạy tạo tên database ngẫu nhiên dạng snipeit_test_<id>, user snipeit_test và mật khẩu ngẫu nhiên; không dùng thông tin xác thực của môi trường thật.',
            ' - Chờ database báo sẵn sàng trước khi Laravel chạy migration; kiểm tra trạng thái bằng health check hoặc truy vấn kết nối.',
            ' - Testcontainers cấp host và cổng ánh xạ động. Khi test runner chạy trên máy host, Laravel phải dùng đúng host/cổng do container trả về, không mặc định DB_HOST=127.0.0.1.',
            ' - Nếu test runner cũng chạy trong Docker, kết nối qua tên dịch vụ/container và cổng nội bộ trên cùng network; không dùng cổng ánh xạ dành cho host.',
            ' - Runner hiện truyền container.getHost() vào DB_HOST và container.getMappedPort(3306) vào DB_PORT trong môi trường của tiến trình PHPUnit; APP_ENV=testing và TESTCONTAINERS_MYSQL=1 cũng được đặt trước khi chạy test.',
            '3. Cấu hình Laravel cho test',
            ' - Dùng DB_CONNECTION=mysql; MariaDB vẫn kết nối qua driver MySQL của PDO/Laravel.',
            ' - Đặt APP_ENV=testing và APP_DEBUG=true trong môi trường test; không nạp thông tin xác thực của môi trường thật.',
            ' - Truyền DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME và DB_PASSWORD từ thông tin kết nối container trước khi khởi tạo ứng dụng Laravel.',
            ' - Dùng APP_KEY dành riêng cho test và APP_URL cục bộ; giữ các giá trị này ngoài cấu hình production.',
            ' - Cấu hình môi trường trước khi bootstrap Laravel hoặc tải config cache để bảo đảm ứng dụng nhận đúng endpoint động.',
            ' - Tạo database/schema riêng cho lần chạy; không trỏ test tới database dùng chung.',
            '4. Quy trình chạy kiểm thử',
            ' - Cài dependencies trước bằng npm install và composer install; bảo đảm Docker daemon đang chạy.',
            ' - Chạy toàn bộ PHPUnit thông qua runner bằng lệnh: npm run test:integration:testcontainers.',
            ' - Chạy riêng test checkout tài sản bằng lệnh: npm run test:integration:testcontainers -- tests/Feature/Checkouts/Api/AssetCheckoutTest.php.',
            ' - Runner chuyển tiếp các tham số sau dấu -- cho PHPUnit; database được kiểm tra kết nối trước khi PHPUnit bắt đầu.',
            ' - Runner dừng container trong finally kể cả khi test lỗi; nếu runner tự tạo .env.testing từ .env.testing.example thì file tạm đó cũng được xóa sau khi chạy.',
            '5. Tích hợp CI và kiểm soát rủi ro',
            ' - CI runner cần có Docker daemon hoặc quyền truy cập Docker phù hợp; thất bại khi không kết nối được Docker phải báo lỗi rõ ràng, không tự chuyển sang database dùng chung.',
            ' - Pin phiên bản image và cấu hình timeout/retry cho thời gian khởi động để giảm lỗi chập chờn.',
            ' - Giới hạn số container chạy song song theo tài nguyên runner; cô lập database giữa các worker nếu bật PHPUnit parallel testing.',
            ' - Không ghi mật khẩu, dữ liệu production hoặc file .env thật vào log và artifact CI.',
            '6. Kết luận',
            ' - Testcontainers giúp các lần chạy integration test có database cô lập, tái lập được trên máy phát triển và CI.',
            ' - Cấu hình host/cổng phải lấy từ container runtime; chỉ dùng 127.0.0.1 khi đó thực sự là endpoint host được cấp.',
            ' - Có thể mở rộng sang queue/cache sau khi xác định rõ test cần các dịch vụ đó.'
        )
    },
    @{
        Name = 'Thành viên 3';
        FileName = 'K16_ThanhVien_3_Tích_hợp_và_kiểm_thử.docx';
        Title = 'Kế hoạch triển khai K16 Testcontainers - Thành viên 3';
        Summary = 'Tích hợp Testcontainers với Laravel, chạy kiểm thử và đánh giá kết quả thực tế.';
        Body = @(
            '1. Tích hợp với Laravel',
            ' - Runner Node.js tests/testcontainers/run.mjs khởi chạy image mysql:8.0.36; host và cổng được ánh xạ được truyền cho PHPUnit qua DB_HOST và DB_PORT.',
            ' - Database, user và mật khẩu kiểm thử được tạo riêng cho mỗi lần chạy; APP_ENV=testing và TESTCONTAINERS_MYSQL=1 được truyền vào tiến trình test.',
            ' - Runner sinh cặp khóa RSA tạm thời cho Laravel Passport và truyền qua PASSPORT_PRIVATE_KEY/PASSPORT_PUBLIC_KEY; không ghi khóa kiểm thử ra file hoặc dùng trong production.',
            ' - Cấu hình mẫu nằm trong .env.testing.example; runner chỉ tạo .env.testing tạm từ mẫu khi file chưa có và xóa file tạm khi kết thúc.',
            ' - Yêu cầu chạy: npm install, composer install, Docker daemon hoạt động; sau đó dùng npm run test:integration:testcontainers.',
            '2. Kịch bản kiểm thử',
            ' - Test vòng đời mới: Tests\Feature\Checkouts\Api\AssetCheckoutTest::test_asset_can_be_checked_out_and_checked_back_in_through_api.',
            ' - Test tạo asset, người nhận và nhân viên có quyền checkout/checkin; gọi API checkout rồi checkin trên cùng asset qua database MySQL của Testcontainers.',
            ' - Sau checkout, xác nhận API thành công, asset được gán đúng user và checkout_counter tăng; sau checkin, xác nhận asset được thu hồi, checkin_counter tăng và last_checkout/last_checkin được ghi.',
            ' - Chạy riêng test vòng đời bằng lệnh: npm run test:integration:testcontainers -- tests/Feature/Checkouts/Api/AssetCheckoutTest.php --filter=test_asset_can_be_checked_out_and_checked_back_in_through_api.',
            ' - Có thể mở rộng kiểm thử checkin, quyền checkout, asset không sẵn sàng và các endpoint API khác bằng các test hiện có trong cùng suite.',
            '3. Đánh giá hiệu quả',
            ' - Lần chạy ngày 10/10/2026 trước khi bổ sung test vòng đời mới: suite AssetCheckoutTest.php có 29 test, 134 assertions, 1 test incomplete; PHPUnit không báo failure hoặc error.',
            ' - Thời gian lần chạy trước ghi nhận là 5 phút 24.708 giây; môi trường PHPUnit hiển thị PHP 8.2.12 và PHPUnit 11.5.55.',
            ' - Xác minh lại ngày 10/10/2026 bằng lệnh npm run test:integration:testcontainers -- tests/Feature/Checkouts/Api/AssetCheckoutTest.php --filter=test_asset_can_be_checked_out_and_checked_back_in_through_api: 1 test, 11 assertions, pass, không có failure/error; thời gian 1 phút 56.240 giây.',
            ' - Kết quả xác minh mới dùng PHPUnit 11.5.55 và PHP 8.2.12; xác nhận test vòng đời checkout rồi checkin chạy qua MySQL Testcontainers.',
            ' - Kết quả của file AssetCheckoutTest.php không đại diện cho toàn bộ suite Unit và Feature; cần lưu ý test incomplete khi đánh giá.',
            '4. Kết luận',
            ' - Tích hợp Testcontainers là bước đúng để nâng chất lượng kiểm thử integration của Snipe-IT.'
        )
    },
    @{
        Name = 'Thành viên 4';
        FileName = 'K16_ThanhVien_4_Đánh_giá_rủi_ro.docx';
        Title = 'Kế hoạch triển khai K16 Testcontainers - Thành viên 4';
        Summary = 'Đánh giá rủi ro, chỉ số thành công, ưu nhược điểm và kế hoạch triển khai thực tế.';
        Body = @(
            '1. Rủi ro và giải pháp',
            ' - Docker daemon không hoạt động hoặc runner không truy cập được Docker: Testcontainers phải làm test thất bại rõ ràng; không chuyển sang database local/dùng chung. CI dùng runner ubuntu-latest có Docker.',
            ' - Lộ dữ liệu kiểm thử: database và mật khẩu được tạo ngẫu nhiên cho mỗi lượt; khóa RSA Passport chỉ truyền trong môi trường tiến trình PHPUnit; không dùng thông tin production.',
            ' - Sai khác phiên bản database: runner ghim mysql:8.0.36; cần đối chiếu với database production trước khi tuyên bố tương đương môi trường.',
            ' - Thời gian/tài nguyên: chỉ chạy test checkout-checkin đại diện trong workflow riêng, giới hạn job 15 phút và dọn container sau lượt chạy.',
            '2. Tiêu chí thành công',
            ' - Test checkout-checkin qua API đạt trạng thái PHPUnit OK; lần xác minh local ngày 10/10/2026 đạt 1 test, 11 assertions, không failure/error, thời gian 2 phút 8.481 giây.',
            ' - Runner tạo database/container riêng, truyền host/cổng động và dừng container khi hoàn tất.',
            ' - Workflow .github/workflows/testcontainers.yml được kích hoạt trên pull request và push lên master/develop; điều kiện nghiệm thu CI là job checkout-checkin màu xanh.',
            ' - Môi trường production/demo không bị tác động vì test dùng container, database, thông tin đăng nhập và khóa Passport tạm riêng.',
            '3. Ưu nhược điểm',
            ' - Ưu: MySQL 8.0.36 được ghim phiên bản, database cô lập và cấu hình endpoint động giúp tái lập test; CI tự chạy test đại diện trên pull request.',
            ' - Nhược: cần Docker daemon, tải image và khởi động container làm tăng thời gian; lần test mục tiêu local gần nhất ghi nhận khoảng 2 phút 8 giây.',
            ' - Giới hạn: workflow mới chỉ chạy test checkout-checkin đại diện, chưa thay thế toàn bộ suite MySQL/Unit/Feature và chưa có kết quả CI từ lần chạy workflow.',
            '4. Kế hoạch triển khai',
            ' - Hoàn thành: thiết lập runner Testcontainers, ghim MySQL 8.0.36, tạo dữ liệu kiểm thử ngẫu nhiên/cô lập và truyền biến môi trường vào Laravel.',
            ' - Hoàn thành: thêm test API checkout-checkin và xác minh local đạt 1 test, 11 assertions.',
            ' - Hoàn thành cấu hình: thêm workflow CI riêng chạy test mục tiêu trên pull request và push master/develop.',
            ' - Còn chờ nghiệm thu: xác nhận workflow CI chạy xanh trên GitHub; nếu thất bại, xem log PHPUnit/artifact, không bỏ qua test hoặc chuyển sang database dùng chung.'
        )
    }
)

function New-DocxPackage {
    param(
        [string]$Folder,
        [string]$Title,
        [string[]]$Body,
        [string]$Summary,
        [string]$Name,
        [string]$FileName
    )

    $folderWord = Join-Path $Folder 'word'
    $folderProps = Join-Path $Folder 'docProps'
    $folderRel = Join-Path $Folder '_rels'

    New-Item -Path $folderWord, $folderProps, $folderRel -ItemType Directory -Force | Out-Null

    $contentTypes = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
'@
    Set-Content -Path (Join-Path $Folder '[Content_Types].xml') -Value $contentTypes -Encoding UTF8

    $rels = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
'@
    Set-Content -Path (Join-Path $folderRel '.rels') -Value $rels -Encoding UTF8

    $coreXml = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:title>__TITLE__</dc:title>
  <dc:creator>__NAME__</dc:creator>
  <cp:lastModifiedBy>__NAME__</cp:lastModifiedBy>
  <dcterms:created xsi:type="dcterms:W3CDTF">2026-10-09T00:00:00Z</dcterms:created>
  <dcterms:modified xsi:type="dcterms:W3CDTF">2026-10-09T00:00:00Z</dcterms:modified>
</cp:coreProperties>
'@
    $coreXml = $coreXml.Replace('__TITLE__', $Title).Replace('__NAME__', $Name)
    Set-Content -Path (Join-Path $folderProps 'core.xml') -Value $coreXml -Encoding UTF8

    $appXml = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>Microsoft Office Word</Application>
  <DocSecurity>0</DocSecurity>
  <ScaleCrop>false</ScaleCrop>
  <HeadingPairs>
    <vt:vector size="2" baseType="variant">
      <vt:variant><vt:lpstr>Title</vt:lpstr></vt:variant>
      <vt:variant><vt:i4>1</vt:i4></vt:variant>
    </vt:vector>
  </HeadingPairs>
  <TitlesOfParts>
    <vt:vector size="1" baseType="lpstr">
      <vt:lpstr>__TITLE__</vt:lpstr>
    </vt:vector>
  </TitlesOfParts>
  <Company>Trường K16</Company>
  <LinksUpToDate>false</LinksUpToDate>
  <SharedDoc>false</SharedDoc>
  <HyperlinksChanged>false</HyperlinksChanged>
  <AppVersion>16.0000</AppVersion>
</Properties>
'@
    $appXml = $appXml.Replace('__TITLE__', $Title)
    Set-Content -Path (Join-Path $folderProps 'app.xml') -Value $appXml -Encoding UTF8

    $paragraphs = @(
        '<w:p><w:r><w:t>Tiêu đề: ' + [System.Security.SecurityElement]::Escape($Title) + '</w:t></w:r></w:p>',
        '<w:p><w:r><w:t>Tác giả: ' + [System.Security.SecurityElement]::Escape($Name) + '</w:t></w:r></w:p>',
        '<w:p><w:r><w:t>Tóm tắt: ' + [System.Security.SecurityElement]::Escape($Summary) + '</w:t></w:r></w:p>'
    )

    foreach ($line in $Body) {
        $paragraphs += '<w:p><w:r><w:t>' + [System.Security.SecurityElement]::Escape($line) + '</w:t></w:r></w:p>'
    }

    $document = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:body>
    __PARAGRAPHS__
    <w:sectPr>
      <w:pgSz w:w="12240" w:h="15840"/>
      <w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/>
    </w:sectPr>
  </w:body>
</w:document>
'@
    $document = $document.Replace('__PARAGRAPHS__', ($paragraphs -join ''))
    Set-Content -Path (Join-Path $folderWord 'document.xml') -Value $document -Encoding UTF8

    $archivePath = Join-Path $reportRoot $FileName
    if (Test-Path $archivePath) {
        Remove-Item $archivePath -Force
    }

    Compress-Archive -Path (Join-Path $Folder '*') -DestinationPath $archivePath -CompressionLevel Optimal -Force | Out-Null
}

foreach ($member in $members) {
    $memberFolder = Join-Path $reportRoot $member.FileName.Replace('.docx', '')
    if (Test-Path $memberFolder) {
        Remove-Item $memberFolder -Recurse -Force
    }
    New-Item -Path $memberFolder -ItemType Directory -Force | Out-Null
    New-DocxPackage -Folder $memberFolder -Title $member.Title -Body $member.Body -Summary $member.Summary -Name $member.Name
    Remove-Item $memberFolder -Recurse -Force
}

Write-Host "Đã tạo 4 file DOCX báo cáo K16 Testcontainers trong: $reportRoot"
