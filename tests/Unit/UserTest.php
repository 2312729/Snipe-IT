<?php

namespace Tests\Unit;

use App\Models\Actionlog;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_first_name_split()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_firstname = 'Natalia';
        $expected_lastname = "Allanovna Romanova-O'Shostakova";
        $user = User::generateFormattedNameFromFullName($fullname, 'firstname');
        $this->assertEquals($expected_firstname, $user['first_name']);
        $this->assertEquals($expected_lastname, $user['last_name']);
    }

    public function test_first_name()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'natalia';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstname');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_first_name_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'natalia@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstname');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_last_name()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'allanovna-romanova-oshostakova';
        $user = User::generateFormattedNameFromFullName($fullname, 'lastname');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_last_name_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'allanovna-romanova-oshostakova@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'lastname');
        $this->assertEquals($expected_username, $user['username'].'@example.com');
    }

    public function test_first_name_dot_last_name()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'natalia.allanovna-romanova-oshostakova';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstname.lastname');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_first_name_dot_last_name_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'natalia.allanovna-romanova-oshostakova@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstname.lastname');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_last_name_first_initial()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'allanovna-romanova-oshostakovan';
        $user = User::generateFormattedNameFromFullName($fullname, 'lastnamefirstinitial');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_last_name_first_initial_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'allanovna-romanova-oshostakovan@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'lastnamefirstinitial');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_first_initial_last_name()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'nallanovna-romanova-oshostakova';
        $user = User::generateFormattedNameFromFullName($fullname, 'filastname');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_first_initial_last_name_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'nallanovna-romanova-oshostakova@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'filastname');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_first_initial_underscore_last_name()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'nallanovna-romanova-oshostakova';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstinitial_lastname');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_first_initial_underscore_last_name_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'nallanovna-romanova-oshostakova@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstinitial_lastname');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_single_name()
    {
        $fullname = 'Natalia';
        $expected_username = 'natalia';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstname_lastname');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_single_name_email()
    {
        $fullname = 'Natalia';
        $expected_email = 'natalia@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstname_lastname');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_first_initial_dot_lastname()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'nallanovna-romanova-oshostakova';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstinitial.lastname');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_first_initial_dot_lastname_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'nallanovna-romanova-oshostakova@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstinitial.lastname');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_last_name_dot_first_initial()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'allanovna-romanova-oshostakova.n';
        $user = User::generateFormattedNameFromFullName($fullname, 'lastname.firstinitial');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_last_name_dot_first_initial_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'allanovna-romanova-oshostakova.n@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'lastname.firstinitial');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_last_name_underscore_first_initial()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'allanovna-romanova-oshostakova_n';
        $user = User::generateFormattedNameFromFullName($fullname, 'lastname_firstinitial');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_last_name_underscore_first_initial_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'allanovna-romanova-oshostakova_n@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'lastname_firstinitial');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_first_name_last_name()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'nataliaallanovna-romanova-oshostakova';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstnamelastname');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_first_name_last_name_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'nataliaallanovna-romanova-oshostakova@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstnamelastname');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_first_name_last_initial()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_username = 'nataliaa';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstnamelastinitial');
        $this->assertEquals($expected_username, $user['username']);
    }

    public function test_first_name_last_initial_email()
    {
        $fullname = "Natalia Allanovna Romanova-O'Shostakova";
        $expected_email = 'nataliaa@example.com';
        $user = User::generateFormattedNameFromFullName($fullname, 'firstnamelastinitial');
        $this->assertEquals($expected_email, $user['username'].'@example.com');
    }

    public function test_link_light_color_attribute()
    {
        // if global branding and user are not set we use the app default defined in the accessor:
        $this->settings->set(['link_light_color' => null]);
        $this->assertEquals(
            '#296282',
            User::factory()->create()->link_light_color
        );

        // nothing set on user we use global branding:
        $this->settings->set(['link_light_color' => '#ff6700']);
        $this->assertEquals(
            '#ff6700',
            User::factory()->create()->link_light_color
        );

        // user has it set we use it:
        $user = User::factory()->create(['link_light_color' => '#61b329']);
        $this->assertEquals(
            '#61b329',
            $user->link_light_color
        );
    }

    public function test_link_dark_color_attribute()
    {
        // if global branding and user are not set we use the app default defined in the accessor:
        $this->settings->set(['link_dark_color' => null]);
        $this->assertEquals(
            '#5fa4cc',
            User::factory()->create()->link_dark_color
        );

        // nothing set on user we use global branding:
        $this->settings->set(['link_dark_color' => '#ff6700']);
        $this->assertEquals(
            '#ff6700',
            User::factory()->create()->link_dark_color
        );

        // user has it set we use it:
        $user = User::factory()->create(['link_dark_color' => '#61b329']);
        $this->assertEquals(
            '#61b329',
            $user->link_dark_color
        );
    }

    public function test_nav_link_color_attribute()
    {
        // if global branding and user are not set we use the app default defined in the accessor:
        $this->settings->set(['nav_link_color' => null]);
        $this->assertEquals(
            '#ffffff',
            User::factory()->create()->nav_link_color
        );

        // nothing set on user we use global branding:
        $this->settings->set(['nav_link_color' => '#ff6700']);
        $this->assertEquals(
            '#ff6700',
            User::factory()->create()->nav_link_color
        );

        // user has it set we use it:
        $user = User::factory()->create(['nav_link_color' => '#61b329']);
        $this->assertEquals(
            '#61b329',
            $user->nav_link_color
        );
    }

    /**
     * WBS Member 4 (Nguyễn Thành Minh - 2312691):
     * TC-SEC-01: Quyền lực tối cao của Superuser (God Mode Bypass).
     * Xác minh tài khoản có thuộc tính permissions->superuser = '1' luôn được cấp phép
     * truy cập vô điều kiện vào mọi section hệ thống (admin, users, assets, reports, v.v.).
     */
    public function test_tc_sec_01_superuser_has_unconditional_access_to_all_sections(): void
    {
        $superUser = User::factory()->superuser()->create();

        $sectionsToTest = [
            'admin',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'assets.view',
            'assets.checkout',
            'assets.checkin',
            'reports.view',
            'settings.general',
        ];

        foreach ($sectionsToTest as $section) {
            $this->assertTrue(
                $superUser->hasAccess($section),
                "Superuser must have unconditional access to [{$section}]"
            );
        }

        $this->assertTrue($superUser->isSuperUser(), 'isSuperUser() must return true for superuser');
        $this->assertTrue($superUser->hasAccess('admin'), 'hasAccess(admin) must return true for superuser');
    }

    /**
     * WBS Member 4 (Nguyễn Thành Minh - 2312691):
     * TC-SEC-02: Kiểm tra quyền phân tầng đơn lẻ (Fine-grained Granular Permission).
     * Xác minh tài khoản có quyền cụ thể chỉ được thực thi đúng quyền đó,
     * các hành vi chưa cấp hoặc đặt '0' phải bị từ chối tuyệt đối.
     */
    public function test_tc_sec_02_user_with_granular_permissions_is_strictly_bounded(): void
    {
        // 1. User được cấu hình quyền granular qua JSON permissions
        $user = User::factory()->create([
            'permissions' => json_encode([
                'users.view' => '1',
                'users.create' => '0',
            ]),
        ]);

        $this->assertTrue($user->hasAccess('users.view'), 'User should have access to users.view');
        $this->assertFalse($user->hasAccess('users.create'), 'Permission set to "0" must be denied');
        $this->assertFalse($user->hasAccess('users.delete'), 'Unassigned permission must be denied');
        $this->assertFalse($user->hasAccess('admin'), 'User must not have admin access');
        $this->assertFalse($user->isSuperUser(), 'User must not be superuser');

        // 2. Kiểm tra với Factory state viewUsers()
        $viewerUser = User::factory()->viewUsers()->create();
        $this->assertTrue($viewerUser->hasAccess('users.view'), 'Viewer must have users.view');
        $this->assertFalse($viewerUser->hasAccess('users.delete'), 'Viewer must not have users.delete');
        $this->assertFalse($viewerUser->hasAccess('users.create'), 'Viewer must not have users.create');
        $this->assertFalse($viewerUser->hasAccess('assets.checkout'), 'Viewer must not have assets.checkout');
    }

    /**
     * WBS Member 4 (Nguyễn Thành Minh - 2312691):
     * TC-SEC-03: Giải quyết xung đột — Phủ quyết đích danh thắng cấp phép từ nhóm (Explicit Deny Overrides Group Allow).
     * Xác minh nguyên tắc an ninh cốt lõi trong RBAC:
     * Khi Group cấp quyền '1', nhưng User có thuộc tính '-1', hệ thống BẮT BUỘC phải từ chối.
     */
    public function test_tc_sec_03_explicit_user_deny_overrides_group_permission_allow(): void
    {
        $group = Group::factory()->create([
            'name' => 'Asset Managers Group ' . uniqid(),
            'permissions' => json_encode([
                'users.delete' => '1',
                'users.view' => '1',
                'assets.edit' => '1',
            ]),
        ]);

        $user = User::factory()->create([
            'permissions' => json_encode([
                'users.delete' => '-1', // Phủ quyết đích danh
                'assets.create' => '1',  // Cấp phép riêng lẻ
            ]),
        ]);

        $user->groups()->attach($group->id);

        // 1. Explicit Deny (-1) đè Group Allow (1)
        $this->assertFalse(
            $user->hasAccess('users.delete'),
            'Explicit deny (-1) on user MUST strictly override group allow (1)'
        );

        // 2. Quyền không xung đột từ nhóm (1) vẫn được kế thừa
        $this->assertTrue(
            $user->hasAccess('users.view'),
            'Non-conflicting group permission (1) should be inherited'
        );
        $this->assertTrue(
            $user->hasAccess('assets.edit'),
            'Non-conflicting group permission assets.edit should be inherited'
        );

        // 3. Quyền cấp riêng cho User không phụ thuộc vào nhóm
        $this->assertTrue(
            $user->hasAccess('assets.create'),
            'Direct user permission assets.create (1) must be allowed'
        );

        // 4. Quyền không thuộc nhóm và không thuộc user bị từ chối
        $this->assertFalse(
            $user->hasAccess('hardware.delete'),
            'Undefined permission must be denied'
        );
    }

    /**
     * WBS Member 4 (Nguyễn Thành Minh - 2312691):
     * TC-SEC-04: Tích lũy kế thừa quyền hạn từ nhiều nhóm (Multi-Group Aggregation).
     * Xác minh khi một nhân sự tham gia nhiều nhóm vai trò, họ sẽ kế thừa phép hội (Union)
     * của tất cả các quyền được cấp phép từ các nhóm đó.
     */
    public function test_tc_sec_04_user_inherits_union_of_permissions_from_multiple_groups(): void
    {
        $groupAuditors = Group::factory()->create([
            'name' => 'Auditors Group ' . uniqid(),
            'permissions' => json_encode([
                'assets.view' => '1',
                'assets.audit' => '1',
            ]),
        ]);

        $groupLicenseManagers = Group::factory()->create([
            'name' => 'License Managers Group ' . uniqid(),
            'permissions' => json_encode([
                'licenses.view' => '1',
                'licenses.checkout' => '1',
            ]),
        ]);

        $user = User::factory()->create([
            'permissions' => '',
        ]);

        $user->groups()->attach([$groupAuditors->id, $groupLicenseManagers->id]);

        // Kế thừa quyền từ Group 1
        $this->assertTrue($user->hasAccess('assets.view'), 'User should inherit assets.view from Group 1');
        $this->assertTrue($user->hasAccess('assets.audit'), 'User should inherit assets.audit from Group 1');

        // Kế thừa quyền từ Group 2
        $this->assertTrue($user->hasAccess('licenses.view'), 'User should inherit licenses.view from Group 2');
        $this->assertTrue($user->hasAccess('licenses.checkout'), 'User should inherit licenses.checkout from Group 2');

        // Quyền không nằm trong bất kỳ nhóm nào phải bị từ chối
        $this->assertFalse($user->hasAccess('users.delete'), 'User should not have users.delete');
        $this->assertFalse($user->hasAccess('admin'), 'User should not have admin access');
    }

    /**
     * WBS Member 4 (Nguyễn Thành Minh - 2312691):
     * Kiểm thử chính sách bảo mật UserPolicy ngăn chặn leo thang đặc quyền.
     */
    public function test_user_policy_prevents_privilege_escalation(): void
    {
        $superUser = User::factory()->superuser()->create();
        $regularUser = User::factory()->create();
        $targetUser = User::factory()->create();

        // SuperUser được phép thực hiện mọi thao tác quản trị trên UserPolicy
        $this->assertTrue(Gate::forUser($superUser)->allows('create', User::class));
        $this->assertTrue(Gate::forUser($superUser)->allows('delete', $targetUser));

        // Regular User không có quyền admin/users.delete bị chặn
        $this->assertFalse(Gate::forUser($regularUser)->allows('delete', $targetUser));
    }

    /**
     * WBS Member 4 (Nguyễn Thành Minh - 2312691):
     * Kiểm thử tính bất biến của nhật ký kiểm toán hệ thống (action_logs).
     */
    public function test_action_logs_audit_trail_integrity(): void
    {
        $admin = User::factory()->superuser()->create();
        $target = User::factory()->create();

        $log = new Actionlog;
        $log->item_type = User::class;
        $log->item_id = $target->id;
        $log->action_type = 'create';
        $log->created_by = $admin->id;
        $log->note = 'Initial user audit record';
        $log->save();

        $this->assertDatabaseHas('action_logs', [
            'id' => $log->id,
            'item_type' => User::class,
            'item_id' => $target->id,
            'action_type' => 'create',
            'created_by' => $admin->id,
            'note' => 'Initial user audit record',
        ]);

        // ActionLog duy trì bản ghi vĩnh viễn (không bị xóa mềm / mất dấu)
        $this->assertNull($log->deleted_at);
        $this->assertEquals(1, Actionlog::where('id', $log->id)->count());
    }
}
