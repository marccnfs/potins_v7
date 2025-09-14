// asset/js/quiz.js


document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('quiz-form');
    const questions = document.querySelectorAll('.question');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const submitBtn = document.getElementById('submit-btn');
    let currentQuestionIndex = 0;

    function showQuestion(index) {
        questions.forEach((question, i) => {
            question.classList.toggle('active', i === index);
        });
        prevBtn.disabled = index === 0;
        nextBtn.disabled = index === questions.length - 1;
        submitBtn.disabled = index !== questions.length - 1;
    }

    prevBtn.addEventListener('click', () => {
        if (currentQuestionIndex > 0) {
            currentQuestionIndex--;
            showQuestion(currentQuestionIndex);
        }
    });

    nextBtn.addEventListener('click', () => {
        if (currentQuestionIndex < questions.length - 1) {
            currentQuestionIndex++;
            showQuestion(currentQuestionIndex);
        }
    });

    questions.forEach(question => {
        question.querySelectorAll('button').forEach(button => {
            button.addEventListener('click', () => {
                const answerInput = question.querySelector('input[type="hidden"]');
                answerInput.value = button.getAttribute('data-value');
            });
        });
    });

    showQuestion(currentQuestionIndex);
});