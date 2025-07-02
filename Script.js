document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("taskForm");
  const input = document.getElementById("taskInput");
  const list = document.getElementById("taskList");

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const taskText = input.value.trim();
    if (taskText === "") return;

    const li = document.createElement("li");
    li.className = "list-group-item d-flex justify-content-between align-items-center";
    li.innerHTML = `
      <span>${taskText}</span>
      <div>
        <button class="btn btn-success btn-sm me-2" onclick="completeTask(this)">✔</button>
        <button class="btn btn-danger btn-sm" onclick="deleteTask(this)">✖</button>
      </div>
    `;
    list.appendChild(li);
    input.value = "";
  });
});

function completeTask(button) {
  const li = button.closest("li");
  li.classList.toggle("completed");
}

function deleteTask(button) {
  const li = button.closest("li");
  li.remove();
}
