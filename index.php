<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Journey</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Modern & Aesthetic CSS - "Clean White" Theme */
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Poppins', sans-serif;
            /* Subtle Cloudy White Gradient */
            background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
            color: #333;
        }

        .gate-container {
            text-align: center;
            /* Pure White Glass Effect */
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05); /* Very soft shadow */
            border: 1px solid rgba(255,255,255,1);
            max-width: 400px;
            width: 100%;
        }

        h1 {
            font-weight: 600;
            letter-spacing: 3px;
            margin-bottom: 30px;
            margin-top: 0;
            color: #111; /* Almost Black */
            text-transform: uppercase;
        }

        /* Modern Input Fields */
        input {
            width: 100%;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 12px;
            box-sizing: border-box;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background-color: #fff;
            color: #333;
        }

        input:focus {
            border-color: #666; /* Dark Grey Border */
            outline: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        /* Black Gradient Button */
        button {
            width: 100%;
            padding: 15px;
            /* Midnight Black Gradient */
            background: linear-gradient(to right, #434343, #000000);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
            letter-spacing: 1px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .note {
            font-size: 13px;
            color: #999;
            margin-top: 25px;
            font-weight: 300;
        }
    </style>
</head>
<body>

    <div class="gate-container">
        <h1>The Archive</h1>
        
        <form action="auth.php" method="POST">
            <input type="email" name="visitor_email" placeholder="Visitor Email Address" required>
            
            <input type="date" name="passcode" required>
            
            <button type="submit">Unlock Journey</button>
        </form>

        <p class="note">Enter my birthday to verify access.</p>
    </div>

</body>
</html>