<?php
function getUserProfilePic($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($profile_pic);
    $stmt->fetch();
    $stmt->close();
    return $profile_pic ?: 'assets/default-avatar.png';
}