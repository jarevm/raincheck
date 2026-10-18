<?php
require 'config.php';
checkLogin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $area_id = (int)$_POST['area'];
    $rain = $_POST['rain'];
    $flood = $_POST['flood'];
    $note = trim($_POST['note']);
    $photo = null;

    $rain_list = ['NONE', 'LIGHT', 'MODERATE', 'HEAVY'];
    $flood_list = ['NONE', 'ANKLE', 'KNEE', 'WAIST'];

    // check that the area exists
    $stmt = $conn->prepare("SELECT id FROM areas WHERE id = ?");
    $stmt->bind_param('i', $area_id);
    $stmt->execute();
    $area_found = $stmt->get_result()->num_rows;

    if ($area_found == 0 || !in_array($rain, $rain_list) || !in_array($flood, $flood_list)) {
        $error = 'Please choose an area, rain intensity and flood level.';
    }

    // check the photo if one was picked
    if ($error == '' && !empty($_FILES['photo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed_ext)) {
            $error = 'Photo must be jpg, png, gif or webp.';
        } else if ($_FILES['photo']['size'] > 3 * 1024 * 1024) {
            $error = 'Photo must be under 3MB.';
        } else if (!getimagesize($_FILES['photo']['tmp_name'])) {
            $error = 'That file is not a real image.';
        } else {
            // random file name so files do not overwrite each other
            $photo = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $photo);
        }
    }

    if ($error == '') {
        $stmt = $conn->prepare("INSERT INTO reports (user_id, area_id, rain, flood, note, photo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iissss', $_SESSION['user_id'], $area_id, $rain, $flood, $note, $photo);
        $stmt->execute();
        header('Location: index.php#reports');
        exit;
    }
}

$areas = $conn->query("SELECT id, name FROM areas");

$page_title = 'Post Report';
include 'header.php';
?>
<div class="box">
    <h2>Post a rain / flood report</h2>
    <?php if ($error != '') { ?>
        <div class="error"><?php echo $error; ?></div>
    <?php } ?>
    <form method="post" enctype="multipart/form-data">
        <label>Area</label>
        <select name="area" required>
            <?php while ($area = $areas->fetch_assoc()) { ?>
                <option value="<?php echo $area['id']; ?>"><?php echo htmlspecialchars($area['name']); ?></option>
            <?php } ?>
        </select>
        <label>Rain intensity</label>
        <select name="rain">
            <option value="NONE">No rain</option>
            <option value="LIGHT">Light</option>
            <option value="MODERATE">Moderate</option>
            <option value="HEAVY">Heavy</option>
        </select>
        <label>Flood level</label>
        <select name="flood">
            <option value="NONE">No flood</option>
            <option value="ANKLE">Ankle-deep</option>
            <option value="KNEE">Knee-deep</option>
            <option value="WAIST">Waist-deep</option>
        </select>
        <label>Note</label>
        <input type="text" name="note" maxlength="255" placeholder="ex: passable for jeepneys, avoid the underpass">
        <label>Photo (optional)</label>
        <input type="file" name="photo" id="photo" accept="image/*" onchange="showPreview()">
        <img id="preview" alt="preview">
        <br><br>
        <button type="submit">Post report</button>
    </form>
</div>
<script src="post.js"></script>
<?php include 'footer.php'; ?>
