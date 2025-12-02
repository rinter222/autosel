(function () {
		//Защита выделения текста прописана у меня в style.css в секции body{}
		//Это то же самое, но в файле js
    //document.body.style.userSelect = 'none';
    //document.body.style.webkitUserSelect = 'none';
    //document.body.style.mozUserSelect = 'none';
    //document.body.style.msUserSelect = 'none';

    document.addEventListener('contextmenu', function (e) {
        e.preventDefault();
    });

    // Блокируем горячие клавиши
    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey || e.metaKey) {
            if (['c', 'v', 'x', 'u'].includes(e.key.toLowerCase())) {
                e.preventDefault();
                return false;
            }

            if (e.shiftKey && (e.key.toLowerCase() === 'i' || e.key.toLowerCase() === 'j')) {
                e.preventDefault();
                return false;
            }
        }

        if (e.key === 'F12') {
            e.preventDefault();
            return false;
        }

        if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.key === 'J' || e.key === 'j')) {
            e.preventDefault();
            return false;
        }
    });

    // Блокируем перетаскивание текста и изображений
    document.addEventListener('dragstart', function (e) {
        e.preventDefault();
    });
})();