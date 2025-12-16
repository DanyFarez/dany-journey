<?php
session_start();
include 'db.php';

// --- 0. PREVENT BROWSER CACHING ---
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// --- CONFIGURATION ---
$ADMIN_PASSWORD = ""; 

// 1. HANDLE LOGOUT (Fully Destroy Session)
if (isset($_GET['logout'])) {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: admin.php");
    exit();
}

// 2. HANDLE LOGIN
if (isset($_POST['login_btn'])) {
    if ($_POST['password'] === $ADMIN_PASSWORD) {
        $_SESSION['is_admin'] = true;
        // --- FIX: FORCE RELOAD AFTER LOGIN ---
        header("Location: admin.php"); 
        exit();
    } else {
        $error = "Wrong password.";
    }
}

// Check if user is logged in
$is_admin = isset($_SESSION['is_admin']);

// INITIALIZE VARIABLES FOR EDIT MODE
$edit_mode = false;
$id = $title = $description = $event_date = $media_type = $media_url = "";

// 3. CHECK IF WE ARE EDITING A POST
if (isset($_GET['edit']) && $is_admin) {
    $edit_id = $_GET['edit'];
    $edit_query = $conn->query("SELECT * FROM memories WHERE id=$edit_id");
    if ($edit_query->num_rows > 0) {
        $row = $edit_query->fetch_assoc();
        $edit_mode = true;
        $id = $row['id'];
        $title = $row['title'];
        $description = $row['description'];
        $event_date = $row['event_date'];
        $media_type = $row['media_type'];
        $media_url = $row['media_url'];
    }
}

// 4. HANDLE ADD OR UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_admin) {
    
    if (isset($_POST['login_btn'])) return;

    $title_in = $conn->real_escape_string($_POST['title']);
    $desc_in = $conn->real_escape_string($_POST['description']);
    $type_in = $_POST['media_type'];
    
    $raw_date = $_POST['event_date'];
    if (empty($raw_date)) {
        $date_sql = "NULL"; 
    } else {
        $date_sql = "'$raw_date'"; 
    }

    $final_media_url = $_POST['old_media_url']; 
    
    if (!empty($_FILES['media_file']['name'])) {
        $target_dir = "uploads/";
        $file_name = time() . "_" . basename($_FILES["media_file"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["media_file"]["tmp_name"], $target_file)) {
            $final_media_url = $target_file;
        }
    }

    if (isset($_POST['update_memory'])) {
        $id_to_update = $_POST['update_id'];
        $sql = "UPDATE memories SET 
                title='$title_in', 
                description='$desc_in', 
                event_date=$date_sql, 
                media_type='$type_in', 
                media_url='$final_media_url' 
                WHERE id=$id_to_update";
        
        if ($conn->query($sql)) {
            echo "<script>alert('Memory Updated!'); window.location.href='admin.php';</script>";
        }
        
    } elseif (isset($_POST['add_memory'])) {
        $sql = "INSERT INTO memories (title, description, media_url, media_type, event_date) 
                VALUES ('$title_in', '$desc_in', '$final_media_url', '$type_in', $date_sql)";
        
        if ($conn->query($sql)) {
            echo "<script>alert('Memory Added!'); window.location.href='admin.php';</script>";
        }
    }
}

// 5. HANDLE DELETE
if (isset($_GET['delete']) && $is_admin) {
    $del_id = $_GET['delete'];
    $conn->query("DELETE FROM memories WHERE id=$del_id");
    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* "CLEAN WHITE" ADMIN THEME */
        body { 
            font-family: 'Poppins', sans-serif; 
            padding: 50px; 
            margin: 0;
            background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); 
            min-height: 100vh;
            color: #333;
        }

        /* Clean White Card Style */
        .card { 
            background: #fff; 
            padding: 40px; 
            border-radius: 20px; 
            max-width: 700px; 
            margin: auto; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
            border: 1px solid #eee;
        }

        h2, h3 { color: #111; font-weight: 600; margin-top: 0; }

        /* Form Styling */
        label { display: block; margin-top: 15px; font-weight: 600; font-size: 0.9rem; color: #333; }
        
        input[type="text"], input[type="password"], input[type="date"], textarea, select, input[type="file"] { 
            width: 100%; 
            margin-top: 8px; 
            padding: 12px; 
            box-sizing: border-box; 
            border: 1px solid #ddd; 
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            background: #fff;
            transition: 0.3s;
            color: #333;
        }

        /* Focus color is now Dark Grey */
        input:focus, textarea:focus, select:focus {
            border-color: #666;
            outline: none;
            box-shadow: 0 0 8px rgba(0,0,0, 0.1);
        }

        /* Buttons - Black Gradient */
        button { 
            background: linear-gradient(to right, #434343, #000000); 
            color: #fff; 
            font-weight: bold;
            padding: 15px 20px; 
            border: none; 
            border-radius: 12px; 
            font-size: 1rem;
            cursor: pointer; 
            width: 100%; 
            margin-top: 25px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        button:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .cancel-btn { 
            display: block; 
            text-align: center; 
            margin-top: 15px; 
            text-decoration: none; 
            color: #888; 
            font-size: 0.9rem;
        }
        .cancel-btn:hover { color: #555; }

        /* Table Styling */
        table { width: 100%; margin-top: 10px; border-collapse: separate; border-spacing: 0 10px; }
        
        th { text-align: left; color: #888; font-weight: 400; font-size: 0.85rem; padding: 10px; }
        
        td { 
            background: #fff; 
            padding: 15px; 
            border-top: 1px solid #f0f0f0; 
            border-bottom: 1px solid #f0f0f0;
        }
        
        tr td:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; border-left: 1px solid #f0f0f0;}
        tr td:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; border-right: 1px solid #f0f0f0;}

        /* Links */
        .logout-link { color: #333; font-weight: bold; text-decoration: none; font-size: 0.9rem; border: 1px solid #ddd; padding: 5px 15px; border-radius: 20px; }
        .logout-link:hover { background: #333; color: white; }
        
        .edit-btn { color: #333; text-decoration: none; font-weight: bold; margin-right: 15px; }
        .delete-btn { color: #E74C3C; text-decoration: none; font-weight: bold; }
        
        .empty-date { color: #bbb; font-style: italic; font-size: 0.8rem; }
    </style>
</head>
<body>

<?php if (!$is_admin): ?>
    
    <div class="card" style="max-width: 400px; text-align: center;">
        <h2 style="margin-bottom: 20px;">Admin Access</h2>
        <p style="color:#777; margin-bottom: 30px;">Enter credentials to manage the timeline.</p>
        <form method="POST">
            <input type="password" name="password" placeholder="Passcode" required>
            <button type="submit" name="login_btn">Login</button>
            <?php if(isset($error)) echo "<p style='color:#E74C3C; margin-top:10px;'>$error</p>"; ?>
        </form>
    </div>

<?php else: ?>

    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <h2><?php echo $edit_mode ? "Edit Story" : "Create New Story"; ?></h2>
            <a href="admin.php?logout=true" class="logout-link">Log Out</a>
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_id" value="<?php echo $id; ?>">
            <input type="hidden" name="old_media_url" value="<?php echo $media_url; ?>">

            <label>Title of Memory</label>
            <input type="text" name="title" value="<?php echo $title; ?>" placeholder="e.g. My Graduation Day" required>

            <label>Date (Optional)</label>
            <input type="date" name="event_date" value="<?php echo $event_date; ?>">

            <label>The Story</label>
            <textarea name="description" rows="5" placeholder="Write something meaningful..."><?php echo $description; ?></textarea>

            <div style="display: flex; gap: 20px;">
                <div style="flex:1;">
                    <label>Type</label>
                    <select name="media_type">
                        <option value="text" <?php if($media_type=='text') echo 'selected'; ?>>Text Only</option>
                        <option value="image" <?php if($media_type=='image') echo 'selected'; ?>>Image</option>
                        <option value="video" <?php if($media_type=='video') echo 'selected'; ?>>Video</option>
                    </select>
                </div>
                <div style="flex:2;">
                     <label>Attachment</label>
                     <input type="file" name="media_file">
                </div>
            </div>

            <?php if($edit_mode && $media_url): ?>
                <p style="font-size:12px; color:#666; margin-top:5px;">Attached: <?php echo basename($media_url); ?></p>
            <?php endif; ?>

            <?php if ($edit_mode): ?>
                <button type="submit" name="update_memory">Update Memory</button>
                <a href="admin.php" class="cancel-btn">Cancel Editing</a>
            <?php else: ?>
                <button type="submit" name="add_memory">Save to Timeline</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="card" style="margin-top: 40px; border-top: 5px solid #333;">
        <h3>Your Timeline</h3>
        <?php
        $result = $conn->query("SELECT * FROM memories ORDER BY event_date DESC");
        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<thead><tr><th>DATE</th><th>TITLE</th><th style='text-align:right'>ACTIONS</th></tr></thead>";
            echo "<tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                $dateDisplay = $row['event_date'] ? $row['event_date'] : "<span class='empty-date'>No Date</span>";
                echo "<td>" . $dateDisplay . "</td>";
                echo "<td><strong>" . htmlspecialchars($row['title']) . "</strong></td>";
                echo "<td style='text-align:right;'>";
                echo "<a href='admin.php?edit=" . $row['id'] . "' class='edit-btn'>Edit</a>";
                echo "<a href='admin.php?delete=" . $row['id'] . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this memory?\")'>Delete</a>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
        } else {
            echo "<p style='text-align:center; color:#888; margin-top:20px;'>No memories yet. Start writing your story above.</p>";
        }
        ?>
    </div>

<?php endif; ?>

</body>

</html>
