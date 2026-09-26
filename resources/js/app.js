const toggle = document.querySelector('[data-nav-toggle]');
const sidebar = document.querySelector('[data-sidebar]');
toggle?.addEventListener('click', () => {
    sidebar?.classList.toggle('max-lg:translate-x-0');
    sidebar?.classList.toggle('max-lg:-translate-x-full');
});

document.querySelectorAll('[data-fill]').forEach((button) => {
    button.addEventListener('click', () => {
        const mobile = document.querySelector('[name="mobile"]');
        const password = document.querySelector('[name="password"]');
        if (mobile) mobile.value = button.dataset.mobile || '';
        if (password) password.value = button.dataset.password || '';
    });
});

document.querySelectorAll('[data-auth-tab]').forEach((button) => {
    button.addEventListener('click', () => {
        const target = button.dataset.authTab;
        document.querySelectorAll('[data-auth-panel]').forEach((panel) => {
            panel.classList.toggle('hidden', panel.dataset.authPanel !== target);
        });
        document.querySelectorAll('[data-auth-tab]').forEach((tab) => {
            tab.classList.toggle('bg-forest', tab.dataset.authTab === target);
            tab.classList.toggle('text-foam', tab.dataset.authTab === target);
        });
    });
});

document.querySelector('[data-geo]')?.addEventListener('click', () => {
    const form = document.querySelector('[data-search-form]');
    if (!navigator.geolocation || !form) return;
    navigator.geolocation.getCurrentPosition((position) => {
        form.querySelector('[name="lat"]').value = position.coords.latitude;
        form.querySelector('[name="lng"]').value = position.coords.longitude;
        form.requestSubmit();
    });
});

document.querySelector('[data-add-medicine]')?.addEventListener('click', () => {
    const wrap = document.querySelector('[data-medicines]');
    if (!wrap) return;
    const index = wrap.querySelectorAll('.medicine-row').length;
    const row = document.createElement('div');
    row.className = 'medicine-row grid gap-2 md:grid-cols-4';
    row.innerHTML = `
        <input class="field" name="medicines[${index}][name]" placeholder="Medicine">
        <input class="field" name="medicines[${index}][dosage]" placeholder="Dosage">
        <input class="field" name="medicines[${index}][duration]" placeholder="Duration">
        <input class="field" name="medicines[${index}][timing]" placeholder="Timing">
    `;
    wrap.appendChild(row);
});

document.querySelectorAll('[data-print]').forEach((button) => {
    button.addEventListener('click', () => window.print());
});

function connectSocket(url, onMessage) {
    if (!url || !window.WebSocket) return;
    try {
        const socket = new WebSocket(url);
        socket.addEventListener('message', () => onMessage());
    } catch (error) {
        console.warn(error);
    }
}

function chime() {
    const context = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = context.createOscillator();
    const gain = context.createGain();
    oscillator.frequency.value = 523;
    gain.gain.value = 0.05;
    oscillator.connect(gain);
    gain.connect(context.destination);
    oscillator.start();
    oscillator.stop(context.currentTime + 0.2);
}

const tracker = document.querySelector('[data-tracker]');
if (tracker) {
    const pull = async () => {
        const response = await fetch(tracker.dataset.tracker, { headers: { Accept: 'application/json' } });
        if (!response.ok) return;
        const data = await response.json();
        const current = tracker.querySelector('[data-current]');
        const ahead = tracker.querySelector('[data-ahead]');
        const wait = tracker.querySelector('[data-wait]');
        const note = tracker.querySelector('[data-note]');
        if (current) current.textContent = data.current_token ?? '—';
        if (ahead) ahead.textContent = data.ahead ?? '—';
        if (wait) wait.textContent = data.wait_minutes ?? '—';
        if (note) {
            if (data.your_turn) note.textContent = tracker.dataset.now;
            else if (Number(data.ahead) === 0) note.textContent = tracker.dataset.next;
            else note.textContent = tracker.dataset.aheadLabel.replace(':count', data.ahead);
        }
    };
    pull();
    setInterval(pull, 2000);
    connectSocket(tracker.dataset.ws, pull);
}

const board = document.querySelector('[data-display]');
if (board) {
    const clock = board.querySelector('[data-clock]');
    const mount = board.querySelector('[data-boards]');
    let seen = '';
    const tick = () => {
        if (clock) clock.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    };
    tick();
    setInterval(tick, 1000);

    const pull = async () => {
        const response = await fetch(board.dataset.display, { headers: { Accept: 'application/json' } });
        if (!response.ok) return;
        const data = await response.json();
        const signature = JSON.stringify(data.doctors.map((doctor) => [doctor.doctor_id, doctor.current_token]));
        if (seen && seen !== signature) chime();
        seen = signature;
        mount.innerHTML = data.doctors.map((doctor) => `
            <article class="rounded-[2rem] border border-white/10 bg-white/5 p-6 md:p-8">
                <p class="text-sm uppercase tracking-[0.18em] text-[#f3e2b8]/70">${doctor.room || ''} · ${doctor.specialization || ''}</p>
                <h2 class="mt-2 font-display text-3xl">${doctor.doctor_name || ''}</h2>
                <p class="mt-6 text-sm uppercase tracking-[0.22em] text-white/50">Now serving</p>
                <p class="now-number">${doctor.current_token || 0}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    ${(doctor.upcoming || []).map((token) => `<span class="grid h-14 w-14 place-items-center rounded-full border border-[#f3e2b8]/40 font-display text-xl">${token.token_number}</span>`).join('')}
                </div>
            </article>
        `).join('');
    };
    pull();
    setInterval(pull, 2000);
    connectSocket(board.dataset.ws, pull);
}

const doctorLive = document.querySelector('[data-doctor-live]');
if (doctorLive) {
    const pull = async () => {
        const date = document.querySelector('[name="date"]:checked')?.value || '';
        const url = new URL(doctorLive.dataset.doctorLive, window.location.origin);
        if (date) url.searchParams.set('date', date);
        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!response.ok) return;
        const data = await response.json();
        const current = doctorLive.querySelector('[data-current]');
        if (current) current.textContent = data.current_token ?? 0;
    };
    pull();
    setInterval(pull, 3000);
    document.querySelectorAll('[name="date"]').forEach((input) => input.addEventListener('change', pull));
    connectSocket(doctorLive.dataset.ws, pull);
}
