<?php
session_start();
include 'db.php';

// --- 1. PREVENT BROWSER CACHING ---
header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

// --- 2. HANDLE LOGOUT ---
if (isset($_GET['logout'])) {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    header("Location: index.php"); 
    exit();
}

// Security Check: If they didn't login, kick them out
if (!isset($_SESSION['access_granted'])) {
    header("Location: index.php");
    exit();
}

// Fetch memories ordered by date. 
$sql = "SELECT * FROM memories ORDER BY event_date ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Journey</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* AESTHETIC STYLES - "CLEAN WHITE" THEME */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
            color: #333;
            margin: 0;
            padding-bottom: 80px;
            min-height: 100vh;
        }

        /* The Header */
        header {
            text-align: center;
            padding: 60px 20px 40px;
            background: transparent;
            position: relative; 
        }

        /* LOGOUT BUTTON STYLE */
        .logout-btn {
            position: absolute;
            top: 30px;
            right: 30px;
            text-decoration: none;
            color: #555;
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid #ddd;
            padding: 8px 20px;
            border-radius: 30px;
            background: white;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #111;
            color: white;
            border-color: #111;
        }

        h1 { 
            font-weight: 600; 
            letter-spacing: 3px; 
            text-transform: uppercase; 
            color: #111; 
            margin: 0;
        }
        p.subtitle { 
            color: #777; 
            font-size: 1rem; 
            margin-top: 10px;
            font-weight: 300;
        }

        /* The Timeline Container */
        .timeline {
            position: relative;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        /* The Vertical Line */
        .timeline::after {
            content: '';
            position: absolute;
            width: 4px;
            background-color: #ddd; 
            top: 0;
            bottom: 0;
            left: 50%; 
            margin-left: -2px;
            border-radius: 2px;
        }

        /* The Memory Card Container */
        .container {
            padding: 10px 50px;
            position: relative;
            width: 50%; 
            box-sizing: border-box;
        }

        .left { left: 0; }
        .right { left: 50%; }

        /* The Content Box */
        .content {
            padding: 25px;
            background: #fff; 
            border: 1px solid #eee; 
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }

        .content:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }

        /* Arrows */
        .left .content::before {
            content: " ";
            height: 0;
            position: absolute;
            top: 22px;
            width: 0;
            z-index: 1;
            right: -10px;
            border: medium solid white;
            border-width: 10px 0 10px 10px;
            border-color: transparent transparent transparent #fff;
        }

        .right .content::before {
            content: " ";
            height: 0;
            position: absolute;
            top: 22px;
            width: 0;
            z-index: 1;
            left: -10px;
            border: medium solid white;
            border-width: 10px 10px 10px 0;
            border-color: transparent #fff transparent transparent;
        }

        /* Dots on the line */
        .container::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            right: -10px; 
            background-color: #333; 
            border: 4px solid #f4f4f4; 
            top: 22px;
            border-radius: 50%;
            z-index: 1;
        }

        .right::after { left: -10px; } 

        /* Typography & Media */
        h2 { margin-top: 0; color: #111; font-size: 1.4rem; margin-bottom: 10px; font-weight: 600;}
        p { line-height: 1.6; color: #555; font-size: 0.95rem; margin-top: 0;}
        
        img, video {
            max-width: 100%;
            border-radius: 10px;
            margin-top: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .date { 
            font-weight: 700; 
            font-size: 0.85rem; 
            color: #999; 
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px; 
            display: block;
        }

        /* Music Player Button (Floating) */
        .music-toggle {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(to right, #434343, #000000);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 25px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0, 0.2);
            z-index: 100;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .music-toggle:hover { transform: scale(1.05); }

        /* Mobile Responsive */
        @media screen and (max-width: 600px) {
            .logout-btn { top: 20px; right: 20px; padding: 6px 15px; font-size: 0.8rem; }
            .timeline::after { left: 31px; }
            .container { width: 100%; padding-left: 70px; padding-right: 25px; }
            .container::after { left: 19px; }
            .left .content::before, .right .content::before { border: none; }
            .left { text-align: left; }
            .right { left: 0%; }
        }
    </style>
</head>
<body>

    <header>
        <a href="timeline.php?logout=true" class="logout-btn">Log Out</a>
        <h1>My Life Journey</h1>
        <p class="subtitle">A story left behind.</p>
    </header>

    <div class="timeline">
        <?php
        $counter = 0;
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $side = ($counter % 2 == 0) ? "left" : "right";
                
                echo '<div class="container ' . $side . '">';
                echo '  <div class="content">';
                
                if (!empty($row['event_date'])) {
                    echo '    <span class="date">' . date("F j, Y", strtotime($row['event_date'])) . '</span>';
                }
                
                echo '    <h2>' . htmlspecialchars($row['title']) . '</h2>';
                
                if (!empty($row['description'])) {
                    echo '    <p>' . nl2br(htmlspecialchars($row['description'])) . '</p>';
                }
                
                if (!empty($row['media_url'])) {
                    if ($row['media_type'] == 'image') {
                        echo '<img src="' . htmlspecialchars($row['media_url']) . '" alt="Memory">';
                    } elseif ($row['media_type'] == 'video') {
                        echo '<video controls><source src="' . htmlspecialchars($row['media_url']) . '" type="video/mp4"></video>';
                    }
                }
                echo '  </div>';
                echo '</div>';
                
                $counter++;
            }
        } else {
            echo "<div style='text-align:center; padding: 40px; background: #fff; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);'>
                    <p>No stories have been written yet.</p>
                  </div>";
        }
        ?>
    </div>

    <audio id="bg-music" loop>
        <source src="song.mp3" type="audio/mpeg">
    </audio>

    <button class="music-toggle" onclick="toggleMusic()">🎵 Play Music</button>

    <script>
        // --- 1. MUSIC PLAYER LOGIC ---
        var music = document.getElementById("bg-music");
        var btn = document.querySelector(".music-toggle");
        var isPlaying = false;

        function toggleMusic() {
            if (isPlaying) {
                music.pause();
                btn.innerHTML = "🎵 Play Music";
            } else {
                music.play();
                btn.innerHTML = "⏸ Pause Music";
            }
            isPlaying = !isPlaying;
        }

        // --- 2. SECURITY: FORCE RELOAD ON BACK BUTTON ---
        // This detects if the browser loaded the page from the "Back-Forward Cache"
        // If it did, it forces a reload. The server will then reject the user
        // because the session was destroyed.
        window.onpageshow = function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        };
    </script>

</body>
</html>