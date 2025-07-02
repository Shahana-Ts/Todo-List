<?php
include("navbar.php");
session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Smart To-Do Home - Your Productivity Hub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  
  <!-- Enhanced CSS Libraries -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      --dark-gradient: linear-gradient(135deg, #0c0c0c 0%, #1a1a2e 50%, #16213e 100%);
      --glass-bg: rgba(255, 255, 255, 0.1);
      --glass-border: rgba(255, 255, 255, 0.2);
      --text-primary: #ffffff;
      --text-secondary: #b8c5d6;
      --accent-blue: #64b5f6;
      --accent-purple: #ab47bc;
      --accent-green: #4caf50;
      --accent-orange: #ff9800;
      --card-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      --hover-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: var(--dark-gradient);
      color: var(--text-primary);
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      min-height: 100vh;
      overflow-x: hidden;
      position: relative;
    }

    /* Animated Background */
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.05) 0%, transparent 50%);
      z-index: -1;
      animation: backgroundShift 20s ease-in-out infinite;
    }

    @keyframes backgroundShift {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.8; }
    }

    /* Floating particles */
    .particles {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      overflow: hidden;
    }

    .particle {
      position: absolute;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      animation: float 15s infinite linear;
    }

    @keyframes float {
      0% {
        transform: translateY(100vh) rotate(0deg);
        opacity: 0;
      }
      10% {
        opacity: 1;
      }
      90% {
        opacity: 1;
      }
      100% {
        transform: translateY(-10vh) rotate(360deg);
        opacity: 0;
      }
    }

    /* Header Styles */
    .header {
      background: var(--glass-bg);
      backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--glass-border);
      padding: 1.5rem 0;
      position: relative;
      box-shadow: var(--card-shadow);
    }

    .welcome-text {
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      font-size: 1.8rem;
      background: var(--primary-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 0;
    }

    .logout-btn {
      background: var(--secondary-gradient);
      border: none;
      padding: 0.7rem 1.5rem;
      border-radius: 50px;
      font-weight: 500;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(245, 87, 108, 0.3);
    }

    .logout-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(245, 87, 108, 0.4);
    }

    /* Main Content */
    .main-section {
      max-width: 1200px;
      margin: 4rem auto;
      padding: 0 2rem;
    }

    .hero-title {
      font-family: 'Poppins', sans-serif;
      font-size: 3.5rem;
      font-weight: 700;
      background: var(--primary-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 1rem;
      text-align: center;
    }

    .hero-subtitle {
      font-size: 1.3rem;
      color: var(--text-secondary);
      text-align: center;
      margin-bottom: 3rem;
      font-weight: 300;
    }

    /* Feature Cards */
    .feature-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
      margin-bottom: 3rem;
    }

    .feature-card {
      background: var(--glass-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      padding: 2.5rem;
      text-decoration: none;
      color: inherit;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      position: relative;
      overflow: hidden;
      box-shadow: var(--card-shadow);
    }

    .feature-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: var(--primary-gradient);
      opacity: 0;
      transition: opacity 0.3s ease;
      z-index: -1;
    }

    .feature-card:hover {
      transform: translateY(-10px) scale(1.02);
      box-shadow: var(--hover-shadow);
      border-color: rgba(255, 255, 255, 0.3);
    }

    .feature-card:hover::before {
      opacity: 0.1;
    }

    .feature-icon {
      font-size: 3rem;
      margin-bottom: 1.5rem;
      display: block;
      transition: all 0.3s ease;
    }

    .feature-card:nth-child(1) .feature-icon { color: var(--accent-blue); }
    .feature-card:nth-child(2) .feature-icon { color: var(--accent-purple); }
    .feature-card:nth-child(3) .feature-icon { color: var(--accent-green); }
    .feature-card:nth-child(4) .feature-icon { color: var(--accent-orange); }

    .feature-card:hover .feature-icon {
      transform: scale(1.1) rotate(5deg);
    }

    .feature-title {
      font-family: 'Poppins', sans-serif;
      font-size: 1.4rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--text-primary);
    }

    .feature-description {
      color: var(--text-secondary);
      line-height: 1.6;
      font-weight: 400;
    }

    /* Stats Section */
    .stats-section {
      background: var(--glass-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      padding: 2rem;
      margin-bottom: 3rem;
      box-shadow: var(--card-shadow);
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 2rem;
      text-align: center;
    }

    .stat-item {
      padding: 1rem;
    }

    .stat-number {
      font-size: 2.5rem;
      font-weight: 700;
      background: var(--primary-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      display: block;
    }

    .stat-label {
      color: var(--text-secondary);
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-top: 0.5rem;
    }

    /* Quick Actions */
    .quick-actions {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-bottom: 3rem;
      flex-wrap: wrap;
    }

    .quick-btn {
      background: var(--glass-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      color: var(--text-primary);
      padding: 0.8rem 2rem;
      border-radius: 50px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .quick-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(0, 0, 0, 0.2);
      background: var(--primary-gradient);
      color: white;
    }

    /* Footer */
    .footer {
      background: var(--glass-bg);
      backdrop-filter: blur(20px);
      border-top: 1px solid var(--glass-border);
      text-align: center;
      padding: 2rem;
      margin-top: auto;
      color: var(--text-secondary);
    }

    .footer-gradient-text {
      background: var(--primary-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-weight: 600;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .hero-title {
        font-size: 2.5rem;
      }
      
      .hero-subtitle {
        font-size: 1.1rem;
      }
      
      .welcome-text {
        font-size: 1.5rem;
      }
      
      .feature-card {
        padding: 2rem;
      }
      
      .main-section {
        padding: 0 1rem;
        margin: 2rem auto;
      }
    }

    /* Loading Animation */
    .fade-in-up {
      animation: fadeInUp 0.8s ease-out forwards;
      opacity: 0;
      transform: translateY(30px);
    }

    @keyframes fadeInUp {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Stagger animations */
    .feature-card:nth-child(1) { animation-delay: 0.1s; }
    .feature-card:nth-child(2) { animation-delay: 0.2s; }
    .feature-card:nth-child(3) { animation-delay: 0.3s; }
    .feature-card:nth-child(4) { animation-delay: 0.4s; }
  </style>
</head>
<body>

<!-- Floating Particles -->
<div class="particles">
  <div class="particle" style="left: 10%; width: 4px; height: 4px; animation-delay: 0s;"></div>
  <div class="particle" style="left: 20%; width: 6px; height: 6px; animation-delay: 2s;"></div>
  <div class="particle" style="left: 30%; width: 3px; height: 3px; animation-delay: 4s;"></div>
  <div class="particle" style="left: 40%; width: 5px; height: 5px; animation-delay: 6s;"></div>
  <div class="particle" style="left: 50%; width: 4px; height: 4px; animation-delay: 8s;"></div>
  <div class="particle" style="left: 60%; width: 6px; height: 6px; animation-delay: 10s;"></div>
  <div class="particle" style="left: 70%; width: 3px; height: 3px; animation-delay: 12s;"></div>
  <div class="particle" style="left: 80%; width: 5px; height: 5px; animation-delay: 14s;"></div>
  <div class="particle" style="left: 90%; width: 4px; height: 4px; animation-delay: 16s;"></div>
</div>

<header class="header">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-8">
        <h2 class="welcome-text">
          <i class="fa-solid fa-sparkles me-2"></i>
          Welcome back, <?= htmlspecialchars($username) ?>!
        </h2>
      </div>
      <div class="col-md-4 text-end">
        <a href="logout.php" class="btn logout-btn">
          <i class="fa-solid fa-sign-out-alt me-2"></i>Logout
        </a>
      </div>
    </div>
  </div>
</header>

<main class="main-section">
  <div data-aos="fade-up" data-aos-duration="1000">
    <h1 class="hero-title">Smart To-Do Hub</h1>
    <p class="hero-subtitle">Your intelligent productivity companion designed to transform how you work and achieve your goals</p>
  </div>

  <!-- Quick Stats Section -->
  <div class="stats-section fade-in-up" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
    <div class="stats-grid">
      <div class="stat-item">
        <span class="stat-number">127</span>
        <div class="stat-label">Tasks Completed</div>
      </div>
      <div class="stat-item">
        <span class="stat-number">8</span>
        <div class="stat-label">Active Projects</div>
      </div>
      <div class="stat-item">
        <span class="stat-number">45</span>
        <div class="stat-label">Focus Sessions</div>
      </div>
      <div class="stat-item">
        <span class="stat-number">Level 12</span>
        <div class="stat-label">Current Level</div>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="quick-actions" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
    <a href="quick-add.php" class="quick-btn">
      <i class="fa-solid fa-plus me-2"></i>Quick Add Task
    </a>
    <a href="today.php" class="quick-btn">
      <i class="fa-solid fa-calendar-day me-2"></i>Today's Plan
    </a>
    <a href="insights.php" class="quick-btn">
      <i class="fa-solid fa-chart-line me-2"></i>Insights
    </a>
  </div>

  <!-- Feature Cards -->
  <div class="feature-grid">
    <a href="index.php" class="feature-card fade-in-up" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
      <i class="fa-solid fa-list-check feature-icon"></i>
      <h3 class="feature-title">Smart Task Manager</h3>
      <p class="feature-description">Organize, prioritize, and track your tasks with intelligent categorization and AI-powered suggestions for maximum productivity.</p>
    </a>

    <a href="focus.php" class="feature-card fade-in-up" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="500">
      <i class="fa-solid fa-brain feature-icon"></i>
      <h3 class="feature-title">Deep Focus Mode</h3>
      <p class="feature-description">Enter distraction-free zones with our advanced Pomodoro timer, ambient sounds, and progress tracking for enhanced concentration.</p>
    </a>

    <a href="gamify.php" class="feature-card fade-in-up" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="600">
      <i class="fa-solid fa-trophy feature-icon"></i>
      <h3 class="feature-title">Achievement System</h3>
      <p class="feature-description">Level up your productivity game! Earn XP, unlock badges, compete with friends, and turn your goals into an engaging adventure.</p>
    </a>

    <a href="habits.php" class="feature-card fade-in-up" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="700">
      <i class="fa-solid fa-calendar-check feature-icon"></i>
      <h3 class="feature-title">Habit Architect</h3>
      <p class="feature-description">Build lasting habits with streak tracking, smart reminders, and personalized insights to create positive routines that stick.</p>
    </a>

    <!-- New Enhanced Features -->
    <a href="analytics.php" class="feature-card fade-in-up" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="800">
      <i class="fa-solid fa-chart-pie feature-icon"></i>
      <h3 class="feature-title">Smart Analytics</h3>
      <p class="feature-description">Discover patterns in your productivity with detailed insights, time tracking, and personalized recommendations for improvement.</p>
    </a>

    <a href="collaboration.php" class="feature-card fade-in-up" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="900">
      <i class="fa-solid fa-users feature-icon"></i>
      <h3 class="feature-title">Team Collaboration</h3>
      <p class="feature-description">Share projects, assign tasks, and collaborate seamlessly with your team using real-time updates and progress tracking.</p>
    </a>

    <a href="ai-assistant.php" class="feature-card fade-in-up" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="1000">
      <i class="fa-solid fa-robot feature-icon"></i>
      <h3 class="feature-title">AI Assistant</h3>
      <p class="feature-description">Get intelligent task suggestions, automated scheduling, and personalized productivity tips powered by advanced AI algorithms.</p>
    </a>

    <a href="wellness.php" class="feature-card fade-in-up" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="1100">
      <i class="fa-solid fa-heart feature-icon"></i>
      <h3 class="feature-title">Wellness Hub</h3>
      <p class="feature-description">Balance productivity with well-being through mindfulness reminders, break suggestions, and work-life balance tracking.</p>
    </a>
  </div>
</main>

<footer class="footer">
  <div class="container">
    <p class="mb-0">
      Crafted with <i class="fa-solid fa-heart text-danger"></i> by 
      <span class="footer-gradient-text">Smart To-Do Team</span> | 
      Powered by Modern Web Technologies
    </p>
    <small class="text-muted">Version 2024.1 - Making productivity beautiful and intelligent</small>
  </div>
</footer>

<!-- Enhanced JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

<script>
// Initialize AOS (Animate On Scroll)
AOS.init({
  duration: 1000,
  easing: 'ease-out-cubic',
  once: true,
  offset: 50
});

// Add smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    document.querySelector(this.getAttribute('href')).scrollIntoView({
      behavior: 'smooth'
    });
  });
});

// Add typing effect to hero title
function typeWriter(element, text, speed = 100) {
  let i = 0;
  element.innerHTML = '';
  function type() {
    if (i < text.length) {
      element.innerHTML += text.charAt(i);
      i++;
      setTimeout(type, speed);
    }
  }
  type();
}

// Initialize typing effect after page load
window.addEventListener('load', () => {
  const heroTitle = document.querySelector('.hero-title');
  if (heroTitle) {
    typeWriter(heroTitle, 'Smart To-Do Hub', 150);
  }
});

// Add floating animation to particles
function animateParticles() {
  const particles = document.querySelectorAll('.particle');
  particles.forEach(particle => {
    const randomDelay = Math.random() * 15000;
    particle.style.animationDelay = randomDelay + 'ms';
  });
}

animateParticles();

// Add dynamic stats counter
function animateCounter(element, target, duration = 2000) {
  let start = 0;
  const increment = target / (duration / 16);
  
  function updateCounter() {
    start += increment;
    if (start < target) {
      element.textContent = Math.floor(start);
      requestAnimationFrame(updateCounter);
    } else {
      element.textContent = target;
    }
  }
  
  updateCounter();
}

// Initialize counters when they come into view
const observerOptions = {
  threshold: 0.5,
  rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const statNumbers = entry.target.querySelectorAll('.stat-number');
      statNumbers.forEach((stat, index) => {
        const targets = [127, 8, 45, 12];
        setTimeout(() => {
          animateCounter(stat, targets[index], 1500);
        }, index * 200);
      });
      observer.unobserve(entry.target);
    }
  });
}, observerOptions);

const statsSection = document.querySelector('.stats-section');
if (statsSection) {
  observer.observe(statsSection);
}
</script>

</body>
</html>