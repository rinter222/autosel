function updateOnlineCount() {
    fetch('counter.php')
        .then(response => {
            if (!response.ok) throw new Error('Ошибка загрузки');
            return response.json();
        })
        .then(data => {
            const el = document.getElementById('online-count');
            if (el) {
                el.textContent = data.count || 0;
            }
        })
        .catch(() => {
            const el = document.getElementById('online-count');
            if (el) el.textContent = '—';
        });
}

// Обновляем каждые 30 секунд
setInterval(updateOnlineCount, 30000);

// Запускаем сразу
updateOnlineCount();