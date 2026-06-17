<?php

if (!function_exists('izin_status_label')) {
    function izin_status_label(int|string $status): string
    {
        $map = [
            1 => 'Diterima oleh Pengelola Surat Masuk',
            2 => 'Ditolak karena tidak memenuhi persyaratan',
            3 => 'Diterima oleh Kakanwil',
            4 => 'Ditolak oleh Pimpinan',
            5 => 'Diterima Kabag TU & Umum',
            6 => 'Surat balasan akan dikirim melalui WhatsApp yang terdaftar',
        ];

        return $map[$status] ?? 'Status tidak diketahui';
    }
}

if (!function_exists('izin_status_badge')) {
    function izin_status_badge(int|string $status): string
    {
        $classMap = [
            1 => 'badge-info',
            2 => 'badge-danger',
            3 => 'badge-primary',
            4 => 'badge-danger',
            5 => 'badge-success',
            6 => 'badge-success',
        ];

        $label = izin_status_label($status);
        $class = $classMap[$status] ?? 'badge-secondary';

        return "<span class=\"badge {$class}\">{$label}</span>";
    }
}