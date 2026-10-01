document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.password-toggle').forEach((button) => {
        button.addEventListener('click', () => {
            const input = button.parentElement.querySelector('input');
            input.type = input.type === 'password' ? 'text' : 'password';
            button.textContent = input.type === 'password' ? 'lihat' : 'sembunyikan';
        });
    });

    const roleInputs = document.querySelectorAll('input[name="role"]');
    const adminCode = document.querySelector('.admin-code-field');
    roleInputs.forEach((input) => input.addEventListener('change', () => {
        if (adminCode) adminCode.classList.toggle('hidden', input.value !== 'admin' || !input.checked);
    }));

    const dateInput = document.querySelector('#booking-date');
    const slotList = document.querySelector('#slot-list');
    const bookingForm = document.querySelector('#booking-form');
    if (!dateInput || !slotList || !bookingForm) return;

    const fieldId = bookingForm.querySelector('[name="field_id"]').value;
    const selectedStart = document.querySelector('#selected-start');
    const selectedDate = document.querySelector('#selected-date');
    const duration = document.querySelector('#duration-hours');
    const total = document.querySelector('#total-price');
    const summary = document.querySelector('#selected-summary');
    const submit = document.querySelector('#book-submit');
    let price = 0;
    let selectedMinute = null;

    const toMinutes = (time) => {
        const parts = time.split(':').map(Number);
        return parts[0] * 60 + parts[1];
    };
    const pad = (n) => String(n).padStart(2, '0');
    const timeString = (minutes) => {
        const clockMinute = ((minutes % 1440) + 1440) % 1440;
        return `${pad(Math.floor(clockMinute / 60))}:${pad(clockMinute % 60)}`;
    };
    const isOccupied = (start, end, occupied) => occupied.some(
        (slot) => Number(slot.start_minute) < end && Number(slot.end_minute) > start
    );
    const dateAtOffset = (dateValue, offset) => {
        const [year, month, day] = dateValue.split('-').map(Number);
        const date = new Date(year, month - 1, day + offset);
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
    };

    async function loadSlots() {
        selectedMinute = null;
        selectedStart.value = '';
        submit.disabled = true;
        summary.textContent = 'Pilih slot dari kalender';
        total.textContent = '—';
        selectedDate.value = dateInput.value;
        slotList.innerHTML = '<div class="slot-loading">Memuat slot...</div>';
        try {
            const baseUrl = window.SWOOSH?.baseUrl || '';
            const response = await fetch(
                `${baseUrl}/?route=booking/slots&field_id=${encodeURIComponent(fieldId)}&date=${encodeURIComponent(dateInput.value)}`,
                { headers: { Accept: 'application/json' }, cache: 'no-store' }
            );
            const data = await response.json().catch(() => null);
            if (!response.ok) {
                throw new Error(data?.error || `Permintaan slot gagal (HTTP ${response.status}).`);
            }
            if (!data?.field || !Array.isArray(data.occupied)) {
                throw new Error(data?.error || 'Server tidak mengirim data slot yang valid. Periksa URL aplikasi dan muat ulang.');
            }
            price = Number(data.field.price_per_hour);
            const start = toMinutes(data.field.open_time);
            const rawClose = toMinutes(data.field.close_time);
            const close = rawClose <= start ? rawClose + 1440 : rawClose;
            const occupied = data.occupied || [];
            slotList.innerHTML = '';
            for (let minute = start; minute < close; minute += 60) {
                if (minute + 60 > close) continue;
                const end = minute + 60;
                const dayOffset = Math.floor(minute / 1440);
                const slotDate = dateAtOffset(dateInput.value, dayOffset);
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'slot';
                button.dataset.minute = String(minute);
                button.textContent = `${timeString(minute)}${dayOffset ? ' (+1 hari)' : ''}`;
                if (isOccupied(minute, end, occupied)) {
                    button.classList.add('slot-occupied');
                    button.disabled = true;
                    button.title = 'Slot ini sudah dibooking';
                    button.textContent = `× ${timeString(minute)}${dayOffset ? ' (+1 hari)' : ''}`;
                    button.setAttribute('aria-label', `${timeString(minute)}, sudah dibooking dan tidak tersedia`);
                } else {
                    button.addEventListener('click', () => selectSlot(button, minute, slotDate, occupied, close));
                }
                slotList.appendChild(button);
            }
        } catch (error) {
            const message = document.createElement('div');
            message.className = 'slot-loading';
            message.textContent = error instanceof Error
                ? error.message
                : 'Slot belum bisa dimuat. Coba refresh halaman.';
            slotList.replaceChildren(message);
        }
    }

    function selectSlot(button, minute, slotDate, occupied, close) {
        document.querySelectorAll('.slot.selected').forEach((item) => item.classList.remove('selected'));
        const hours = Number(duration.value);
        const end = minute + hours * 60;
        if (end > close || isOccupied(minute, end, occupied)) {
            summary.textContent = `Durasi ${hours} jam tidak tersedia dari ${timeString(minute)}.`;
            selectedMinute = null;
            selectedStart.value = '';
            submit.disabled = true;
            total.textContent = '—';
            return;
        }
        button.classList.add('selected');
        selectedMinute = minute;
        selectedStart.value = timeString(minute);
        selectedDate.value = slotDate;
        summary.textContent = `${slotDate.split('-').reverse().join('/')} · ${timeString(minute)} · ${hours} jam`;
        total.textContent = `Rp ${(price * hours).toLocaleString('id-ID')}`;
        submit.disabled = false;
    }

    duration.addEventListener('change', () => {
        if (selectedMinute !== null) {
            const active = [...document.querySelectorAll('.slot')].find(
                (item) => Number(item.dataset.minute) === selectedMinute
            );
            if (active) active.click();
        }
    });
    dateInput.addEventListener('change', loadSlots);
    loadSlots();
});