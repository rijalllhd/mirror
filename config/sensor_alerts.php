<?php

return [
    // Ambang status untuk rekap harian/dashboard.
    'amonia' => (float) env('ALERT_AMONIA_THRESHOLD', 25),
    'thi' => (float) env('ALERT_THI_THRESHOLD', 83),

    // Ambang khusus notifikasi WhatsApp kondisi ekstrem.
    'extreme_amonia' => (float) env('ALERT_EXTREME_AMONIA_THRESHOLD', 50),
    'extreme_thi' => (float) env('ALERT_EXTREME_THI_THRESHOLD', 85),
    'cooldown_minutes' => (int) env('ALERT_COOLDOWN_MINUTES', 360),

    // Nilai di atas batas ini dianggap pembacaan sensor yang tidak valid dan
    // tidak dipakai pada riwayat, grafik, maupun ekspor.
    'history_max_amonia' => (float) env('HISTORY_MAX_AMONIA_PPM', 50),
];
