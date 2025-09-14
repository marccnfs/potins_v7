import tinymce from 'tinymce/tinymce';
import 'tinymce/themes/silver/theme';
import 'tinymce/icons/default/icons';

document.addEventListener("DOMContentLoaded", function () {
    tinymce.init({
        selector: 'textarea.editor',
        plugins: 'lists link image table media',
        toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image media',
        menubar: false,
        skin: 'oxide', // Charge le skin TinyMCE
        content_css: '/build/tinymce/skins/content/default/content.css', // Charge le CSS de TinyMCE
        base_url: '/build/tinymce/', // Indique où chercher les fichiers JS/CSS
        license_key: 'gpl' // Évite le message "TinyMCE is running in evaluation mode"
    });
});
document.addEventListener('touchmove', function(event) {
    event.passive = true;
}, { passive: true });

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
    div.innerHTML = `
            <label>Titre du chapitre</label>
            <input type="text" name="articles[${index}][titre]" required>

            <label>Contenu</label>
            <textarea class="editor" id="editor-${index}" name="articles[${index}][contenu]"></textarea>

            <label>Média</label>
            <input type="file" name="articles[${index}][media]" accept="image/*,video/*">
            <input type="hidden" name="articles[${index}][delete]" value="0"> <!-- Ajout de l'input caché -->
            <button type="button" onclick="deleteArticle(this)">Supprimer</button>
        `;
    container.appendChild(div);

    // Vérifie si TinyMCE est déjà appliqué avant d'initialiser
    if (tinymce.get(`editor-${index}`)) {
        tinymce.get(`editor-${index}`).remove();
    }
    // Réappliquer TinyMCE sur le nouveau champ
    tinymce.init({
        selector: `#editor-${index}`,
        plugins: 'lists link image table media',
        toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image media',
        menubar: false,
        skin: 'oxide', // Charge le skin TinyMCE
        content_css: '/build/tinymce/skins/content/default/content.css', // Charge le CSS de TinyMCE
        base_url: '/build/tinymce/', // Indique où chercher les fichiers JS/CSS
        license_key: 'gpl' // Évite le message "TinyMCE is running in evaluation mode"
    });

}

document.getElementById("ressource-form").addEventListener("submit", function (event) {
    event.preventDefault();

    let submitButton = document.getElementById("submit-button");
    let spinner = document.getElementById("loading-spinner");

    // 🔥 Forcer TinyMCE à sauvegarder les contenus avant envoi
    tinymce.triggerSave();

    // Désactiver le bouton et afficher le loader
    submitButton.disabled = true;
    spinner.style.display = "block";

    let formData = new FormData(this);
    let articles = document.querySelectorAll(".article-block");

    articles.forEach((article, index) => {
        let deleteInput = article.querySelector("[name$='[delete]']");
        let deleteValue = deleteInput ? deleteInput.value : "0"; // Évite l'erreur

        formData.append(`articles[${index}][id]`, article.getAttribute("data-id") || null);
        formData.append(`articles[${index}][titre]`, article.querySelector("[name$='[titre]']").value);
        formData.append(`articles[${index}][contenu]`, article.querySelector("[name$='[contenu]']").value);
        formData.append(`articles[${index}][delete]`, deleteValue);

        let fileInput = article.querySelector("[name$='[media]']");
        if (fileInput.files.length > 0) {
            formData.append(`articles[${index}][media]`, fileInput.files[0]);
        }
    });

    // Déterminer la bonne URL (création ou édition)
    let actionUrl = document.getElementById("manage-form").getAttribute("action");

    fetch(actionUrl, {
        method: "POST",
        body: formData
    })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url; // Redirection automatique si Symfony redirige
            } else {
                return response.json();
            }
        })
        .then(data => {
            if (data && data.message) {
                alert(data.message);
            }
        })
        .catch(error => console.error("Erreur :", error))
        .finally(() => {
            submitButton.disabled = false;
            spinner.style.display = "none";
        });
});
