document.addEventListener('DOMContentLoaded', function() {
    // Handle day clicks
    document.querySelectorAll('.calendar-day').forEach(day => {
        day.addEventListener('click', function() {
            const habitId = this.closest('.habit-card').dataset.habitId;
            const date = this.dataset.date;
            const isCompleted = this.classList.contains('completed');
            
            // Toggle visually
            this.classList.toggle('completed');
            
            // Send to server
            fetch('../habits/log.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    habit_id: habitId,
                    date: date,
                    completed: !isCompleted
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    updateStreakCounter(habitId);
                }
            });
        });
    });
    
    // Calculate initial streaks
    document.querySelectorAll('.habit-card').forEach(card => {
        updateStreakCounter(card.dataset.habitId);
    });
});

function updateStreakCounter(habitId) {
    fetch(`../habits/stats.php?habit_id=${habitId}`)
        .then(response => response.json())
        .then(data => {
            const streakElement = document.querySelector(
                `.habit-card[data-habit-id="${habitId}"] .streak-count`
            );
            streakElement.textContent = data.current_streak;
        });
}