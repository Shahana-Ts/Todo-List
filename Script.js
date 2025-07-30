document.addEventListener("DOMContentLoaded", () => {
  loadTasks();

  const form = document.getElementById("taskForm");

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const formData = new FormData(form);

    fetch("php/saveTask.php", {
      method: "POST",
      body: formData,
    })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        form.reset();
        loadTasks();
      } else {
        alert(data.message || "Failed to add task.");
      }
    })
    .catch((err) => {
      console.error("Error:", err);
    });
  });
});

// Load tasks from the database
function loadTasks() {
  fetch("php/fetchtask.php")
    .then((res) => res.json())
    .then((tasks) => {
      const list = document.getElementById("taskList");
      list.innerHTML = "";

      if (tasks.length === 0) {
        list.innerHTML = `<li class="list-group-item text-muted">No tasks yet.</li>`;
        return;
      }

      tasks.forEach((task) => {
        const li = document.createElement("li");
        li.className = "list-group-item d-flex justify-content-between align-items-center";
        if (task.is_completed == 1) li.classList.add("completed");

        li.innerHTML = `
          <div>
            <strong>${task.task_name}</strong><br>
            <small>${task.description || ""}</small>
          </div>
          <div>
            <button class="btn btn-success btn-sm me-2" onclick="completeTask(${task.id})">✔</button>
            <button class="btn btn-danger btn-sm" onclick="deleteTask(${task.id})">✖</button>
          </div>
        `;
        list.appendChild(li);
      });
    });
}

function completeTask(id) {
  fetch("php/completetask.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${id}`,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        loadTasks();
      }
    });
}

function deleteTask(id) {
  fetch("php/deleteTask.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${id}`,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        loadTasks();
      }
    });
}
