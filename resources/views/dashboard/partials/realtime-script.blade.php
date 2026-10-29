<script>
const FIREBASE_CONFIG = @json($firebaseConfig);
const SENSOR_PATH = @json($sensorPath);
const MAX_HISTORY = 30;
let fallbackReadings = 0;
let charts = { Suhu: null, Kelembapan: null, Amonia_PPM: null, THI: null };

function el(id) {
    return document.getElementById(id);
}

function setText(id, value) {
    const target = el(id);
    if (target) target.textContent = value;
}

function numberText(value, digits) {
    const numeric = Number(value);
    return Number.isFinite(numeric) ? numeric.toFixed(digits) : '-';
}

function badgeClass(status) {
    const base = 'inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-extrabold ';
    const classes = {
        normal: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        warning: 'border-amber-200 bg-amber-50 text-amber-700',
        danger: 'border-red-200 bg-red-50 text-red-700',
    };
    return base + (classes[status] || classes.normal);
}

function setBadge(key, status, labelMap = { normal: 'Normal', warning: 'Waspada', danger: 'Bahaya' }) {
    const badge = el(`badge-${key}`);
    const label = el(`label-${key}`);
    if (!badge || !label) return;
    badge.className = badgeClass(status);
    label.textContent = labelMap[status] || '-';
}

function getSuhuStatus(v) {
    return v >= 35 ? 'danger' : v >= 30 ? 'warning' : 'normal';
}

function getKelembapanStatus(v) {
    return v >= 80 ? 'danger' : v >= 70 ? 'warning' : 'normal';
}

function getAmoniaStatus(v) {
    return v >= 25 ? 'danger' : v >= 20 ? 'warning' : 'normal';
}

function getThiStatus(v) {
    return v >= 83 ? 'danger' : v >= 72 ? 'warning' : 'normal';
}

function getActuatorStatus(value) {
    const text = String(value || '').toUpperCase();
    if (text.includes('MENYALA') || text.includes('ON') || text.includes('AKTIF')) return 'danger';
    if (text.includes('WASPADA') || text.includes('SIAGA')) return 'warning';
    return 'normal';
}

function setActuator(key, value) {
    const status = getActuatorStatus(value);
    setBadge(key, status, { normal: 'Mati', warning: 'Siaga', danger: 'Menyala' });
    setText(`val-${key}`, value || '-');
}

function hexToRgba(hex, alpha) {
    const normalized = hex.replace('#', '');
    const r = parseInt(normalized.substring(0, 2), 16);
    const g = parseInt(normalized.substring(2, 4), 16);
    const b = parseInt(normalized.substring(4, 6), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function makeChart(id, color, label) {
    const canvas = el(id);
    if (!canvas || typeof Chart === 'undefined') return null;

    return new Chart(canvas, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label,
                data: [],
                borderColor: color,
                borderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 4,
                tension: 0.38,
                fill: true,
                backgroundColor: hexToRgba(color, 0.12),
            }],
        },
        options: {
            animation: false,
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#0f172a', padding: 10 },
            },
            scales: {
                x: { grid: { display: false }, ticks: { maxTicksLimit: 4, maxRotation: 0 } },
                y: { grid: { color: 'rgba(148, 163, 184, 0.18)' }, ticks: { maxTicksLimit: 4 } },
            },
        },
    });
}

if (typeof Chart !== 'undefined') {
    charts = {
        Suhu: makeChart('chart-suhu', '#0369a1', 'Suhu'),
        Kelembapan: makeChart('chart-kelembapan', '#0f766e', 'Kelembapan'),
        Amonia_PPM: makeChart('chart-amonia', '#b45309', 'Amonia PPM'),
        THI: makeChart('chart-thi', '#7c3aed', 'THI'),
    };
}

function pushChart(key, label, value) {
    if (!charts[key] || !Number.isFinite(value)) return;
    const chart = charts[key];
    if (chart.data.labels.length >= MAX_HISTORY) {
        chart.data.labels.shift();
        chart.data.datasets[0].data.shift();
    }
    chart.data.labels.push(label);
    chart.data.datasets[0].data.push(value);
    chart.update('none');
}

firebase.initializeApp(FIREBASE_CONFIG);
const db = firebase.database();

db.ref(SENSOR_PATH + '/Realtime').on('value', (snap) => {
    const d = snap.val();
    if (!d) return;

    const now = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

    setText('val-suhu', numberText(d.Suhu, 1));
    setText('val-kelembapan', numberText(d.Kelembapan, 1));
    setText('val-amonia', numberText(d.Amonia_PPM, 5));
    setText('val-thi', numberText(d.THI, 2));
    setText('val-raw', d.Amonia_Raw ?? '-');
    setText('val-status-amonia', d.Status_Amonia ?? '-');

    const rawStrip = el('raw-strip');
    if (rawStrip) {
        rawStrip.classList.remove('hidden');
        rawStrip.classList.add('flex');
    }

    setActuator('kipas', d.Status_Kipas);
    setActuator('nozzle', d.Status_Nozzle);

    setBadge('suhu', getSuhuStatus(Number(d.Suhu)));
    setBadge('kelembapan', getKelembapanStatus(Number(d.Kelembapan)));
    setBadge('amonia', getAmoniaStatus(Number(d.Amonia_PPM)));
    setBadge('thi', getThiStatus(Number(d.THI)));

    pushChart('Suhu', now, Number(d.Suhu));
    pushChart('Kelembapan', now, Number(d.Kelembapan));
    pushChart('Amonia_PPM', now, Number(d.Amonia_PPM));
    pushChart('THI', now, Number(d.THI));

    fallbackReadings = Math.min(fallbackReadings + 1, MAX_HISTORY);
    const count = charts.Suhu ? charts.Suhu.data.labels.length : fallbackReadings;
    setText('data-count', `${count} readings`);

    const connBadge = el('conn-badge');
    if (connBadge) connBadge.className = badgeClass('normal');
    const connDot = el('conn-dot');
    if (connDot) connDot.classList.add('animate-pulse');
    setText('conn-text', 'Live');
}, (err) => {
    console.error('Firebase error:', err);
    const connBadge = el('conn-badge');
    if (connBadge) connBadge.className = badgeClass('danger');
    setText('conn-text', 'Gagal terhubung');
});
</script>
