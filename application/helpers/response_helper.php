<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Helper response standar mengikuti pola aplikasi internal.
 * Kode '00' = sukses, selain itu = error.
 *
 * Catatan: Ini ASUMSI berdasarkan pola pemanggilan give_response($code, $message, $data)
 * yang terlihat di C_dashboard.php. Kalau nanti kamu cek file helper asli di aplikasi
 * lama dan formatnya beda (misal ada field tambahan), tinggal sesuaikan function ini
 * SATU tempat saja — semua controller di modul ini otomatis ikut menyesuaikan.
 */
if (!function_exists('give_response')) {
    function give_response($code, $message, $data = [])
    {
        $CI = &get_instance();
        $CI->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'code'    => $code,
                'message' => $message,
                'data'    => $data,
            ]));
    }
}
