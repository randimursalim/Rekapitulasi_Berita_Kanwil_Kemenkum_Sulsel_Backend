<?php

if (!function_exists('buildWaMessage')) {
    function buildWaMessage(string $nama, string $link, string $jenis = 'default', ?string $keterangan = null): string
    {
        switch ($jenis) {
            case 'penolakan':
                $msg = "Yth. Sdr/i *{$nama}*,\n\n"
                    . "Mohon maaf, pengajuan perizinan Anda di Kanwil Kemenkum Sulsel belum dapat diproses / ditolak.\n\n";

                if (!empty($keterangan)) {
                    $msg .= "*Alasan:* " . trim($keterangan) . "\n\n";
                }

                if (!empty($link)) {
                    $msg .= "📄 Dokumen: {$link}\n\n";
                }

                $msg .= "Terima kasih.\n_Kanwil Kemenkum Sulsel_";
                return $msg;

            default:
                return "Yth. Sdr/i *{$nama}*,\n\n"
                    . "Surat balasan perizinan Anda dari Kanwil Kemenkum Sulsel sudah tersedia.\n\n"
                    . "📄 Dokumen: {$link}\n\n"
                    . "Terima kasih.\n_Kanwil Kemenkum Sulsel_";
        }
    }
}