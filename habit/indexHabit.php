<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Habit Tracker</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f8f9fa;
    }
    .card {
      box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05);
    }
  </style>
</head>
<body>
<div class="container mt-5">
  <h2 class="mb-4">🧠 Habit Tracker</h2>

  <form id="habitForm" class="card p-4 mb-4">
    <h5>Add New Habit</h5>
    <div class="row g-3">
      <div class="col-md-4">
        <input name="habit_name" type="text" class="form-control" placeholder="Habit name" required>
      </div>
      <div class="col-md-4">
        <select name="frequency" class="form-control">
          <option value="daily">Daily</option>
          <option value="weekly">Weekly</option>
        </select>
      </div>
      <div class="col-md-4">
        <input name="start_date" type="date" class="form-control">
      </div>
      <div class="col-12 mt-3">
        <button type="submit" class="btn btn-primary w-100">Add Habit</button>
      </div>
    </div>
  </form>

  <div id="habitList" class="row g-3">
    <!-- Habit cards will appear here -->
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    loadHabits();

    document.getElementById("habitForm").addEventListener("submit", function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch("habit.php", {
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if(data.status === 'success') {
          this.reset();
          loadHabits();
        } else {
          alert(data.message || "Error adding habit.");
        }
      });
    });
  });

  function loadHabits() {
    fetch("fetchHabit.php")
    .then(res => res.json())
    .then(habits => {
      const container = document.getElementById("habitList");
      container.innerHTML = "";
      if (habits.length === 0) {
        container.innerHTML = "<p class='text-muted'>No habits found.</p>";
        return;
      }
      habits.forEach(habit => {
        const col = document.createElement("div");
        col.className = "col-md-4";
        col.innerHTML = `
          <div class="card p-3">
            <h5>${habit.habit_name}</h5>
            <p>⏰ Frequency: <strong>${habit.frequency}</strong></p>
            <p>📅 Start Date: ${habit.start_date}</p>
            <button class="btn btn-sm btn-outline-${habit.is_active == 1 ? 'danger' : 'success'}"
              onclick="toggleHabit(${habit.id})">
              ${habit.is_active == 1 ? 'Deactivate' : 'Activate'}
            </button>
          </div>
        `;
        container.appendChild(col);
      });
    });
  }

  function toggleHabit(id) {
    fetch("togglehabit.php", {
      method: "POST",
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${id}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        loadHabits();
      } else {
        alert("Failed to toggle habit.");
      }
    });
  }
</script>
</body>
</html>
