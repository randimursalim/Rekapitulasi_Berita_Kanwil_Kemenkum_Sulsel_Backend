<?php

if (!function_exists('buildWaMessage')) {
    function buildWaMessage(string $nama, string $link, string $jenis = 'default'): string
    {
        switch ($jenis) {
            case 'penolakan':
                return "Halo Sdr/i *{$nama}*,\n\n"
                    . "Mohon maaf, pengajuan Anda ditolak.\n\n"
                    . "Detail: {$link}";

            default:
                return "Halo Sdr/i *{$nama}*,\n\n"
                    . "Surat balasan Anda sudah tersedia.\n\n"
                    . "📄 {$link}";
        }
    }
}