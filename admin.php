<?php
require 'config.php';
checkAdmin();

// handle the buttons (verify, hide, delete report, delete user)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id'];
    $action = $_POST['action'];

    if ($action == 'verify') {
        $stmt = $conn->prepare("UPDATE reports SET status = 'VERIFIED' WHERE id = ?");
    } else if ($action == 'hide') {
        $stmt = $conn->prepare("UPDATE reports SET status = 'HIDDEN' WHERE id = ?");
    } else if ($action == 'del_report') {
        $stmt = $conn->prepare("DELETE FROM reports WHERE id = ?");
    } else if ($action == 'del_user' && $id != $_SESSION['user_id']) {
        // the admin cannot delete their own account
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    }

    if (isset($stmt)) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
    header('Location: admin.php');
    exit;
}

// get one number from a COUNT query
function countRows($sql) {
    global $conn;
    $row = $conn->query($sql)->fetch_assoc();
    return $row['total'];
}

// makes a small button form for a table row
function actionButton($action, $id, $label, $class) {
    $html = '<form class="inline" method="post">';
    $html .= '<input type="hidden" name="id" value="' . $id . '">';
    $html .= '<input type="hidden" name="action" value="' . $action . '">';
    $html .= '<button type="submit" class="' . $class . '">' . $label . '</button>';
    $html .= '</form> ';
    return $html;
}

$reports = $conn->query("SELECT r.id, a.name AS area, r.rain, r.flood, r.note, r.status, u.name AS author
                         FROM reports r
                         JOIN areas a ON a.id = r.area_id
                         JOIN users u ON u.id = r.user_id
                         ORDER BY r.status = 'PENDING' DESC, r.created_at DESC");
$users = $conn->query("SELECT id, name, email, role FROM users");

$page_title = 'Admin';
include 'header.php';
?>
<h1>Admin Dashboard</h1>

<div class="stats">
    <div class="stat"><b><?php echo countRows("SELECT COUNT(*) AS total FROM reports"); ?></b>Total reports</div>
    <div class="stat"><b><?php echo countRows("SELECT COUNT(*) AS total FROM reports WHERE status = 'PENDING'"); ?></b>Pending</div>
    <div class="stat"><b><?php echo countRows("SELECT COUNT(*) AS total FROM reports WHERE status = 'VERIFIED'"); ?></b>Verified</div>
    <div class="stat"><b><?php echo countRows("SELECT COUNT(*) AS total FROM reports WHERE flood <> 'NONE' AND created_at > NOW() - INTERVAL 1 DAY"); ?></b>Flood reports (24h)</div>
    <div class="stat"><b><?php echo countRows("SELECT COUNT(*) AS total FROM users"); ?></b>Users</div>
</div>

<h2>Reports</h2>
<div class="box wide">
    <table>
        <tr>
            <th>Area</th>
            <th>Rain</th>
            <th>Flood</th>
            <th>Note</th>
            <th>By</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $reports->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['area']); ?></td>
                <td><?php echo $row['rain']; ?></td>
                <td><?php echo $row['flood']; ?></td>
                <td><?php echo htmlspecialchars($row['note'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['author']); ?></td>
                <td><span class="badge <?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                <td>
                    <?php
                    if ($row['status'] != 'VERIFIED') {
                        echo actionButton('verify', $row['id'], 'Verify', '');
                    }
                    if ($row['status'] != 'HIDDEN') {
                        echo actionButton('hide', $row['id'], 'Hide', 'alt');
                    }
                    echo actionButton('del_report', $row['id'], 'Delete', 'delete');
                    ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<h2>User Management</h2>
<div class="box wide">
    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $users->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo $row['role']; ?></td>
                <td>
                    <?php
                    if ($row['id'] != $_SESSION['user_id']) {
                        echo actionButton('del_user', $row['id'], 'Delete', 'delete');
                    }
                    ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>
<?php include 'footer.php'; ?>
