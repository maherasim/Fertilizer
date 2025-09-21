<?php include __DIR__ . '/header.php'; ?>

<div class="card-agri">
  <div class="card-header">Welcome to AgriTrack</div>
  <div class="card-body">
    <p style="color:#51656c; margin-bottom:18px;">Sales & inventory for fertilizer and pesticide shops.</p>
    <div class="row g-3">
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card-agri">
          <div class="card-body">
            <div style="font-weight:700; color:#224c38;">Inventory - Fertilizers</div>
            <div style="color:#6b7f86; font-size:14px;">Manage stock, prices, and products</div>
            <div style="margin-top:10px;"><a href="fertilizer.php" class="btn-agri">Open</a></div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card-agri">
          <div class="card-body">
            <div style="font-weight:700; color:#224c38;">Inventory - Pesticides</div>
            <div style="color:#6b7f86; font-size:14px;">Manage stock, prices, and products</div>
            <div style="margin-top:10px;"><a href="pesticide.php" class="btn-agri">Open</a></div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card-agri">
          <div class="card-body">
            <div style="font-weight:700; color:#224c38;">New Sale</div>
            <div style="color:#6b7f86; font-size:14px;">Create daily report entry and invoice</div>
            <div style="margin-top:10px;"><a href="create_daily_report.php" class="btn-agri">Create</a></div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card-agri">
          <div class="card-body">
            <div style="font-weight:700; color:#224c38;">Sales Reports</div>
            <div style="color:#6b7f86; font-size:14px;">Analyze and export</div>
            <div style="margin-top:10px;"><a href="daily_report.php" class="btn-agri">Open</a></div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card-agri">
          <div class="card-body">
            <div style="font-weight:700; color:#224c38;">Import CSV</div>
            <div style="color:#6b7f86; font-size:14px;">Bulk upload daily reports</div>
            <div style="margin-top:10px;"><a href="import_daily_report.php" class="btn-agri">Import</a></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
