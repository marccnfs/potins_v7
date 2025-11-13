document.addEventListener('touchmove', function(event) {
    event.passive = true;
}, { passive: true });

const MARKDOWN_HINT_CLASS = 'markdown-hint';

function getMarkdownHint() {
    const container = document.getElementById('articles-container');
    return container ? container.dataset.markdownHint || '' : '';
}

window.deleteArticle = function(button) {
    let articleBlock = button.closest(".article-block");
    let deleteInput = articleBlock.querySelector("[name$='[delete]']");

    if (deleteInput) {
        deleteInput.value = "1"; // Marque l'article comme supprimé
    }

    articleBlock.style.display = "none"; // Cache l'article visuellement
}

window.addChapter = function() {
    let index = document.querySelectorAll('.article-block').length;
    let container = document.getElementById('articles-container');
    let div = document.createElement('div');
    div.classList.add('article-block');
    const markdownHint = getMarkdownHint();
    div.innerHTML = `
            <label>Titre du chapitre</label>
            <input type="text" name="articles[${index}][titre]" required>

            <label>Contenu</label>
             <textarea class="editor" id="editor-${index}" name="articles[${index}][contenu]" rows="10"></textarea>
            ${markdownHint ? `<p class="${MARKDOWN_HINT_CLASS}">${markdownHint}</p>` : ''}

            <label>Média</label>
            <input type="file" name="articles[${index}][media]" accept="image/*,video/*">
            <input type="hidden" name="articles[${index}][delete]" value="0"> <!-- Ajout de l'input caché -->
            <button type="button" onclick="deleteArticle(this)">Supprimer</button>
        `;
    container.appendChild(div);
    }
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('manage-form');
    if (!form) {
        return;
    }

    const submitButton = document.getElementById('submit-button');
    const spinner = document.getElementById('loading-spinner');

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (submitButton) {
            submitButton.disabled = true;
        }
        if (spinner) {
            spinner.style.display = 'block';
        }

        const formData = new FormData(this);
        const articles = document.querySelectorAll('.article-block');

        articles.forEach((article, index) => {
            const deleteInput = article.querySelector("[name$='[delete]']");
            const deleteValue = deleteInput ? deleteInput.value : '0';

            formData.append(`articles[${index}][id]`, article.getAttribute('data-id') || '');
            const titleField = article.querySelector("[name$='[titre]']");
            formData.append(`articles[${index}][titre]`, titleField ? titleField.value : '');

            const contentField = article.querySelector("[name$='[contenu]']");
            formData.append(`articles[${index}][contenu]`, contentField ? contentField.value : '');
            formData.append(`articles[${index}][delete]`, deleteValue);

            const fileInput = article.querySelector("[name$='[media]']");
            if (fileInput && fileInput.files.length > 0) {
                formData.append(`articles[${index}][media]`, fileInput.files[0]);
            }
        });

        const actionUrl = form.getAttribute('action');

        fetch(actionUrl, {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                    return null;
                }
                return response.json();
            })
            .then(data => {
                if (data && data.message) {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Erreur :', error))
            .finally(() => {
                if (submitButton) {
                    submitButton.disabled = false;
                }
                if (spinner) {
                    spinner.style.display = 'none';
                }
            });
    });
});
