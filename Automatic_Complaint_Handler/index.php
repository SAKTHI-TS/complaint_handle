<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart E-Governance Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --gradient-1: linear-gradient(45deg, #4158D0, #C850C0, #FFCC70);
            --gradient-2: linear-gradient(135deg, #0093E9, #80D0C7);
            --gradient-3: linear-gradient(45deg, #8EC5FC, #E0C3FC);
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--gradient-1);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            min-height: 100vh;
            color: #fff;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header styles */
        header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 40px;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(to right, #fff, #e0e0e0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-links a {
            color: #fff;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Hero section */
        .hero {
            text-align: center;
            padding: 60px 0;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            animation: fadeInUp 1s ease;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 40px;
            animation: fadeInUp 1s ease 0.2s;
            opacity: 0;
            animation-fill-mode: forwards;
        }

        /* Cards section */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }

        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-10px);
        }

        .card i {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .card h3 {
            margin-bottom: 15px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                gap: 20px;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <div class="logo">Smart E-Governance Grievance Portal</div>
                <div class="nav-links">
                    <a href="#"><i class="fas fa-home"></i> Home</a>
                    <a href="login.php"><i class="fas fa-user"></i> Login</a>
                </div>
            </nav>
        </header>

        <section class="hero">
            <h1>Welcome to Smart E-Governance Grievance Portal</h1>
            <p>Empowering citizens through transparent and efficient governance</p>
        </section>

        <section class="cards">
            <div class="card">
                <i class="fas fa-tasks"></i>
                <h3>File Complaints</h3>
                <p>Submit your grievances easily through our online portal</p>
            </div>
            <div class="card">
                <i class="fas fa-search-location"></i>
                <h3>Track Status</h3>
                <p>Monitor your complaint status in real-time</p>
            </div>
            <div class="card">
                <i class="fas fa-chart-line"></i>
                <h3>Quick Resolution</h3>
                <p>Get faster resolution to your problems</p>
            </div>
        </section>
    </div>

    <script>
        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>