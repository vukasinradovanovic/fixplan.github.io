export async function initInfoCardsFull(page = 1, limit = 6) {
    let jobCardHolder = document.querySelector(".jobCards--full");
    if (!jobCardHolder) return;

    try {
        const roleResponse = await fetch('api/api-role.php');
        let userRole = 'Gost';

        if (roleResponse.ok) {
            const roleData = await roleResponse.json();
            userRole = roleData.role || 'Gost';
        }

        console.log("Session verified user role:", userRole);

        // Fetch your services data list
        const response = await fetch(`api/api-services.php?page=${page}&limit=${limit}`);
        if (!response.ok) throw new Error("Mrežni odziv nije ispravan.");

        const rawText = await response.text();
        const apiResponse = JSON.parse(rawText);
        const servicesItems = apiResponse.items || [];
        
        // --- PAGINATION METADATA EXTRACTED HERE ---
        const metadata = apiResponse.metadata || { total_pages: 1, current_page: 1 };

        if (servicesItems.length === 0) {
            jobCardHolder.innerHTML = `<div class="alert alert-info w-50 mx-auto">Nema dostupnih usluga.</div>`;
            return;
        }

        let cardsHtml = '';
        let tempRow = [];

        // Dynamic layout processing loop
        servicesItems.forEach((card, idx) => {
            const cardBackground = "public/img/" + (card.bgi || "default.png");
            const serviceId = card.id || card.ID || 0;

            let cardControls = '';

            if (userRole === 'Radnik') {
                cardControls = `
                    <div class="jobCards_card-admin d-flex gap-2 w-100 justify-content-center mt-3 pt-3 border-top border-secondary-subtle">
                        <a href="manage_service.php?id=${serviceId}" class="btn btn-warning btn-sm px-3 shadow-sm">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Izmeni
                        </a>
                        <a href="api/api-service-delete.php?id=${serviceId}" class="btn btn-danger btn-sm px-3 shadow-sm">
                            <i class="fa-solid fa-trash me-1"></i> Obriši
                        </a>
                    </div>
                `;
            } else {
                cardControls = `
                    <button class="btn secondaryButton" data-bs-toggle="modal" data-bs-target="#questionModal" data-service-value="${card.value}">
                        Zakaži uslugu
                    </button>
                `;
            }

            tempRow.push(`
                <div class="col-12 col-md-5 m-2">
                    <div class="jobCards_card p-4 d-flex flex-column justify-content-center align-items-center" style="background-image: url('${cardBackground}'); min-height: 340px;">
                        <h3>${card.label}</h3>
                        <p>${card.desc}</p>
                        ${cardControls}
                    </div>
                </div>
            `);

            if (tempRow.length === 2 || idx === servicesItems.length - 1) {
                cardsHtml += `<div class="row justify-content-center mb-4 w-100 m-0">${tempRow.join('')}</div>`;
                tempRow = [];
            }
        });

        // --- DYNAMICALLY GENERATE PAGINATION HTML ---
        let paginationHtml = '';
        if (metadata.total_pages > 1) {
            paginationHtml += `
                <nav aria-label="Page navigation" class="mt-4 d-flex justify-content-center w-100">
                    <ul class="pagination">
                        <li class="page-item ${metadata.current_page <= 1 ? 'disabled' : ''}">
                            <a class="page-link change-page-btn" href="#" data-page="${metadata.current_page - 1}">&laquo; Prethodna</a>
                        </li>
            `;

            for (let i = 1; i <= metadata.total_pages; i++) {
                paginationHtml += `
                    <li class="page-item ${metadata.current_page === i ? 'active' : ''}">
                        <a class="page-link change-page-btn" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            }

            paginationHtml += `
                        <li class="page-item ${metadata.current_page >= metadata.total_pages ? 'disabled' : ''}">
                            <a class="page-link change-page-btn" href="#" data-page="${metadata.current_page + 1}">Sledeća &raquo;</a>
                        </li>
                    </ul>
                </nav>
            `;
        }

        // Combine cards and the pagination component together
        jobCardHolder.innerHTML = cardsHtml + paginationHtml;

        // --- ATTACH EVENT LISTENERS TO THE PAGINATION BUTTONS ---
        document.querySelectorAll(".change-page-btn").forEach(button => {
            button.addEventListener("click", function(e) {
                e.preventDefault();
                const targetPage = parseInt(this.getAttribute("data-page"));
                if (targetPage && targetPage !== metadata.current_page && targetPage >= 1 && targetPage <= metadata.total_pages) {
                    // Re-run the function with the chosen page index number
                    initInfoCardsFull(targetPage, limit);
                    // Smoothly scroll back to the top of the section
                    document.querySelector(".jobCards--full").scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

    } catch (error) {
        console.error("Detaljna greška u initInfoCardsFull:", error);
    }
}