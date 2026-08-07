<!-- Mini Calendar & Agenda Widget (TeamHub Aesthetic) -->
<div class="content-card mini-calendar-card mb-4">
    <div class="content-card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <div class="content-card-header-icon" style="background: linear-gradient(135deg, rgba(62, 137, 20, 0.15), rgba(62, 137, 20, 0.05)); color: var(--primary);">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <h5 class="content-card-title mb-0">Kalender & Agenda</h5>
        </div>
        <div class="d-flex align-items-center gap-1">
            <button type="button" class="btn btn-sm btn-icon shadow-none" id="miniCalPrev" title="Bulan Sebelumnya" style="width: 28px; height: 28px; border-radius: 50%; background: rgba(19, 70, 17, 0.05); color: var(--primary); border: none; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                <i class="fas fa-chevron-left"></i>
            </button>
            <span id="miniCalMonthYear" class="fw-bold px-2 text-dark" style="font-size: 0.85rem; font-family: 'Plus Jakarta Sans', sans-serif;"></span>
            <button type="button" class="btn btn-sm btn-icon shadow-none" id="miniCalNext" title="Bulan Selanjutnya" style="width: 28px; height: 28px; border-radius: 50%; background: rgba(19, 70, 17, 0.05); color: var(--primary); border: none; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
    <div class="content-card-body p-3">
        <!-- Calendar Grid -->
        <div class="mini-calendar-grid mb-3">
            <div class="mini-cal-weekdays d-grid text-center text-muted fw-bold mb-2" style="grid-template-columns: repeat(7, 1fr); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">
                <div>Sen</div>
                <div>Sel</div>
                <div>Rab</div>
                <div>Kam</div>
                <div>Jum</div>
                <div class="text-danger">Sab</div>
                <div class="text-danger">Min</div>
            </div>
            <div id="miniCalDaysGrid" class="mini-cal-days d-grid text-center gap-1" style="grid-template-columns: repeat(7, 1fr);"></div>
        </div>

        <!-- Selected Date Header & Event List -->
        <div class="pt-3 border-top">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold small text-dark" id="miniCalSelectedDateLabel">Agenda Hari Ini</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle" id="miniCalEventCount" style="font-size: 0.68rem; border-radius: 20px;">0 Kegiatan</span>
            </div>
            <div id="miniCalAgendaList" class="d-flex flex-column gap-2" style="max-height: 220px; overflow-y: auto;"></div>
        </div>
    </div>
</div>

<style>
.mini-cal-day-cell {
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.22, 0.61, 0.36, 1);
    color: var(--text-body);
    position: relative;
    user-select: none;
    margin: 0 auto;
    width: 32px;
    height: 32px;
}

.mini-cal-day-cell:hover:not(.mini-cal-day--empty) {
    background-color: rgba(62, 137, 20, 0.12);
    color: var(--primary);
}

.mini-cal-day--muted {
    color: #cbd5e1 !important;
    font-weight: 400;
}

.mini-cal-day--today {
    background: linear-gradient(135deg, #134611 0%, #20631c 100%) !important;
    color: #ffffff !important;
    font-weight: 700;
    box-shadow: 0 4px 10px rgba(19, 70, 17, 0.25);
}

.mini-cal-day--selected:not(.mini-cal-day--today) {
    background-color: rgba(62, 137, 20, 0.2) !important;
    color: var(--primary) !important;
    border: 1.5px solid var(--primary);
}

.mini-cal-event-dot {
    width: 4px;
    height: 4px;
    background-color: var(--primary-light);
    border-radius: 50%;
    position: absolute;
    bottom: 3px;
}

.mini-cal-day--today .mini-cal-event-dot {
    background-color: #96E072;
}

.mini-cal-agenda-item {
    padding: 0.6rem 0.75rem;
    border-radius: var(--radius-sm);
    background: rgba(19, 70, 17, 0.03);
    border: 1px solid rgba(19, 70, 17, 0.08);
    transition: all 0.2s ease;
}

.mini-cal-agenda-item:hover {
    background: rgba(19, 70, 17, 0.07);
    transform: translateX(2px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentDate = new Date();
    let selectedDate = new Date();

    // Data agenda dari Blade/Controller jika disuplai, atau default fallback
    const eventsData = @json($events ?? []);

    const monthNamesIndo = [
        "Januari", "Februari", "Maret", "April", "Mei", "Juni",
        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
    ];

    const prevBtn = document.getElementById('miniCalPrev');
    const nextBtn = document.getElementById('miniCalNext');
    const monthYearLabel = document.getElementById('miniCalMonthYear');
    const daysGrid = document.getElementById('miniCalDaysGrid');
    const selectedDateLabel = document.getElementById('miniCalSelectedDateLabel');
    const eventCountBadge = document.getElementById('miniCalEventCount');
    const agendaList = document.getElementById('miniCalAgendaList');

    if (!daysGrid) return;

    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        monthYearLabel.textContent = `${monthNamesIndo[month]} ${year}`;

        // Getting first day of month (0 = Sunday, 1 = Monday, etc.)
        let firstDayIndex = new Date(year, month, 1).getDay();
        // Convert Sunday (0) to 7 for Monday-start calculation
        firstDayIndex = firstDayIndex === 0 ? 6 : firstDayIndex - 1;

        const totalDaysInMonth = new Date(year, month + 1, 0).getDate();
        const prevMonthLastDay = new Date(year, month, 0).getDate();

        daysGrid.innerHTML = '';

        // Previous month days
        for (let x = firstDayIndex; x > 0; x--) {
            const dayNum = prevMonthLastDay - x + 1;
            const cell = document.createElement('div');
            cell.className = 'mini-cal-day-cell mini-cal-day--muted';
            cell.textContent = dayNum;
            daysGrid.appendChild(cell);
        }

        const today = new Date();

        // Current month days
        for (let i = 1; i <= totalDaysInMonth; i++) {
            const cell = document.createElement('div');
            cell.className = 'mini-cal-day-cell';
            cell.textContent = i;

            const thisDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;

            // Check if Today
            if (i === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                cell.classList.add('mini-cal-day--today');
            }

            // Check if Selected
            if (i === selectedDate.getDate() && month === selectedDate.getMonth() && year === selectedDate.getFullYear()) {
                cell.classList.add('mini-cal-day--selected');
            }

            // Check if has Event
            const dayEvents = eventsData.filter(e => e.date === thisDateStr);
            if (dayEvents.length > 0) {
                cell.classList.add('mini-cal-day--has-event');
                const dot = document.createElement('div');
                dot.className = 'mini-cal-event-dot';
                cell.appendChild(dot);
            }

            cell.addEventListener('click', function() {
                selectedDate = new Date(year, month, i);
                renderCalendar();
                renderAgenda(thisDateStr, selectedDate);
            });

            daysGrid.appendChild(cell);
        }

        // Remaining days to fill grid (6 rows * 7 = 42 cells total)
        const totalCells = daysGrid.children.length;
        const remainingCells = (42 - totalCells) % 7;
        for (let j = 1; j <= remainingCells; j++) {
            const cell = document.createElement('div');
            cell.className = 'mini-cal-day-cell mini-cal-day--muted';
            cell.textContent = j;
            daysGrid.appendChild(cell);
        }

        const todayStr = `${selectedDate.getFullYear()}-${String(selectedDate.getMonth() + 1).padStart(2, '0')}-${String(selectedDate.getDate()).padStart(2, '0')}`;
        renderAgenda(todayStr, selectedDate);
    }

    function renderAgenda(dateStr, dateObj) {
        const isToday = dateObj.toDateString() === new Date().toDateString();
        const formattedDate = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        
        selectedDateLabel.textContent = isToday ? `Agenda Hari Ini (${formattedDate})` : `Agenda ${formattedDate}`;

        const dayEvents = eventsData.filter(e => e.date === dateStr);
        eventCountBadge.textContent = `${dayEvents.length} Kegiatan`;

        agendaList.innerHTML = '';

        if (dayEvents.length === 0) {
            agendaList.innerHTML = `
                <div class="text-center py-3 text-muted" style="font-size: 0.82rem;">
                    <i class="far fa-calendar-check me-1"></i> Tidak ada kegiatan pada tanggal ini.
                </div>
            `;
            return;
        }

        dayEvents.forEach(evt => {
            const item = document.createElement('div');
            item.className = 'mini-cal-agenda-item d-flex align-items-center justify-content-between';
            item.innerHTML = `
                <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; background: rgba(19, 70, 17, 0.08); color: var(--primary); font-size: 0.8rem;">
                        <i class="${evt.icon || 'fas fa-calendar-day'}"></i>
                    </div>
                    <div class="text-truncate">
                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.83rem;">${evt.title}</div>
                        <small class="text-muted text-truncate d-block" style="font-size: 0.73rem;">${evt.time || ''} ${evt.subtitle ? '• ' + evt.subtitle : ''}</small>
                    </div>
                </div>
                ${evt.url ? `<a href="${evt.url}" class="btn btn-sm btn-outline-primary shadow-none py-0 px-2 flex-shrink-0" style="font-size: 0.72rem; border-radius: 12px;">Buka</a>` : ''}
            `;
            agendaList.appendChild(item);
        });
    }

    prevBtn.addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    nextBtn.addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    renderCalendar();
});
</script>
