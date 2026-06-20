/**
 * Inicijalizuje FAQ sekciju preuzimanjem podataka iz baze.
 */
export async function initFAQSection() {
    const $faqSection = $('.faq-section');
    if ($faqSection.length === 0) return; // Exit if the container is not present on this page

    try {
        // Asynchronously fetch FAQ data from your new backend JSON endpoint
        const response = await fetch("api/api-faq.php");
        
        if (!response.ok) {
            throw new Error("Neuspešno učitavanje FAQ podataka.");
        }

        const questionsList = await response.json();

        // Clear out any placeholder text before appending dynamic data
        $faqSection.empty();

        questionsList.forEach(q => {
            const $question = $(`<div class="faq-question">${q.question}</div>`);
            const $answer = $(`<div class="faq-answer">${q.answer}</div>`);

            $faqSection.append($question).append($answer);

            // Bind accordion toggle logic
            $question.on('click', () => {
                $answer.toggleClass('open');
            });
        });

    } catch (error) {
        console.error("Greška prilikom učitavanja FAQ-a:", error);
        $faqSection.html(`<div class="alert alert-warning">Trenutno nije moguće učitati često postavljana pitanja.</div>`);
    }
}