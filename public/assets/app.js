const bootstrap = window.__PRICING_BOOTSTRAP__ || {};
const state = {
  year: bootstrap.defaultYear || 2026,
  month: bootstrap.defaultMonth || 2,
  acriss: [...(bootstrap.defaultCodes || ['CCAR'])],
  rows: [],
  history: [],
};

const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const acrissCodes = ['CCAR', 'CCMR', 'ECAR', 'ECMR', 'FFAR', 'IDAR', 'IFAR', 'MDMR', 'PVAR', 'SDAR'];

const yearChips = document.getElementById('yearChips');
const monthChips = document.getElementById('monthChips');
const acrissChips = document.getElementById('acrissChips');
const viewingStatus = document.getElementById('viewingStatus');
const historySummary = document.getElementById('historySummary');
const saveStatus = document.getElementById('saveStatus');
const table = document.getElementById('rateTable');
const bulkModal = document.getElementById('bulkModal');
const historyModal = document.getElementById('historyModal');
const historyList = document.getElementById('historyList');
const operationInput = document.getElementById('operationInput');
const daysGrid = document.getElementById('daysGrid');

function createChip(label, active, onClick, extraClass = '') {
  const button = document.createElement('button');
  button.type = 'button';
  button.className = `chip ${extraClass}`.trim();
  if (active) button.classList.add('active');
  button.textContent = label;
  button.addEventListener('click', onClick);
  return button;
}

function renderFilters() {
  [2025, 2026, 2027].forEach((year) => {
    yearChips.appendChild(createChip(String(year), year === state.year, () => {
      state.year = year;
      rerenderFilterState();
      loadDashboard();
    }));
  });

  monthNames.forEach((month, index) => {
    monthChips.appendChild(createChip(month, index + 1 === state.month, () => {
      state.month = index + 1;
      rerenderFilterState();
      loadDashboard();
    }));
  });

  acrissCodes.forEach((code) => {
    acrissChips.appendChild(createChip(code, state.acriss.includes(code), () => {
      if (state.acriss.includes(code)) {
        state.acriss = state.acriss.filter((item) => item !== code);
      } else {
        state.acriss.push(code);
      }
      if (!state.acriss.length) state.acriss = ['CCAR'];
      rerenderFilterState();
      loadDashboard();
    }));
  });

  document.getElementById('selectAllCodes').addEventListener('click', () => {
    state.acriss = [...acrissCodes];
    rerenderFilterState();
    loadDashboard();
  });

  document.getElementById('clearCodes').addEventListener('click', () => {
    state.acriss = ['CCAR'];
    rerenderFilterState();
    loadDashboard();
  });
}

function rerenderFilterState() {
  [...yearChips.children].forEach((chip) => chip.classList.toggle('active', Number(chip.textContent) === state.year));
  [...monthChips.children].forEach((chip, index) => chip.classList.toggle('active', index + 1 === state.month));
  [...acrissChips.children].forEach((chip) => chip.classList.toggle('active', state.acriss.includes(chip.textContent)));
}

function buildDaySelectors() {
  for (let day = 1; day <= 31; day += 1) {
    const label = document.createElement('label');
    label.className = 'day-item';
    label.innerHTML = `<input type="checkbox" value="${day}" checked> Day ${day}`;
    daysGrid.appendChild(label);
  }
}

async function loadDashboard() {
  const params = new URLSearchParams({
    year: String(state.year),
    month: String(state.month),
    acriss: state.acriss.join(','),
  });

  const response = await fetch(`/api/dashboard?${params.toString()}`);
  const payload = await response.json();
  state.rows = payload.rows || [];
  state.history = payload.history || [];
  renderTable();
  renderHistory();
  viewingStatus.textContent = `Viewing: ${monthNames[state.month - 1]} ${state.year} • ACRISS: ${state.acriss.join(', ')}`;
  historySummary.textContent = `${state.history.length} recent event(s) available`;
}

function renderTable() {
  const thead = table.querySelector('thead');
  const tbody = table.querySelector('tbody');
  thead.innerHTML = '';
  tbody.innerHTML = '';

  const headers = ['Pickup Date', 'ACRISS'];
  for (let day = 1; day <= 16; day += 1) headers.push(`Day ${day}`);
  const tr = document.createElement('tr');
  headers.forEach((header) => {
    const th = document.createElement('th');
    th.textContent = header;
    tr.appendChild(th);
  });
  thead.appendChild(tr);

  state.rows.forEach((row) => {
    const tableRow = document.createElement('tr');
    if (Number(row.is_locked) === 1) tableRow.classList.add('locked');
    tableRow.dataset.rowId = row.id;

    const dateCell = document.createElement('td');
    dateCell.textContent = row.pickup_date;
    tableRow.appendChild(dateCell);

    const codeCell = document.createElement('td');
    codeCell.textContent = row.acriss_code;
    tableRow.appendChild(codeCell);

    for (let day = 1; day <= 16; day += 1) {
      const cell = document.createElement('td');
      cell.textContent = Number(row[`day_${day}`]).toFixed(2);
      tableRow.appendChild(cell);
    }

    tbody.appendChild(tableRow);
  });
}

function renderHistory() {
  historyList.innerHTML = '';
  if (!state.history.length) {
    historyList.innerHTML = '<article class="history-item"><strong>No history yet</strong><span>New actions will appear here.</span></article>';
    return;
  }

  state.history.forEach((item) => {
    const article = document.createElement('article');
    article.className = 'history-item';
    article.innerHTML = `<strong>${item.action}</strong><div>${item.details || ''}</div><span>${item.created_at}</span>`;
    historyList.appendChild(article);
  });
}

async function postJson(url, body) {
  const response = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  });
  return response.json();
}

function flashSaved(message) {
  saveStatus.textContent = message;
}

async function applyBulkUpdate() {
  const selectedDays = [...daysGrid.querySelectorAll('input:checked')].map((input) => Number(input.value));
  const operation = operationInput.value.trim();
  if (!operation) {
    alert('Enter an operation such as 120, +10, or -5%.');
    return;
  }

  await postJson('/api/bulk-update', {
    year: state.year,
    month: state.month,
    acriss_codes: state.acriss,
    operation,
    selected_days: selectedDays,
  });

  operationInput.value = '';
  bulkModal.style.display = 'none';
  flashSaved('Bulk update applied');
  await loadDashboard();
}

async function copyRates() {
  await postJson('/api/copy-rates', {
    year: state.year,
    month: state.month,
    acriss_codes: state.acriss,
  });
  flashSaved('Rates copied from Day 1');
  await loadDashboard();
}

async function runIntegrity() {
  const params = new URLSearchParams({
    year: String(state.year),
    month: String(state.month),
    acriss: state.acriss.join(','),
  });
  const response = await fetch(`/api/integrity?${params.toString()}`);
  const payload = await response.json();
  const flaggedIds = new Set((payload.issues || []).map((issue) => String(issue.row_id)));
  [...table.querySelectorAll('tbody tr')].forEach((row) => row.classList.toggle('flagged', flaggedIds.has(row.dataset.rowId)));
  flashSaved(payload.issue_count ? `${payload.issue_count} issue(s) flagged` : 'Integrity check passed');
  await loadDashboard();
}

function exportCsv() {
  const headers = ['Pickup Date', 'ACRISS'];
  for (let day = 1; day <= 16; day += 1) headers.push(`Day ${day}`);
  const rows = state.rows.map((row) => [row.pickup_date, row.acriss_code, ...Array.from({ length: 16 }, (_, index) => Number(row[`day_${index + 1}`]).toFixed(2))]);
  const csv = [headers, ...rows].map((line) => line.join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
  const url = URL.createObjectURL(blob);
  const anchor = document.createElement('a');
  anchor.href = url;
  anchor.download = `pricing-${state.year}-${String(state.month).padStart(2, '0')}.csv`;
  anchor.click();
  URL.revokeObjectURL(url);
}

function bindEvents() {
  document.getElementById('openBulkModal').addEventListener('click', () => { bulkModal.style.display = 'flex'; });
  document.getElementById('closeBulkModal').addEventListener('click', () => { bulkModal.style.display = 'none'; });
  document.getElementById('cancelBulkModal').addEventListener('click', () => { bulkModal.style.display = 'none'; });
  document.getElementById('applyBulkBtn').addEventListener('click', applyBulkUpdate);
  document.getElementById('copyRatesBtn').addEventListener('click', copyRates);
  document.getElementById('integrityBtn').addEventListener('click', runIntegrity);
  document.getElementById('exportBtn').addEventListener('click', exportCsv);
  document.getElementById('viewHistoryBtn').addEventListener('click', () => { historyModal.style.display = 'flex'; });
  document.getElementById('closeHistoryModal').addEventListener('click', () => { historyModal.style.display = 'none'; });
  document.getElementById('historyCloseBtn').addEventListener('click', () => { historyModal.style.display = 'none'; });
}

renderFilters();
buildDaySelectors();
bindEvents();
loadDashboard().catch((error) => {
  viewingStatus.textContent = 'Unable to load pricing data.';
  historySummary.textContent = error.message;
});
