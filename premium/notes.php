<?php
session_start();
include("../navbar.php");

// Use PDO
require_once '../Db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user_id = $_SESSION['user_id'] ?? 1;

// Handle Add Note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
    $title = htmlspecialchars(trim($_POST['title']));
    $content = htmlspecialchars(trim($_POST['content']));
    $color = $_POST['color'] ?? '#ffffff';

    if (!empty($title) || !empty($content)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO notes (user_id, title, content, color, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$user_id, $title, $content, $color]);
            header("Location: notes.php");
            exit;
        } catch (PDOException $e) {
            die("Error adding note: " . $e->getMessage());
        }
    }
}

// Handle Delete Note
if (isset($_GET['delete'])) {
    $noteId = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
        $stmt->execute([$noteId, $user_id]);
        header("Location: notes.php");
        exit;
    } catch (PDOException $e) {
        die("Error deleting note: " . $e->getMessage());
    }
}

// Fetch Notes
try {
    $stmt = $pdo->prepare("SELECT * FROM notes WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching notes: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>QuickList | Notes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
  <style>
    body { background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); }
    .note-card { border-radius: 1rem; box-shadow: 0 6px 16px rgba(0,0,0,0.1); transition: transform 0.2s; }
    .note-card:hover { transform: translateY(-4px); }
    .note-color { width: 100%; height: 5px; border-radius: 0.5rem 0.5rem 0 0; }
  </style>
</head>
<body>
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-bold text-primary"><i class="fas fa-sticky-note me-2"></i>My Notes</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
      <i class="fas fa-plus"></i> New Note
    </button>
  </div>

  <!-- Notes Grid -->
  <div class="row g-4">
    <?php if (empty($notes)): ?>
      <div class="col-12">
        <div class="alert alert-info">No notes yet. Add one above!</div>
      </div>
    <?php else: ?>
      <?php foreach ($notes as $note): ?>
        <div class="col-md-4">
          <div class="note-card card animate__animated animate__fadeIn" style="background: <?= htmlspecialchars($note['color']) ?>;">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($note['title']) ?></h5>
              <p class="card-text"><?= nl2br(htmlspecialchars($note['content'])) ?></p>
              <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted"><?= date('M j, Y g:i A', strtotime($note['created_at'])) ?></small>
                <a href="note.php?delete=<?= $note['id'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="addNoteModalLabel"><i class="fas fa-plus"></i> Add Note</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-control" rows="4" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Color</label>
            <input type="color" name="color" class="form-control form-control-color" value="#ffffff">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="add_note" class="btn btn-primary">Save Note</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
<script>
  // SweetAlert Delete Confirm
  document.querySelectorAll('a.btn-danger').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      let url = this.href;
      Swal.fire({
        title: 'Are you sure?',
        text: "This note will be deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
      }).then(result => {
        if (result.isConfirmed) window.location.href = url;
      });
    });
  });
</script>
</body>
</html>
