<?php
require 'config.php';
$areas = $conn->query("SELECT id, name FROM areas");
$page_title = 'Forecast & Reports';
include 'header.php';
?>
<h1>Weather in Calamba, Laguna</h1>

<!-- umbrella advisor -->
<div id="advisor" class="advisor NO">
    <h2>Loading...</h2>
</div>

<!-- forecast -->
<div class="box wide">
    <div id="current">Loading forecast...</div>
    <h3>Next hours</h3>
    <div class="row" id="hourly"></div>
    <h3>5-day outlook</h3>
    <div class="row" id="daily"></div>
</div>

<!-- community reports -->
<h2 id="reports">Community Reports</h2>
<p><small>Reports come from fellow students and staff. Confirm what you also see. This is not an official suspension notice, follow school and PAGASA announcements.</small></p>
<div class="filter">
    <select id="area" onchange="loadReports()">
        <option value="">All areas</option>
        <?php while ($area = $areas->fetch_assoc()) { ?>
            <option value="<?php echo $area['id']; ?>"><?php echo htmlspecialchars($area['name']); ?></option>
        <?php } ?>
    </select>
    <select id="rain" onchange="loadReports()">
        <option value="">Any rain</option>
        <option value="LIGHT">Light</option>
        <option value="MODERATE">Moderate</option>
        <option value="HEAVY">Heavy</option>
    </select>
    <select id="flood" onchange="loadReports()">
        <option value="">Any flood level</option>
        <option value="YES">Flooded (any)</option>
        <option value="ANKLE">Ankle-deep</option>
        <option value="KNEE">Knee-deep</option>
        <option value="WAIST">Waist-deep</option>
    </select>
    <input type="date" id="date" title="Reported on or after" onchange="loadReports()">
    <a class="btn" href="post.php">+ Report</a>
</div>
<div id="feed"></div>

<script src="script.js"></script>
<?php include 'footer.php'; ?>
