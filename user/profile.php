<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $bio = trim($_POST['bio']);
    
    // Handle Profile Image Upload
    $profile_image = $user['profile_image'];
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "../uploads/profiles/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $new_image = time() . "_" . basename($_FILES['profile_image']['name']);
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_dir . $new_image)) {
            $profile_image = "uploads/profiles/" . $new_image;
        }
    }

    $stmtUpdate = $conn->prepare("UPDATE users SET name = ?, phone = ?, bio = ?, profile_image = ? WHERE id = ?");
    if ($stmtUpdate->execute([$name, $phone, $bio, $profile_image, $user_id])) {
        $success = "PROFILE INTEL UPDATED.";
        // Refresh user data
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } else {
        $error = "UPDATE FAILED.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROFILE MGMT - HOSTEL/PG FINDER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../assets/css/brutalism.css">
</head>
<body>
    <?php include '../includes/header_brutal.php'; ?>
    <div class="container brutal-container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="brutal-card">
                    <h1 class="display-4 text-center mb-5 border-bottom border-5 border-dark pb-3">PROFILE MGMT</h1>
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success border-brutal rounded-0 mb-4"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="text-center mb-4">
                            <img src="<?php echo $user['profile_image'] ? '../' . $user['profile_image'] : 'https://via.placeholder.com/150?text=NO+IMAGE'; ?>" 
                                 class="rounded-circle border border-5 border-dark mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                            <input type="file" name="profile_image" class="form-control mt-2" accept="image/*">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">DESIGNATION (NAME)</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">COMMUNICATION LINE (PHONE)</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">MISSION BIO</label>
                            <textarea name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-brutal w-100 fs-4 py-3">UPDATE PROFILE</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer_brutal.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
