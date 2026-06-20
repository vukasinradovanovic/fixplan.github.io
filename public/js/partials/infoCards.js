export async function initInfoCards() {
    let jobCardHolder = document.querySelector(".jobCards");
    if (!jobCardHolder) return; // Exit quickly if container doesn't exist on this page

    try {
        // Fetch values asynchronously from your local database JSON endpoint controller
        const response = await fetch("api/api-services.php");
        const rawText = await response.text();
        
        if (!response.ok) {
            console.error("Server Error Response:", rawText);
            throw new Error("Mrežni odziv nije ispravan.");
        }

        const apiResponse = JSON.parse(rawText);
        
        // FIX: Extract the items array from the object payload safely, fallback to an empty array
        const servicesArray = apiResponse.items || [];

        if (servicesArray.length === 0) {
            jobCardHolder.innerHTML = `<div class="alert alert-info w-50 mx-auto">Nema dostupnih usluga.</div>`;
            return;
        }

        let html = '';
        let tempRow = [];

        // Slice the array safely to restrict output to a maximum of 6 elements for the home layout
        const limitedServices = servicesArray.slice(0, 6);

        limitedServices.forEach((card, idx) => {
            // Properly declared dynamic background paths pointing to your local asset directory
            const cardBackground = "public/img/" + (card.bgi || "default.png");
            
            tempRow.push(`
                <div class="col-12 col-md-6 col-lg-4 d-flex align-items-stretch mb-4">
                    <div class="jobCards_card w-100 p-3 d-flex flex-column justify-content-center align-items-center" style="background-image: url('${cardBackground}');">
                        <h3>${card.label}</h3>
                        <p>${card.desc}</p>
                        <button class="btn secondaryButton" data-bs-toggle="modal" data-bs-target="#questionModal" data-service-value="${card.value}">Zakaži uslugu</button>
                    </div>
                </div>
            `);

            if (tempRow.length === 3 || idx === limitedServices.length - 1) {
                html += `<div class="row justify-content-center mb-4">${tempRow.join('')}</div>`;
                tempRow = [];
            }
        });

        jobCardHolder.innerHTML = html;

    } catch (error) {
        console.error("Detaljna greška:", error);
        jobCardHolder.innerHTML = `<div class="alert alert-danger w-50 mx-auto" role="alert">Trenutno nije moguće učitati usluge.</div>`;
    }
}