<main class="shell">
  <section class="card topbar">
    <div>
      <p class="eyebrow">Fleet &amp; Rates</p>
      <h1>Backoffice Rate Manager</h1>
      <p class="meta">MySQL-backed pricing portal with autosave-style workflows, integrity checks, and audit history.</p>
    </div>
    <div class="header-actions">
      <span class="pill pill-live">Live API</span>
      <a class="link-btn" href="/api/dashboard?year=2026&amp;month=2&amp;acriss=CCAR">Preview JSON</a>
    </div>
  </section>

  <section class="summary-grid">
    <article class="summary-card">
      <span>Active plan</span>
      <strong>#8171</strong>
      <small>Dunya Cars</small>
    </article>
    <article class="summary-card">
      <span>Storage</span>
      <strong>MySQL</strong>
      <small>Schema + seed included</small>
    </article>
    <article class="summary-card">
      <span>Delivery</span>
      <strong>PHP MVC</strong>
      <small>CI-ready smoke tests</small>
    </article>
    <article class="summary-card">
      <span>Status</span>
      <strong id="saveStatus">All changes saved</strong>
      <small>History retained</small>
    </article>
  </section>

  <section class="card toolbar">
    <button class="btn primary" id="openBulkModal">Update Prices</button>
    <button class="btn success" id="copyRatesBtn">Copy Rates</button>
    <button class="btn warning" id="integrityBtn">Integrity Check</button>
    <button class="btn ink" id="exportBtn">Export CSV</button>
    <button class="btn violet" id="viewHistoryBtn">View History</button>
  </section>

  <section class="card filters">
    <div class="group">
      <h3>Year</h3>
      <div class="chips" id="yearChips"></div>
    </div>
    <div class="group">
      <h3>Month</h3>
      <div class="chips" id="monthChips"></div>
    </div>
    <div class="group">
      <div class="group-head">
        <h3>ACRISS Codes</h3>
        <div class="mini-actions">
          <button class="chip chip-light" id="selectAllCodes" type="button">Select all</button>
          <button class="chip chip-light" id="clearCodes" type="button">Clear</button>
        </div>
      </div>
      <div class="chips chips-wrap" id="acrissChips"></div>
    </div>
  </section>

  <section class="status-row">
    <div class="status-card" id="viewingStatus">Loading pricing grid…</div>
    <div class="status-card subtle" id="historySummary">History ready</div>
  </section>

  <section class="card table-card">
    <div class="table-scroll">
      <table id="rateTable">
        <thead></thead>
        <tbody></tbody>
      </table>
    </div>
  </section>

  <footer class="footer-note">Built from the provided portal design and wired to PHP endpoints backed by MySQL-ready models.</footer>
</main>

<div class="modal-backdrop" id="bulkModal">
  <section class="modal">
    <header class="modal-head">
      <h2>Bulk Update Prices</h2>
      <button class="icon-btn" id="closeBulkModal" aria-label="close">✕</button>
    </header>
    <div class="modal-body">
      <div class="modal-grid">
        <div>
          <label class="field-label" for="operationInput">Operation</label>
          <input class="field" type="text" id="operationInput" placeholder="120, +10, -5%">
          <p class="help-text">Accepted formats: fixed price, amount delta, or percentage delta.</p>
        </div>
        <div>
          <label class="field-label">Rental day rows</label>
          <div class="days-grid" id="daysGrid"></div>
        </div>
      </div>
    </div>
    <footer class="modal-foot">
      <button class="btn btn-light" id="cancelBulkModal">Cancel</button>
      <button class="btn primary" id="applyBulkBtn">Apply Prices</button>
    </footer>
  </section>
</div>

<div class="modal-backdrop" id="historyModal">
  <section class="modal modal-history">
    <header class="modal-head">
      <h2>Change History</h2>
      <button class="icon-btn" id="closeHistoryModal" aria-label="close">✕</button>
    </header>
    <div class="modal-body history-list" id="historyList"></div>
    <footer class="modal-foot">
      <button class="btn btn-light" id="historyCloseBtn">Close</button>
    </footer>
  </section>
</div>
