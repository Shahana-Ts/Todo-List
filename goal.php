<?php
header('Content-Type: application/json');

// Connect to the database
require 'db.php';

// Get a variable from POST or GET (helper for cleaner code)
function get($key, $default=null) {
    return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
}

// ADD A NEW GOAL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && get('action') === 'add') {
    $name = get('name');
    $type = get('type');
    $target = intval(get('target_tasks'));
    $deadline = get('deadline');

    // Simple validation
    if (!$name || !$type || !$target || !$deadline) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill all fields!'
        ]);
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO goals (name, type, target_tasks, deadline) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("ssis", $name, $type, $target, $deadline);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Goal added successfully!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error adding goal.'
        ]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// DELETE A GOAL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && get('action') === 'dele    =te') {
    $goal_id = intval(get('goal_id'));
    if (!$goal_id) {
        echo json_encode(['success' => false, 'message' => 'Missing goal ID']);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM goals WHERE id=?");
    $stmt->bind_param("i", $goal_id);
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Goal deleted!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Could not delete goal.'
        ]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// LIST ALL GOALS WITH PROGRESS (completed_tasks & percent)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && get('action') === 'list') {
    $sql = "
        SELECT
            g.*,
            COUNT(t.id) AS completed_tasks
        FROM goals g
        LEFT JOIN tasks t ON g.id = t.goal_id AND t.is_completed = 1
        GROUP BY g.id
        ORDER BY g.deadline ASC
    ";
    $result = $conn->query($sql);
    $goals = [];
    if ($result && $result->num_rows) {
        while ($row = $result->fetch_assoc()) {
            $row['progress'] = $row['target_tasks'] > 0
                ? round(($row['completed_tasks'] / $row['target_tasks']) * 100)
                : 0;
            $goals[] = $row;
        }
    }
    echo json_encode(['success' => true, 'goals' => $goals]);
    $conn->close();
    exit;
}

// DEFAULT: no matching action
echo json_encode([
    'success' => false,
    'message' => 'Invalid action or request method'
]);
exit;
?>
