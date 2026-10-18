<?php require 'config.php';
need();
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $photo = null;
  if (!empty($_FILES['photo']['name'])) {
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) || $_FILES['photo']['size'] > 3 * 1024 * 1024 || !getimagesize($_FILES['photo']['tmp_name'])) $err = 'Photo must be an image under 3MB.';
    else {
      $photo = bin2hex(random_bytes(8)) . '.' . $ext;
      move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/$photo");
    }
  }
  $rain = in_array($_POST['rain'], ['NONE', 'LIGHT', 'MODERATE', 'HEAVY']) ? $_POST['rain'] : 'NONE';
  $flood = in_array($_POST['flood'], ['NONE', 'ANKLE', 'KNEE', 'WAIST']) ? $_POST['flood'] : 'NONE';
  if (!$err) {
    $s = $db->prepare("INSERT INTO reports(user_id,area_id,rain,flood,note,photo) VALUES(?,?,?,?,?,?)");
    $s->bind_param('iissss', $_SESSION['u']['id'], $_POST['area'], $rain, $flood, $_POST['note'], $photo);
    $s->execute();
    header('Location: index.php');
    exit;
  }
}
$areas = $db->query("SELECT * FROM areas");
head('Post Report'); ?>
<div class="panel">
  <h2>Post a rain / flood report</h2><?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <label>Area</label><select name="area" required><?php while ($a = $areas->fetch_assoc()): ?><option value="<?= $a['id'] ?>"><?= e($a['name']) ?></option><?php endwhile; ?></select>
    <label>Rain intensity</label><select name="rain">
      <option value="NONE">No rain</option>
      <option value="LIGHT">Light</option>
      <option value="MODERATE">Moderate</option>
      <option value="HEAVY">Heavy</option>
    </select>
    <label>Flood level</label><select name="flood">
      <option value="NONE">No flood</option>
      <option value="ANKLE">Ankle-deep</option>
      <option value="KNEE">Knee-deep</option>
      <option value="WAIST">Waist-deep</option>
    </select>
    <label>Note</label><input name="note" maxlength="255" placeholder="e.g. Passable for jeepneys, avoid the underpass">
    <label>Photo (optional)</label><input type="file" name="photo" accept="image/*" onchange="const f=this.files[0],i=document.getElementById('prev');if(f){i.src=URL.createObjectURL(f);i.style.display='block'}">
    <img id="prev" alt="preview"><br><button>Post report</button>
  </form>
</div><?php foot(); ?>