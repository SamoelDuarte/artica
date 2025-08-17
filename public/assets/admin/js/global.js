document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('keydown', function (e) {
        const isEnter = e.key === 'Enter';
        if (!isEnter) return;

        // Evita o envio do formulário ao apertar Enter
        e.preventDefault();

        const inputs = Array.from(document.querySelectorAll('input, select, textarea'))
            .filter(el => !el.disabled && el.offsetParent !== null); // ignora inputs escondidos/desativados

        const currentIndex = inputs.indexOf(document.activeElement);

        let nextIndex;
        if (e.shiftKey) {
            // Shift + Enter -> vai para o anterior
            nextIndex = currentIndex > 0 ? currentIndex - 1 : 0;
        } else {
            // Enter -> vai para o próximo
            nextIndex = currentIndex + 1 < inputs.length ? currentIndex + 1 : currentIndex;
        }

        inputs[nextIndex].focus();
    });
});
