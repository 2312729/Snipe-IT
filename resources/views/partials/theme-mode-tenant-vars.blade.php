{{-- Tenant color overrides for the theme-mode CSS variables.

     The static portion of what used to live in default.blade.php's
     inline <style> block moved to resources/assets/less/overrides.less
     (compiled into all.css). Only the variables that admins can
     customize via Settings > Branding stay inline so their chosen
     values interpolate at request time.

     Cascade order: overrides.less loads first (via all.css <link>),
     this inline block loads second in <head>, so these values win
     for tenants who have picked their own.

     Included from both layouts/default.blade.php and
     layouts/basic.blade.php so login and error pages carry the
     tenant's brand colors too. --}}
<style>
    :root {
        --main-theme-color: {{ $snipeSettings->header_color ?? '#3c8dbc' }};
        --btn-theme-text-color: {{ $nav_link_color ?? 'light-dark(hsl(from var(--main-theme-color) h s calc(l + 10)),hsl(from var(--main-theme-color) h s calc(l - 10)))' }};
        --nav-hover-text-color: {{ $nav_link_color ?? 'hsl(from var(--main-theme-color) h s calc(l - 10))' }};
        --nav-primary-text-color: {{ $nav_link_color ?? '#ffffff' }};
        --default-label-link-text: light-dark({{ $link_light_color ?? '#296282' }}, {{ $link_dark_color ?? '#5fa4cc' }});
    }
    [data-theme="light"] {
        --link-color: {{ ($link_light_color && $link_light_color !== '#ffffff') ? $link_light_color : '#111827' }};
    }
    [data-theme="dark"] {
        --link-color: {{ $link_dark_color ?? '#60a5fa' }};
    }

    /* Đảm bảo hiển thị chữ màu đen rõ nét cho tất cả các bảng và liên kết trên toàn hệ thống */
    body,
    .content-wrapper,
    .table,
    .table > tbody > tr > td,
    .table > thead > tr > th,
    .bootstrap-table .table > tbody > tr > td,
    .bootstrap-table .table > thead > tr > th,
    .table a:not(.btn),
    .bootstrap-table .table a:not(.btn) {
        color: #111827 !important;
    }

    .table > tbody > tr > td a:not(.btn):hover,
    .bootstrap-table .table a:not(.btn):hover {
        color: #1d4ed8 !important;
        text-decoration: underline !important;
    }

    .table > thead > tr > th,
    .bootstrap-table .table > thead > tr > th {
        color: #0f172a !important;
        font-weight: 700 !important;
    }

    .text-muted, .table .text-muted {
        color: #374151 !important;
    }
</style>
